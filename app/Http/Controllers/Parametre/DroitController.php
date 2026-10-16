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
            $nextId = \App\Support\Identifiants::prochain('userdroits', 'userdroitid');

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

        // Accès généraux du rôle (cases « Accès à la caisse » / « Accès au back-office »)
        $indicateurs = null;
        if ($request->has('indicateurs_section')) {
            $indicateurs = [
                'notcaisse' => !$request->boolean('acces_caisse'),
                'notbackoffice' => !$request->boolean('acces_backoffice'),
            ];
        }

        // Anti-blocage : l'administrateur ne peut pas retirer à son propre rôle l'accès à cet écran
        if ((int) (auth()->user()->userdroitid ?? 0) === $id) {
            $typeGestionDroits = DB::table('typedroits')->where('abreviation', 'gestion.droits')->value('typedroitid');
            $seBloque = ($typeGestionDroits && isset($permissions[$typeGestionDroits]['bloque']))
                || ($indicateurs !== null && $indicateurs['notbackoffice']);
            if ($seBloque) {
                return redirect()->route('parametre.droit.index', ['id' => $id])
                    ->with('error', 'Impossible : ce rôle est le vôtre. Vous perdriez l\'accès à la gestion des droits (retirez ce droit depuis un autre compte administrateur).');
            }
        }

        DB::transaction(function () use ($id, $permissions, $typeIds, $indicateurs) {
            if ($indicateurs !== null) {
                DB::table('userdroits')->where('userdroitid', $id)->update($indicateurs);
            }

            // Delete all existing permissions for this role first
            DB::table('userdroitdets')->where('userdroitid', $id)->delete();

            $inserts = [];

            foreach ($permissions as $typeId => $values) {
                if (!$typeIds->has($typeId) || !is_array($values)) {
                    continue;
                }

                $bloque = isset($values['bloque']);
                $badge = isset($values['badge']);

                if ($bloque || $badge) {
                    $inserts[] = [
                        'userdroitid' => $id,
                        'typedroitid' => (int) $typeId,
                        'bloque' => $bloque,
                        'badge' => $badge
                    ];
                }
            }

            if (!empty($inserts)) {
                // Identifiants réservés en bloc, séquence du bureau réalignée
                $premier = \App\Support\Identifiants::plage('userdroitdets', 'userdroitdetid', count($inserts));
                foreach ($inserts as $i => &$ligne) {
                    $ligne['userdroitdetid'] = $premier + $i;
                }
                unset($ligne);
                DB::table('userdroitdets')->insert($inserts);
            }
        });

        return redirect()->route('parametre.droit.index', ['id' => $id])->with('success', 'Droits mis à jour avec succès.');
    }
}
