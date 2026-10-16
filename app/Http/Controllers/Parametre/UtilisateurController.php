<?php

namespace App\Http\Controllers\Parametre;

use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UtilisateurController extends Controller
{
    use ParametreHelpers;

    private const MESSAGES = [
        'login.unique' => 'Ce login est déjà utilisé.',
        'userdroitid.exists' => 'Le niveau d\'accès sélectionné n\'existe pas.',
        'password.min' => 'Le mot de passe doit contenir au moins 6 caractères.',
        'employeeid.exists' => 'Le vendeur choisi n\'appartient pas à votre site.',
    ];

    public function index()
    {
        $siteid = $this->userSiteId();

        $utilisateurs = DB::table('users as u')
            ->leftJoin('userdroits as d', 'u.userdroitid', '=', 'd.userdroitid')
            ->where('u.siteid', $siteid)
            ->leftJoin('employees as e', 'u.employeeid', '=', 'e.employeeid')
            ->select('u.userid', 'u.login', 'u.userdroitid', 'u.employeeid', 'd.libelle as droit_libelle', 'e.nom as vendeur_nom')
            ->orderBy('u.userid')
            ->get();

        $droits = DB::table('userdroits')->orderBy('userdroitid')->get();
        // Vendeur (employé) associé au compte : nécessaire pour ouvrir une journée et vendre
        $vendeurs = DB::table('employees')->where('siteid', $siteid)->orderBy('nom')->get(['employeeid', 'nom', 'bloque']);

        return view('parametre.utilisateur.index', compact('utilisateurs', 'droits', 'vendeurs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'login' => 'required|string|max:255|unique:users,login',
            'password' => 'required|string|min:' . \App\Http\Controllers\MonCompteController::LONGUEUR_MIN,
            'userdroitid' => 'required|integer|exists:userdroits,userdroitid',
            'employeeid' => ['nullable', 'integer', Rule::exists('employees', 'employeeid')->where('siteid', $this->userSiteId())],
        ], self::MESSAGES);

        $siteid = $this->userSiteId();
        $createur = auth()->user();
        $societeid = $createur->societeid ?? 1;

        DB::transaction(function () use ($request, $siteid, $societeid, $createur) {
            $nextId = \App\Support\Identifiants::prochain('users', 'userid');

            // Mot de passe stocké en clair : partagé avec l'application desktop historique
            DB::table('users')->insert([
                'userid' => $nextId,
                'login' => $request->login,
                'password' => $request->password,
                'userdroitid' => $request->userdroitid,
                'siteid' => $siteid,
                'societeid' => $societeid,
                // Même rattachement que le créateur (client = société cliente du logiciel, agence de caisse du site)
                'clientid' => $createur->clientid,
                'agencebid' => DB::table('caisses')->where('siteid', $siteid)->value('agencebid') ?? $createur->agencebid,
                'employeeid' => $request->employeeid ?: null,
            ]);
        });

        return redirect()->route('parametre.utilisateur.index')->with('success', 'Utilisateur ajouté avec succès.');
    }

    public function update(Request $request, $id)
    {
        $id = $this->routeId($id);

        $request->validate([
            'login' => ['required', 'string', 'max:255', Rule::unique('users', 'login')->ignore($id, 'userid')],
            'password' => 'nullable|string|min:' . \App\Http\Controllers\MonCompteController::LONGUEUR_MIN,
            'userdroitid' => 'required|integer|exists:userdroits,userdroitid',
            'employeeid' => ['nullable', 'integer', Rule::exists('employees', 'employeeid')->where('siteid', $this->userSiteId())],
        ], self::MESSAGES);

        // Son propre compte : ne pas se retirer l'accès à la gestion des utilisateurs par erreur
        if ($id === (int) auth()->id() && !\App\Support\Droits::roleAutorise((int) $request->userdroitid, 'gestion.users')) {
            return redirect()->route('parametre.utilisateur.index')->with('error', 'Vous ne pouvez pas donner à votre propre compte un rôle sans accès à la gestion des utilisateurs.');
        }

        $data = [
            'login' => $request->login,
            'userdroitid' => $request->userdroitid,
            'employeeid' => $request->employeeid ?: null,
        ];

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $updated = DB::table('users')
            ->where('userid', $id)
            ->where('siteid', $this->userSiteId())
            ->update($data);

        if (!$updated) {
            return redirect()->route('parametre.utilisateur.index')->with('error', 'Utilisateur introuvable.');
        }

        return redirect()->route('parametre.utilisateur.index')->with('success', 'Utilisateur modifié avec succès.');
    }

    public function destroy($id)
    {
        $id = $this->routeId($id);

        if ($id === (int) auth()->id()) {
            return redirect()->route('parametre.utilisateur.index')->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $exists = DB::table('users')
            ->where('userid', $id)
            ->where('siteid', $this->userSiteId())
            ->exists();

        if (!$exists) {
            return redirect()->route('parametre.utilisateur.index')->with('error', 'Utilisateur introuvable.');
        }

        // ctickets / journalcaisses sont en ON DELETE CASCADE sur users : ne jamais effacer l'historique
        $hasHistory = DB::table('ctickets')->where('userid', $id)->exists()
            || DB::table('journalcaisses')->where('userid', $id)->exists();

        if ($hasHistory) {
            return redirect()->route('parametre.utilisateur.index')->with('error', 'Impossible de supprimer cet utilisateur : il possède un historique de ventes ou de caisse. Désactivez-le plutôt que de le supprimer.');
        }

        try {
            DB::transaction(function () use ($id) {
                DB::table('users')
                    ->where('userid', $id)
                    ->where('siteid', $this->userSiteId())
                    ->delete();
            });
        } catch (QueryException $e) {
            return redirect()->route('parametre.utilisateur.index')->with('error', 'Impossible de supprimer cet utilisateur : il est référencé par d\'autres documents.');
        }

        return redirect()->route('parametre.utilisateur.index')->with('success', 'Utilisateur supprimé avec succès.');
    }
}
