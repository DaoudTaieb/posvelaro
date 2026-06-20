<?php

namespace App\Http\Controllers\Transfert;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DemandeTransfertRecuController extends Controller
{
    public function index(Request $request)
    {
        $siteid = auth()->user()->siteid ?? 102; // Le site actuel (le récepteur)

        $etats = DB::table('etatdemandetransferts')->get();

        $query = DB::table('detdemandetransferts as det')
            ->join('demandetransferts as d', 'd.demandetransfertid', '=', 'det.demandetransfertid')
            ->join('sites as demandeur', 'demandeur.siteid', '=', 'd.siteid')
            ->leftJoin('etatdemandetransferts as etat', 'etat.etatdemandetransfertid', '=', 'det.etatdemandetransfertid')
            ->leftJoin('vproduit2stocks as p', function($join) {
                $join->on('p.produit2id', '=', 'det.produit2id')
                     ->whereRaw('p.siteid = det.siterecepteurid'); // On veut NOTRE stock
            })
            ->where('d.siterecepteurid', $siteid) // Nous sommes le récepteur
            ->where('d.etatdemandetransfertid', '!=', 1) // Cacher les brouillons
            ->select(
                'd.demandetransfertnumero',
                'd.demandetransfertdate',
                'demandeur.libelle as demandeur',
                'p.reference',
                'p.couleurlibelle as couleur',
                'p.taillelibelle as taille',
                'det.qte as qte_demandee',
                'p.qtestock as stock',
                'etat.libelle as etat',
                'det.description as cause',
                'det.qteenvoi as qte_validee',
                'det.detdemandetransfertid'
            );

        if ($request->filled('datedebut') && $request->filled('datefin')) {
            $query->whereBetween('d.demandetransfertdate', [$request->datedebut, $request->datefin]);
        } elseif ($request->filled('datedebut')) {
            $query->where('d.demandetransfertdate', '>=', $request->datedebut);
        } elseif ($request->filled('datefin')) {
            $query->where('d.demandetransfertdate', '<=', $request->datefin);
        }

        if ($request->filled('etatid') && $request->etatid !== 'tous') {
            $query->where('det.etatdemandetransfertid', $request->etatid);
        }

        $demandes = $query->orderBy('d.demandetransfertdate', 'desc')
                          ->orderBy('d.demandetransfertnumero', 'desc')
                          ->paginate($request->get('per_page', 20));

        $defaultDateDebut = $request->datedebut ?? Carbon::now()->format('Y-m-d');
        $defaultDateFin = $request->datefin ?? Carbon::now()->format('Y-m-d');

        return view('transfert.demande_recu.index', compact('demandes', 'etats', 'defaultDateDebut', 'defaultDateFin'));
    }

    public function pointer(Request $request)
    {
        $pointages = $request->input('pointage', []);
        $demandetransfertid = null;

        foreach ($pointages as $id => $data) {
            DB::table('detdemandetransferts')
                ->where('detdemandetransfertid', $id)
                ->update([
                    'qteenvoi' => $data['qte_validee'] ?? 0,
                    'description' => $data['cause'] ?? null,
                    'pointer' => true,
                    'etatdemandetransfertid' => 3 // Validé / Pointé
                ]);
                
            if (!$demandetransfertid) {
                $demandetransfertid = DB::table('detdemandetransferts')->where('detdemandetransfertid', $id)->value('demandetransfertid');
            }
        }
        
        if ($demandetransfertid) {
            // Optionnel : on met aussi à jour l'en-tête global à Validé
            DB::table('demandetransferts')
                ->where('demandetransfertid', $demandetransfertid)
                ->update(['etatdemandetransfertid' => 3]);
        }

        return redirect()->back()->with('success', 'Pointage enregistré avec succès ! La demande est maintenant validée.');
    }
}
