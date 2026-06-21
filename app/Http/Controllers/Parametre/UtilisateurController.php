<?php

namespace App\Http\Controllers\Parametre;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UtilisateurController extends Controller
{
    public function index()
    {
        $siteid = auth()->user()->siteid ?? 102;

        $utilisateurs = \Illuminate\Support\Facades\DB::table('users as u')
            ->leftJoin('userdroits as d', 'u.userdroitid', '=', 'd.userdroitid')
            ->where('u.siteid', $siteid)
            ->select('u.userid', 'u.login', 'u.userdroitid', 'd.libelle as droit_libelle')
            ->orderBy('u.userid')
            ->get();

        $droits = \Illuminate\Support\Facades\DB::table('userdroits')->orderBy('userdroitid')->get();

        return view('parametre.utilisateur.index', compact('utilisateurs', 'droits'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'login' => 'required|string|max:255',
            'password' => 'required|string',
            'userdroitid' => 'required|integer'
        ]);

        $nextId = (\Illuminate\Support\Facades\DB::table('users')->max('userid') ?? 0) + 1;
        $siteid = auth()->user()->siteid ?? 102;

        \Illuminate\Support\Facades\DB::table('users')->insert([
            'userid' => $nextId,
            'login' => $request->login,
            'password' => $request->password,
            'userdroitid' => $request->userdroitid,
            'siteid' => $siteid
        ]);

        return redirect()->route('parametre.utilisateur.index')->with('success', 'Utilisateur ajouté avec succès.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'login' => 'required|string|max:255',
            'userdroitid' => 'required|integer'
        ]);

        $data = [
            'login' => $request->login,
            'userdroitid' => $request->userdroitid
        ];

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        \Illuminate\Support\Facades\DB::table('users')
            ->where('userid', $id)
            ->update($data);

        return redirect()->route('parametre.utilisateur.index')->with('success', 'Utilisateur modifié avec succès.');
    }

    public function destroy($id)
    {
        \Illuminate\Support\Facades\DB::table('users')->where('userid', $id)->delete();
        return redirect()->route('parametre.utilisateur.index')->with('success', 'Utilisateur supprimé avec succès.');
    }
}
