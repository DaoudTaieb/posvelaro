<?php

namespace App\Http\Controllers\Transfert;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\View;

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
            ->where('b.siterecepteurid', $siteid); // On est le récepteur

        // Calcul des KPIs
        $kpiQuery = clone $query;
        $totalBons = $kpiQuery->count();
        $aTraiter = (clone $query)->whereIn('b.etatbontransfertid', [2])->count(); // Envoyé, en attente de réception
        $valides = (clone $query)->where('b.etatbontransfertid', 3)->count(); // Reçu/Validé

        $query->select(
            'emet.siteabrege as emetteur_code',
            'rec.siteabrege as recepteur_code',
            'emet.libelle as emetteur',
            'rec.libelle as recepteur',
            'b.bontransfertid',
            'b.bontransfertnumero as numero',
            'b.bontransfertdate as date',
            'b.totalqte as qte',
            'e.libelle as etat',
            'b.trajet',
            'v.libelle as vehicule',
            'v.matricule as matricule',
            'b.description',
            'b.etatbontransfertid'
        );

        // Global search
        if ($request->filled('search')) {
            $search = '%' . strtolower($request->search) . '%';
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(b.bontransfertnumero) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(emet.libelle) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(b.description) LIKE ?', [$search]);
            });
        }

        if ($request->filled('datedebut') && $request->filled('datefin')) {
            $query->whereBetween('b.bontransfertdate', [$request->datedebut, $request->datefin]);
        } elseif ($request->filled('datedebut')) {
            $query->whereDate('b.bontransfertdate', '>=', $request->datedebut);
        } elseif ($request->filled('datefin')) {
            $query->whereDate('b.bontransfertdate', '<=', $request->datefin);
        }

        if ($request->filled('siteid')) {
            $query->where('b.siteid', $request->siteid);
        }

        if ($request->filled('etatid') && $request->etatid !== 'tous') {
            $query->where('b.etatbontransfertid', $request->etatid);
        }

        // Column Filters
        $filters = [
            'f_numero' => 'b.bontransfertnumero',
            'f_date' => 'b.bontransfertdate',
            'f_emetteur' => 'emet.libelle',
            'f_trajet' => 'b.trajet',
            'f_vehicule' => 'v.libelle',
            'f_description' => 'b.description',
            'f_qte' => 'b.totalqte',
            'f_etat' => 'e.libelle'
        ];

        foreach ($filters as $param => $column) {
            if ($request->filled($param)) {
                $val = '%' . strtolower($request->$param) . '%';
                $query->whereRaw("CAST($column AS text) ILIKE ?", [$val]);
            }
        }

        $bontransferts = $query->orderBy('b.bontransfertdate', 'desc')
                               ->orderBy('b.bontransfertnumero', 'desc')
                               ->paginate($request->get('per_page', 20));

        if ($request->ajax()) {
            return response()->json([
                'html' => View::make('transfert.recu.partials.table', compact('bontransferts'))->render(),
                'pagination' => (string) $bontransferts->appends($request->all())->links('pagination::bootstrap-4'),
                'kpis' => [
                    'totalBons' => number_format($totalBons, 0, ',', ' '),
                    'aTraiter' => number_format($aTraiter, 0, ',', ' '),
                    'valides' => number_format($valides, 0, ',', ' ')
                ]
            ]);
        }

        $defaultDateDebut = $request->datedebut ?? Carbon::now()->format('Y-m-d');
        $defaultDateFin = $request->datefin ?? Carbon::now()->format('Y-m-d');

        return view('transfert.recu.index', compact('bontransferts', 'etats', 'defaultDateDebut', 'defaultDateFin', 'sites', 'siteLibelle', 'totalBons', 'aTraiter', 'valides'));
    }

    public function receptionner($id)
    {
        $bon = DB::table('bontransferts as b')
            ->join('sites as rec', 'rec.siteid', '=', 'b.siterecepteurid')
            ->join('sites as emet', 'emet.siteid', '=', 'b.siteid')
            ->leftJoin('vehicules as v', 'v.vehiculeid', '=', 'b.vehiculeid')
            ->leftJoin('etatbontranferts as e', 'e.etatbontransfertid', '=', 'b.etatbontransfertid')
            ->where('b.bontransfertid', $id)
            ->select(
                'b.*',
                'emet.libelle as emetteur',
                'rec.libelle as recepteur',
                'v.libelle as vehicule',
                'v.matricule',
                'e.libelle as etat'
            )
            ->first();

        if (!$bon) {
            return redirect()->route('transfert.recu.index')->with('error', 'Bon introuvable');
        }

        // We fetch lines. If vproduit2stocks doesn't match siterecepteurid easily, we just join on produit2id.
        // It's safer to just join produits to get reference and libelle without strictly filtering by siteid here,
        // because we just need to display the products being received.
        $lignes = DB::table('detbontransferts as det')
            ->join('produit2s as p2', 'p2.produit2id', '=', 'det.produit2id')
            ->join('produits as p', 'p.produitid', '=', 'p2.produitid')
            ->leftJoin('couleurs as c', 'c.couleurid', '=', 'p2.couleurid')
            ->leftJoin('tailles as t', 't.tailleid', '=', 'p2.tailleid')
            ->where('det.bontransfertid', $id)
            ->select(
                'det.*',
                'p.reference',
                'p.libelle',
                'c.libelle as couleur',
                't.libelle as taille'
            )
            ->get();

        return view('transfert.recu.receptionner', compact('bon', 'lignes'));
    }

    public function storeReception(Request $request, $id)
    {
        $receptions = $request->input('reception', []);
        
        foreach ($receptions as $detId => $data) {
            DB::table('detbontransferts')
                ->where('detbontransfertid', $detId)
                ->update([
                    'qterecu' => $data['qte_recue'] ?? 0,
                    'description' => $data['observation'] ?? null,
                    'pointer' => true
                ]);
        }
        
        DB::table('bontransferts')
            ->where('bontransfertid', $id)
            ->update(['etatbontransfertid' => 3]); // 3 = Reçu/Validé

        return redirect()->route('transfert.recu.index')->with('success', 'Bon de transfert réceptionné avec succès.');
    }
}
