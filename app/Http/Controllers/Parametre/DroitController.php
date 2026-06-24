<?php

namespace App\Http\Controllers\Parametre;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DroitController extends Controller
{
    public function index(Request $request)
    {
        $roles = \Illuminate\Support\Facades\DB::table('userdroits')->orderBy('userdroitid')->get();
        $selectedRoleId = $request->id;
        $permissions = [];

        if ($selectedRoleId) {
            $permissions = \Illuminate\Support\Facades\DB::select("
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
        
        $nextId = (\Illuminate\Support\Facades\DB::table('userdroits')->max('userdroitid') ?? 0) + 1;
        
        \Illuminate\Support\Facades\DB::table('userdroits')->insert([
            'userdroitid' => $nextId,
            'libelle' => $request->libelle,
        ]);

        return redirect()->route('parametre.droit.index', ['id' => $nextId])->with('success', 'Rôle ajouté avec succès.');
    }

    public function updateRole(Request $request, $id)
    {
        $request->validate(['libelle' => 'required|string|max:255']);
        
        \Illuminate\Support\Facades\DB::table('userdroits')
            ->where('userdroitid', $id)
            ->update(['libelle' => $request->libelle]);

        return redirect()->route('parametre.droit.index', ['id' => $id])->with('success', 'Rôle modifié avec succès.');
    }

    public function destroyRole($id)
    {
        \Illuminate\Support\Facades\DB::table('userdroitdets')->where('userdroitid', $id)->delete();
        \Illuminate\Support\Facades\DB::table('userdroits')->where('userdroitid', $id)->delete();

        return redirect()->route('parametre.droit.index')->with('success', 'Rôle supprimé avec succès.');
    }

    public function updatePermissions(Request $request, $id)
    {
        $permissions = $request->input('permissions', []);
        
        // Delete all existing permissions for this role first
        \Illuminate\Support\Facades\DB::table('userdroitdets')->where('userdroitid', $id)->delete();
        
        $inserts = [];
        $nextDetId = (\Illuminate\Support\Facades\DB::table('userdroitdets')->max('userdroitdetid') ?? 0) + 1;
        
        foreach ($permissions as $typeId => $values) {
            $bloque = isset($values['bloque']) ? true : false;
            $badge = isset($values['badge']) ? true : false;
            
            if ($bloque || $badge) {
                $inserts[] = [
                    'userdroitdetid' => $nextDetId++,
                    'userdroitid' => $id,
                    'typedroitid' => $typeId,
                    'bloque' => $bloque,
                    'badge' => $badge
                ];
            }
        }
        
        if (!empty($inserts)) {
            \Illuminate\Support\Facades\DB::table('userdroitdets')->insert($inserts);
        }

        return redirect()->route('parametre.droit.index', ['id' => $id])->with('success', 'Droits mis à jour avec succès.');
    }}
