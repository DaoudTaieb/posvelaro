<?php

namespace App\Http\Controllers\Parametre;

use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendeurController extends Controller
{
    use ParametreHelpers;

    public function index()
    {
        $siteid = $this->userSiteId(); // Site de l'utilisateur connecté

        $vendeurs = DB::table('employees')
            ->where('siteid', $siteid)
            ->orderBy('employeeid')
            ->get();
            
        return view('parametre.vendeur.index', compact('vendeurs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'tauxcommission' => 'nullable|numeric|min:0|max:100',
        ]);

        $siteid = $this->userSiteId(); // Site de l'utilisateur connecté

        DB::transaction(function () use ($request, $siteid) {
            $this->lockIds(self::LOCK_EMPLOYEES);

            // next ID
            $nextId = (DB::table('employees')->max('employeeid') ?? 0) + 1;
            $code = str_pad($nextId, 3, '0', STR_PAD_LEFT);

            DB::table('employees')->insert([
                'employeeid' => $nextId,
                'code' => $code,
                'nom' => $request->nom,
                'tauxcommission' => $request->tauxcommission ?? 0,
                'bloque' => $request->has('bloque') ? true : false,
                'isvendeur' => true,
                'siteid' => $siteid
            ]);
        });

        return redirect()->route('parametre.vendeur.index')->with('success', 'Vendeur ajouté avec succès.');
    }

    public function update(Request $request, $id)
    {
        $id = $this->routeId($id);

        $request->validate([
            'nom' => 'required|string|max:255',
            'tauxcommission' => 'nullable|numeric|min:0|max:100',
        ]);

        $updated = DB::table('employees')
            ->where('employeeid', $id)
            ->where('siteid', $this->userSiteId())
            ->update([
                'nom' => $request->nom,
                'tauxcommission' => $request->tauxcommission ?? 0,
                'bloque' => $request->has('bloque') ? true : false,
            ]);

        if (!$updated) {
            return redirect()->route('parametre.vendeur.index')->with('error', 'Vendeur introuvable.');
        }

        return redirect()->route('parametre.vendeur.index')->with('success', 'Vendeur modifié avec succès.');
    }

    public function destroy($id)
    {
        $id = $this->routeId($id);
        $siteid = $this->userSiteId();

        $exists = DB::table('employees')
            ->where('employeeid', $id)
            ->where('siteid', $siteid)
            ->exists();

        if (!$exists) {
            return redirect()->route('parametre.vendeur.index')->with('error', 'Vendeur introuvable.');
        }

        // Le vendeur lié au compte connecté sert aux ventes et à l'ouverture de caisse
        if ((int) $id === (int) auth()->user()->employeeid) {
            return redirect()->route('parametre.vendeur.index')->with('error', 'Impossible de supprimer ou bloquer le vendeur lié à votre propre compte.');
        }

        // ctickets / journalcaisses sont en ON DELETE CASCADE sur employees : on bloque au lieu de supprimer
        $hasHistory = DB::table('ctickets')->where('employeeid', $id)->exists()
            || DB::table('journalcaisses')->where('employeeid', $id)->exists();

        if ($hasHistory) {
            DB::table('employees')
                ->where('employeeid', $id)
                ->where('siteid', $siteid)
                ->update(['bloque' => true]);

            return redirect()->route('parametre.vendeur.index')->with('success', 'Vendeur bloqué (historique existant).');
        }

        try {
            DB::transaction(function () use ($id, $siteid) {
                DB::table('employees')
                    ->where('employeeid', $id)
                    ->where('siteid', $siteid)
                    ->delete();
            });
        } catch (QueryException $e) {
            return redirect()->route('parametre.vendeur.index')->with('error', 'Impossible de supprimer ce vendeur : il est référencé par d\'autres documents. Bloquez-le plutôt.');
        }

        return redirect()->route('parametre.vendeur.index')->with('success', 'Vendeur supprimé avec succès.');
    }
}
