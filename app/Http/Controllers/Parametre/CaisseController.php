<?php

namespace App\Http\Controllers\Parametre;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CaisseController extends Controller
{
    public function index()
    {
        $caisses = \Illuminate\Support\Facades\DB::table('caisses as c')
            ->leftJoin('sites as s', 'c.siteid', '=', 's.siteid')
            ->leftJoin('agencebs as a', 'c.agencebid', '=', 'a.agencebid')
            ->leftJoin('clients as cl', 'c.clientid', '=', 'cl.clientid')
            ->leftJoin('stations as st', 'c.machineid', '=', 'st.stationid')
            ->select(
                'c.*',
                's.libelle as site_libelle',
                'a.libelle as agence_libelle',
                'cl.nom as client_nom',
                'st.libelle as station_libelle'
            )
            ->orderBy('c.caisseid')
            ->get();

        $sites = \Illuminate\Support\Facades\DB::table('sites')->orderBy('siteid')->get();
        $agences = \Illuminate\Support\Facades\DB::table('agencebs')->orderBy('agencebid')->get();
        $clients = \Illuminate\Support\Facades\DB::table('clients')->orderBy('clientid')->get();
        $stations = \Illuminate\Support\Facades\DB::table('stations')->orderBy('stationid')->get();

        return view('parametre.caisse.index', compact('caisses', 'sites', 'agences', 'clients', 'stations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'libelle' => 'required|string',
            'compteur' => 'required|integer',
            'numero' => 'required|integer',
            'siteid' => 'required|integer',
            'agencebid' => 'nullable|integer',
            'clientid' => 'nullable|integer',
            'machineid' => 'nullable|integer',
        ]);

        $nextId = (\Illuminate\Support\Facades\DB::table('caisses')->max('caisseid') ?? 0) + 1;

        \Illuminate\Support\Facades\DB::table('caisses')->insert([
            'caisseid' => $nextId,
            'libelle' => $request->libelle,
            'compteur' => $request->compteur,
            'numero' => $request->numero,
            'siteid' => $request->siteid,
            'agencebid' => $request->agencebid,
            'clientid' => $request->clientid,
            'machineid' => $request->machineid,
            'bloque' => $request->has('bloque')
        ]);

        return redirect()->route('parametre.caisse.index')->with('success', 'Caisse ajoutée avec succès.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'libelle' => 'required|string',
            'compteur' => 'required|integer',
            'numero' => 'required|integer',
            'siteid' => 'required|integer',
            'agencebid' => 'nullable|integer',
            'clientid' => 'nullable|integer',
            'machineid' => 'nullable|integer',
        ]);

        \Illuminate\Support\Facades\DB::table('caisses')
            ->where('caisseid', $id)
            ->update([
                'libelle' => $request->libelle,
                'compteur' => $request->compteur,
                'numero' => $request->numero,
                'siteid' => $request->siteid,
                'agencebid' => $request->agencebid,
                'clientid' => $request->clientid,
                'machineid' => $request->machineid,
                'bloque' => $request->has('bloque')
            ]);

        return redirect()->route('parametre.caisse.index')->with('success', 'Caisse modifiée avec succès.');
    }

    public function destroy($id)
    {
        \Illuminate\Support\Facades\DB::table('caisses')->where('caisseid', $id)->delete();
        return redirect()->route('parametre.caisse.index')->with('success', 'Caisse supprimée avec succès.');
    }

    public function liberation()
    {
        $siteid = auth()->user()->siteid ?? 102; // Site de l'utilisateur connecté

        $caisses = \Illuminate\Support\Facades\DB::table('caisses as c')
            ->leftJoin('sites as s', 's.siteid', '=', 'c.siteid')
            ->select('c.caisseid', 'c.libelle', 's.libelle as site_libelle', 'c.machineid')
            ->whereNotNull('c.machineid')
            ->where('c.siteid', $siteid)
            ->orderBy('c.caisseid')
            ->get();

        return view('parametre.caisse.liberation', compact('caisses'));
    }

    public function liberer($id)
    {
        \Illuminate\Support\Facades\DB::table('caisses')
            ->where('caisseid', $id)
            ->update(['machineid' => null]);

        return redirect()->route('parametre.caisse.liberation')->with('success', 'La caisse a été libérée avec succès.');
    }}
