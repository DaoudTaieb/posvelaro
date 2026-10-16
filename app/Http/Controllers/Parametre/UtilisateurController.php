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
    ];

    public function index()
    {
        $siteid = $this->userSiteId();

        $utilisateurs = DB::table('users as u')
            ->leftJoin('userdroits as d', 'u.userdroitid', '=', 'd.userdroitid')
            ->where('u.siteid', $siteid)
            ->select('u.userid', 'u.login', 'u.userdroitid', 'd.libelle as droit_libelle')
            ->orderBy('u.userid')
            ->get();

        $droits = DB::table('userdroits')->orderBy('userdroitid')->get();

        return view('parametre.utilisateur.index', compact('utilisateurs', 'droits'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'login' => 'required|string|max:255|unique:users,login',
            'password' => 'required|string|min:' . \App\Http\Controllers\MonCompteController::LONGUEUR_MIN,
            'userdroitid' => 'required|integer|exists:userdroits,userdroitid'
        ], self::MESSAGES);

        $siteid = $this->userSiteId();
        $societeid = auth()->user()->societeid ?? 1;

        DB::transaction(function () use ($request, $siteid, $societeid) {
            $this->lockIds(self::LOCK_USERS);
            $nextId = (DB::table('users')->max('userid') ?? 0) + 1;

            // Mot de passe stocké en clair : partagé avec l'application desktop historique
            DB::table('users')->insert([
                'userid' => $nextId,
                'login' => $request->login,
                'password' => $request->password,
                'userdroitid' => $request->userdroitid,
                'siteid' => $siteid,
                'societeid' => $societeid
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
            'userdroitid' => 'required|integer|exists:userdroits,userdroitid'
        ], self::MESSAGES);

        $data = [
            'login' => $request->login,
            'userdroitid' => $request->userdroitid
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
