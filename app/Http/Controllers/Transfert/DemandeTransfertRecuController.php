<?php

namespace App\Http\Controllers\Transfert;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\View;

class DemandeTransfertRecuController extends Controller
{
    public function index(Request $request)
    {
        $siteid = \App\Support\SiteCourant::id(); // Le site actuel (le récepteur)

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
            ->where('d.etatdemandetransfertid', '!=', 1); // Cacher les brouillons

        // KPIs calculation (Base query without search filters)
        $kpiQuery = clone $query;
        $totalLignes = $kpiQuery->count();
        $aTraiter = (clone $query)->where('det.etatdemandetransfertid', 2)->count(); // 2: Envoyé/En attente
        $validees = (clone $query)->whereIn('det.etatdemandetransfertid', [3, 4, 5])->count();

        // Main Select
        $query->select(
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
            'det.detdemandetransfertid',
            'det.etatdemandetransfertid'
        );

        // Global search
        if ($request->filled('search')) {
            $search = '%' . strtolower($request->search) . '%';
            $query->where(function ($q) use ($search) {
                $q->whereRaw('CAST(d.demandetransfertnumero AS TEXT) ILIKE ?', [$search])
                  ->orWhereRaw('LOWER(demandeur.libelle) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(p.reference) LIKE ?', [$search]);
            });
        }

        // Advanced Filters (whereDate pour inclure toute la journée de fin)
        if ($request->filled('datedebut')) {
            $query->whereDate('d.demandetransfertdate', '>=', $request->datedebut);
        }
        if ($request->filled('datefin')) {
            $query->whereDate('d.demandetransfertdate', '<=', $request->datefin);
        }

        if ($request->filled('etatid') && $request->etatid !== 'tous') {
            $query->where('det.etatdemandetransfertid', $request->etatid);
        }

        // Column Filters
        $filters = [
            'f_numero' => 'd.demandetransfertnumero',
            'f_date' => 'd.demandetransfertdate',
            'f_demandeur' => 'demandeur.libelle',
            'f_reference' => 'p.reference',
            'f_couleur' => 'p.couleurlibelle',
            'f_taille' => 'p.taillelibelle',
            'f_etat' => 'etat.libelle',
            'f_qte_demandee' => 'det.qte',
            'f_stock' => 'p.qtestock',
            'f_qte_validee' => 'det.qteenvoi',
            'f_cause' => 'det.description'
        ];

        foreach ($filters as $param => $column) {
            if ($request->filled($param)) {
                $val = '%' . strtolower($request->$param) . '%';
                // Cast all to text for generic ilike match, including numeric
                $query->whereRaw("CAST($column AS text) ILIKE ?", [$val]);
            }
        }

        $demandes = $query->orderBy('d.demandetransfertdate', 'desc')
                          ->orderBy('d.demandetransfertnumero', 'desc')
                          ->paginate($request->get('per_page', 20));

        if ($request->ajax()) {
            return response()->json([
                'html' => View::make('transfert.demande_recu.partials.table', compact('demandes'))->render(),
                'pagination' => (string) $demandes->appends($request->all())->links('pagination::bootstrap-4'),
                'kpis' => [
                    'total' => number_format($totalLignes, 0, ',', ' '),
                    'atraiter' => number_format($aTraiter, 0, ',', ' '),
                    'validees' => number_format($validees, 0, ',', ' ')
                ]
            ]);
        }

        $defaultDateDebut = $request->datedebut ?? Carbon::now()->format('Y-m-d');
        $defaultDateFin = $request->datefin ?? Carbon::now()->format('Y-m-d');

        return view('transfert.demande_recu.index', compact('demandes', 'etats', 'defaultDateDebut', 'defaultDateFin', 'totalLignes', 'aTraiter', 'validees'));
    }

    public function pointer(Request $request)
    {
        $siteid = \App\Support\SiteCourant::id();
        $pointages = (array) $request->input('pointage', []);

        // Seules les lignes cochées (selected_demandes[] = detdemandetransfertid) sont pointées
        $selection = array_values(array_filter(array_map('intval', (array) $request->input('selected_demandes', []))));

        if (count($selection) === 0) {
            return redirect()->back()->with('error', 'Veuillez sélectionner au moins une ligne à pointer.');
        }

        $nbLignes = DB::transaction(function () use ($siteid, $pointages, $selection) {
            // Lignes envoyées (2) de demandes adressées à notre site et non annulées
            $lignes = DB::table('detdemandetransferts as det')
                ->join('demandetransferts as d', 'd.demandetransfertid', '=', 'det.demandetransfertid')
                ->whereIn('det.detdemandetransfertid', $selection)
                ->where('d.siterecepteurid', $siteid)
                ->where('d.etatdemandetransfertid', '!=', 4)
                ->where('det.etatdemandetransfertid', 2)
                ->select('det.detdemandetransfertid', 'det.demandetransfertid', 'det.qte')
                ->lockForUpdate()
                ->get();

            $demandesTouchees = [];

            foreach ($lignes as $ligne) {
                $data = $pointages[$ligne->detdemandetransfertid] ?? [];
                $qteValidee = max(0, min((float) ($data['qte_validee'] ?? 0), (float) $ligne->qte));

                DB::table('detdemandetransferts')
                    ->where('detdemandetransfertid', $ligne->detdemandetransfertid)
                    ->update([
                        'qteenvoi' => $qteValidee,
                        'description' => $data['cause'] ?? null,
                        'pointer' => true,
                        'etatdemandetransfertid' => 3 // Validé / Pointé
                    ]);

                $demandesTouchees[$ligne->demandetransfertid] = true;
            }

            if (count($demandesTouchees) > 0) {
                // On met aussi à jour l'en-tête de chaque demande concernée à Validé
                DB::table('demandetransferts')
                    ->whereIn('demandetransfertid', array_keys($demandesTouchees))
                    ->update(['etatdemandetransfertid' => 3]);
            }

            return count($lignes);
        });

        if ($nbLignes === 0) {
            return redirect()->back()->with('error', 'Aucune ligne pointable : seules les lignes envoyées des demandes adressées à votre site peuvent être pointées.');
        }

        return redirect()->back()->with('success', 'Pointage enregistré avec succès ! La demande est maintenant validée.');
    }
}
