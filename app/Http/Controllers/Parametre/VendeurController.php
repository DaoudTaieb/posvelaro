<?php

namespace App\Http\Controllers\Parametre;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VendeurController extends Controller
{
    public function index()
    {
        $siteid = auth()->user()->siteid ?? 102; // Site de l'utilisateur connecté

        $vendeurs = \Illuminate\Support\Facades\DB::table('employees')
            ->where('siteid', $siteid)
            ->orderBy('employeeid')
            ->get();
            
        return view('parametre.vendeur.index', compact('vendeurs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
        ]);

        $siteid = auth()->user()->siteid ?? 102; // Site de l'utilisateur connecté

        // next ID
        $nextId = (\Illuminate\Support\Facades\DB::table('employees')->max('employeeid') ?? 0) + 1;
        $code = str_pad($nextId, 3, '0', STR_PAD_LEFT);

        \Illuminate\Support\Facades\DB::table('employees')->insert([
            'employeeid' => $nextId,
            'code' => $code,
            'nom' => $request->nom,
            'bloque' => $request->has('bloque') ? true : false,
            'isvendeur' => true,
            'siteid' => $siteid
        ]);

        return redirect()->route('parametre.vendeur.index')->with('success', 'Vendeur ajouté avec succès.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
        ]);

        \Illuminate\Support\Facades\DB::table('employees')
            ->where('employeeid', $id)
            ->update([
                'nom' => $request->nom,
                'bloque' => $request->has('bloque') ? true : false,
            ]);

        return redirect()->route('parametre.vendeur.index')->with('success', 'Vendeur modifié avec succès.');
    }}
