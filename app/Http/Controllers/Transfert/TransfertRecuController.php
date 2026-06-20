<?php

namespace App\Http\Controllers\Transfert;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransfertRecuController extends Controller
{
    public function index(Request $request)
    {
        $siteid = auth()->user()->siteid ?? 102; // Nous sommes le récepteur

        $etats = DB::table('etatbontranferts')->get();
        $sites = DB::table('sites')->get();
        $siteRecepteur = DB::table('sites')->where('siteid', $siteid)->first();
        $siteLibelle = $siteRecepteur ? $siteRecepteur->libelle : 'Velaro';

        $query = DB::table('bontransferts as b')
            ->join('sites as rec', 'rec.siteid', '=', 'b.siterecepteurid')
            ->join('sites as emet', 'emet.siteid', '=', 'b.siteid')
            ->leftJoin('etatbontranferts as e', 'e.etatbontransfertid', '=', 'b.etatbontransfertid')
            ->leftJoin('vehicules as v', 'v.vehiculeid', '=', 'b.vehiculeid')
            ->where('b.siterecepteurid', $siteid) // On est le récepteur
            ->select(
                'emet.siteabrege as emetteur_code',
                'rec.siteabrege as recepteur_code',
                'emet.libelle as emetteur',
                'rec.libelle as recepteur',
                'b.bontransfertnumero as numero',
                'b.bontransfertdate as date',
                'b.totalqte as qte',
                'e.libelle as etat',
                'b.trajet',
                'v.libelle as vehicule',
                'v.matricule as matricule',
                'b.description'
            );

        if ($request->filled('datedebut')) {
            $query->whereDate('b.bontransfertdate', '>=', $request->datedebut);
        }
        if ($request->filled('datefin')) {
            $query->whereDate('b.bontransfertdate', '<=', $request->datefin);
        }

        if ($request->filled('siteid')) {
            $query->where('b.siteid', $request->siteid);
        }

        if ($request->filled('etatid') && $request->etatid !== 'tous') {
            $query->where('b.etatbontransfertid', $request->etatid);
        }

        $bontransferts = $query->orderBy('b.bontransfertdate', 'desc')
                               ->orderBy('b.bontransfertnumero', 'desc')
                               ->paginate($request->get('per_page', 20));

        $defaultDateDebut = $request->datedebut ?? Carbon::now()->format('Y-m-d');
        $defaultDateFin = $request->datefin ?? Carbon::now()->format('Y-m-d');

        return view('transfert.recu.index', compact('bontransferts', 'etats', 'defaultDateDebut', 'defaultDateFin', 'sites', 'siteLibelle'));
    }
}
