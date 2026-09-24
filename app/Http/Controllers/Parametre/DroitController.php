<?php

namespace App\Http\Controllers\Parametre;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DroitController extends Controller
{
    use ParametreHelpers;

    public function index(Request $request)
    {
        $roles = DB::table('userdroits')->orderBy('userdroitid')->get();
        $selectedRoleId = $request->filled('id') ? $this->routeId($request->id) : null;
        $permissions = [];

        if ($selectedRoleId) {
            $permissions = DB::select("
                SELECT t.typedroitid, t.libelle, 
                       COALESCE(d.bloque, false) as bloque, 
                       COALESCE(d.badge, false) as badge
                FROM typedroits t
                LEFT JOIN userdroitdets d ON d.typedroitid = t.typedroitid AND d.userdroitid = ?
                ORDER BY t.typedroitid
            ", [$selectedRoleId]);
        }

        return view('parametre.droit.index', compact('roles', 'selectedRoleId', 'permissions'));
    }

    public function storeRole(Request $request)
    {
        $request->validate(['libelle' => 'required|string|max:255']);

        $nextId = DB::transaction(function () use ($request) {
            $this->lockIds(self::LOCK_USERDROITS);
            $nextId = (DB::table('userdroits')->max('userdroitid') ?? 0) + 1;

            DB::table('userdroits')->insert([
                'userdroitid' => $nextId,
                'libelle' => $request->libelle,
            ]);

            return $nextId;
        });

        return redirect()->route('parametre.droit.index', ['id' => $nextId])->with('success', 'Rôle ajouté avec succès.');
    }

    public function updateRole(Request $request, $id)
    {
        $id = $this->routeId($id);
        $request->validate(['libelle' => 'required|string|max:255']);

        $updated = DB::table('userdroits')
            ->where('userdroitid', $id)
            ->update(['libelle' => $request->libelle]);

        if (!$updated) {
            return redirect()->route('parametre.droit.index')->with('error', 'Rôle introuvable.');
        }

        return redirect()->route('parametre.droit.index', ['id' => $id])->with('success', 'Rôle modifié avec succès.');
    }

    public function destroyRole($id)
    {
        $id = $this->routeId($id);

        // users.userdroitid est en ON DELETE CASCADE (et users cascade vers ctickets/journalcaisses) :
        // on refuse la suppression d'un rôle encore attribué.
        $deleted = DB::transaction(function () use ($id) {
            // Verrouille le rôle : une affectation concurrente (FK) attendra la fin de la transaction
            DB::table('userdroits')->where('userdroitid', $id)->lockForUpdate()->first();

            if (DB::table('users')->where('userdroitid', $id)->exists()) {
                return false;
            }

            DB::table('userdroitdets')->where('userdroitid', $id)->delete();
            DB::table('userdroits')->where('userdroitid', $id)->delete();

            return true;
        });

        if (!$deleted) {
            return redirect()->route('parametre.droit.index', ['id' => $id])
                ->with('error', 'Impossible de supprimer ce rôle : il est attribué à au moins un utilisateur.');
        }

        return redirect()->route('parametre.droit.index')->with('success', 'Rôle supprimé avec succès.');
    }

    public function updatePermissions(Request $request, $id)
    {
        $id = $this->routeId($id);
        abort_unless(DB::table('userdroits')->where('userdroitid', $id)->exists(), 404);

        $permissions = $request->input('permissions', []);
        if (!is_array($permissions)) {
            $permissions = [];
        }

        // Ne garder que les types de droits existants
        $typeIds = DB::table('typedroits')->pluck('typedroitid')->flip();

        DB::transaction(function () use ($id, $permissions, $typeIds) {
            $this->lockIds(self::LOCK_USERDROITDETS);

            // Delete all existing permissions for this role first
            DB::table('userdroitdets')->where('userdroitid', $id)->delete();

            $inserts = [];
            $nextDetId = (DB::table('userdroitdets')->max('userdroitdetid') ?? 0) + 1;

            foreach ($permissions as $typeId => $values) {
                if (!$typeIds->has($typeId) || !is_array($values)) {
                    continue;
                }

                $bloque = isset($values['bloque']);
                $badge = isset($values['badge']);

                if ($bloque || $badge) {
                    $inserts[] = [
                        'userdroitdetid' => $nextDetId++,
                        'userdroitid' => $id,
                        'typedroitid' => (int) $typeId,
                        'bloque' => $bloque,
                        'badge' => $badge
                    ];
                }
            }

            if (!empty($inserts)) {
                DB::table('userdroitdets')->insert($inserts);
            }
        });

        return redirect()->route('parametre.droit.index', ['id' => $id])->with('success', 'Droits mis à jour avec succès.');
    }
}
