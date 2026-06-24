<?php

namespace App\Http\Controllers\Transfert;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\View;

class TransfertEnvoyeController extends Controller
{
    public function index(Request $request)
    {
        $siteid = auth()->user()->siteid ?? 102; // Expéditeur

        $etats = DB::table('etatdemandetransferts')->get(); // Ou etatbontransferts si existe

        $query = DB::table('bontransferts as b')
            ->join('sites as rec', 'rec.siteid', '=', 'b.siterecepteurid')
            ->join('sites as emet', 'emet.siteid', '=', 'b.siteid')
            ->leftJoin('etatdemandetransferts as e', 'e.etatdemandetransfertid', '=', 'b.etatbontransfertid') // On suppose que c'est la même table d'états
            ->where('b.siteid', $siteid); // On est l'émetteur

        // KPIs calculation (Base query without search filters)
        $kpiQuery = clone $query;
        $totalBons = $kpiQuery->count();
        $brouillons = (clone $query)->where('b.etatbontransfertid', 1)->count();
        $envoyes = (clone $query)->where('b.etatbontransfertid', 2)->count();

        $query->select(
            'emet.libelle as emetteur',
            'rec.libelle as recepteur',
            'b.bontransfertnumero as numero',
            'b.bontransfertdate as date',
            'b.totalqte as qte',
            'b.trajet',
            'b.description',
            'b.etatbontransfertid',
            'e.libelle as etat'
        );

        // Global search
        if ($request->filled('search')) {
            $search = '%' . strtolower($request->search) . '%';
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(b.bontransfertnumero) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(rec.libelle) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(b.description) LIKE ?', [$search]);
            });
        }

        // Advanced Filters
        if ($request->filled('datedebut') && $request->filled('datefin')) {
            $query->whereBetween('b.bontransfertdate', [$request->datedebut, $request->datefin]);
        } elseif ($request->filled('datedebut')) {
            $query->whereDate('b.bontransfertdate', '>=', $request->datedebut);
        } elseif ($request->filled('datefin')) {
            $query->whereDate('b.bontransfertdate', '<=', $request->datefin);
        }

        if ($request->filled('siterecepteurid')) {
            $query->where('b.siterecepteurid', $request->siterecepteurid);
        }

        if ($request->filled('etatid') && $request->etatid !== 'tous') {
            $query->where('b.etatbontransfertid', $request->etatid);
        }

        // Column Filters
        $filters = [
            'f_numero' => 'b.bontransfertnumero',
            'f_date' => 'b.bontransfertdate',
            'f_recepteur' => 'rec.libelle',
            'f_trajet' => 'b.trajet',
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
                'html' => View::make('transfert.envoye.partials.table', compact('bontransferts'))->render(),
                'pagination' => (string) $bontransferts->appends($request->all())->links('pagination::bootstrap-4'),
                'kpis' => [
                    'totalBons' => number_format($totalBons, 0, ',', ' '),
                    'brouillons' => number_format($brouillons, 0, ',', ' '),
                    'envoyes' => number_format($envoyes, 0, ',', ' ')
                ]
            ]);
        }

        $defaultDateDebut = $request->datedebut ?? Carbon::now()->format('Y-m-d');
        $defaultDateFin = $request->datefin ?? Carbon::now()->format('Y-m-d');

        $sites = DB::table('sites')->get();
        $siteExpediteur = DB::table('sites')->where('siteid', $siteid)->first();
        $siteLibelle = $siteExpediteur ? $siteExpediteur->libelle : 'Velaro';

        return view('transfert.envoye.index', compact('bontransferts', 'etats', 'defaultDateDebut', 'defaultDateFin', 'sites', 'siteLibelle', 'totalBons', 'brouillons', 'envoyes'));
    }

    public function create()
    {
        $siteid = auth()->user()->siteid ?? 102;
        $site = DB::table('sites')->where('siteid', $siteid)->first();
        $sites = DB::table('sites')->get();
        
        $demandes_validees = DB::table('demandetransferts')
            ->where('siterecepteurid', $siteid)
            ->where('etatdemandetransfertid', 3)
            ->get();

        // Données pour le modal de sélection produit
        $familles = DB::table('familles')->orderBy('famillelibelle')->get();
        $sousFamilles = DB::table('sousfamilles')->orderBy('sousfamillelibelle')->get();
        $categories = DB::table('categories')->orderBy('categorylibelle')->get();
        $marques = DB::table('categories2')->orderBy('category2libelle')->get(); // Marque
        $saisons = DB::table('categories4')->orderBy('category4libelle')->get(); // Saison
        $employees = DB::table('employees')->orderBy('nom')->get();
        $vehicules = DB::table('vehicules')->orderBy('libelle')->get();

        return view('transfert.envoye.create', compact('site', 'sites', 'demandes_validees', 'familles', 'sousFamilles', 'categories', 'marques', 'saisons', 'employees', 'vehicules'));
    }

    public function impressionMultiple(Request $request)
    {
        $siteid = auth()->user()->siteid ?? 102;
        $sites = DB::table('sites')->get();
        $siteExpediteur = DB::table('sites')->where('siteid', $siteid)->first();
        $employees = DB::table('employees')->orderBy('nom')->get();
        $vehicules = DB::table('vehicules')->orderBy('libelle')->get();

        $query = DB::table('bontransferts as b')
            ->join('sites as emet', 'emet.siteid', '=', 'b.siteid')
            ->join('sites as rec', 'rec.siteid', '=', 'b.siterecepteurid')
            ->leftJoin('etatbontranferts as e', 'e.etatbontransfertid', '=', 'b.etatbontransfertid')
            ->where('b.siteid', $siteid)
            ->select(
                'b.bontransfertid',
                'b.bontransfertnumero as numero',
                'b.bontransfertdate as date',
                'e.libelle as etat',
                'emet.libelle as expediteur',
                'rec.libelle as recepteur',
                'b.totalqte as qte'
            );

        if ($request->filled('datedebut')) {
            $query->whereDate('b.bontransfertdate', '>=', $request->datedebut);
        }
        if ($request->filled('datefin')) {
            $query->whereDate('b.bontransfertdate', '<=', $request->datefin);
        }
        if ($request->filled('siterecepteurid')) {
            $query->where('b.siterecepteurid', $request->siterecepteurid);
        }
        if ($request->filled('numero')) {
            $query->where('b.bontransfertnumero', $request->numero);
        }

        $bons = $query->orderBy('b.bontransfertdate', 'desc')->get();

        $defaultDateDebut = $request->datedebut ?? Carbon::now()->subMonths(1)->format('Y-m-d');
        $defaultDateFin = $request->datefin ?? Carbon::now()->format('Y-m-d');

        return view('transfert.envoye.impression_multiple', compact('bons', 'sites', 'siteExpediteur', 'employees', 'vehicules', 'defaultDateDebut', 'defaultDateFin'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'siterecepteurid' => 'required|integer',
        ], [
            'siterecepteurid.required' => 'Veuillez sélectionner un récepteur.',
        ]);

        $siteid = $request->siteid ?? auth()->user()->siteid ?? 102;
        $siterecepteurid = $request->siterecepteurid;
        $description = $request->description;
        $trajet = $request->trajet;
        $vehiculeid = $request->vehiculeid;
        $chauffeurid = $request->chauffeurid;
        $action_type = $request->input('action_type', 'save');
        
        $nouvel_etat = ($action_type === 'envoyer') ? 2 : 1; // 1 = Brouillon, 2 = Envoyé

        // Récupérer le prochain ID
        $id = (DB::table('bontransferts')->max('bontransfertid') ?? 0) + 1;

        // Numéro de séquence
        $numero = DB::table('bontransferts')->max('bontransfertnumero') + 1;
        $numeroInterne = 'BTE-' . date('Y') . '-' . str_pad($numero, 4, '0', STR_PAD_LEFT);

        DB::table('bontransferts')->insert([
            'bontransfertid' => $id,
            'siteid' => $siteid,
            'siterecepteurid' => $siterecepteurid,
            'etatbontransfertid' => $nouvel_etat,
            'bontransfertnumero' => $numero,
            'numerointerne' => $numeroInterne,
            'datecreation' => Carbon::now(),
            'bontransfertdate' => Carbon::now(),
            'datedebut' => Carbon::now(),
            'datefin' => Carbon::now(),
            'userid' => auth()->id() ?? 2,
            'description' => $description,
            'trajet' => $trajet,
            'vehiculeid' => $vehiculeid ? (int)$vehiculeid : null,
            'chauffeurid' => $chauffeurid ? (int)$chauffeurid : null,
            'confirmer' => false,
            'totalqte' => 0,
            'totalbrutht' => 0,
            'remise' => 0,
            'vremise' => 0,
            'totalnetht' => 0,
            'totaltva' => 0,
            'totalttc' => 0,
            'acompte' => 0,
            'netapayer' => 0,
            'modereceptionid' => 1,
            'typetransfertid' => 1
        ]);

        $totalqte = 0;
        $totalht = 0;
        $totalttc = 0;

        if ($request->has('lignes')) {
            $ordre = 1;
            foreach ($request->lignes as $ligne) {
                if (empty($ligne['produitid'])) continue;
                
                $qte = (int) $ligne['qte'];
                $qteenvoi = (int) ($ligne['qteenvoi'] ?? $qte);
                $prix = (float) ($ligne['prix'] ?? 0);
                $ht = $prix / 1.19;
                
                $totalqte += $qteenvoi; // on compte ce qu'on envoie
                $totalttc += ($prix * $qteenvoi);
                $totalht += ($ht * $qteenvoi);

                // Need detbontransfertid sequence
                $detid = DB::selectOne("SELECT nextval('detbontransferts_detbontransfertid_seq') as id")->id;

                DB::table('detbontransferts')->insert([
                    'detbontransfertid' => $detid,
                    'bontransfertid' => $id,
                    'siteid' => $siteid,
                    'siterecepteurid' => $siterecepteurid,
                    'produitid' => $ligne['produitid'],
                    'produit2id' => $ligne['produit2id'] ?? $ligne['produitid'],
                    'taxefamilleid' => 1,
                    'ht' => $ht,
                    'ttc' => $prix,
                    'qte' => $qte,
                    'qteenvoi' => $qteenvoi,
                    'qterecu' => 0,
                    'qteecart' => 0,
                    'etatbontransfertid' => $nouvel_etat,
                    'totalht' => $ht * $qteenvoi,
                    'remise' => 0,
                    'remise2' => 0,
                    'totalhtnet' => $ht * $qteenvoi,
                    'taxe1' => 0, 'vtaxe1' => 0,
                    'taxe2' => 0, 'vtaxe2' => 0,
                    'taxe3' => 0, 'vtaxe3' => 0,
                    'taxe4' => 0, 'vtaxe4' => 0,
                    'tva' => 19,
                    'vtva' => 0,
                    'totalttc' => $prix * $qteenvoi,
                    'totalttcnet' => $prix * $qteenvoi,
                    'date' => Carbon::now(),
                    'largeur' => 0, 'longueur' => 0, 'surface' => 0,
                    'pointer' => false,
                    'ordre' => $ordre++,
                    'prodid' => $ligne['produitid'],
                    'modestock' => 1,
                    'grammagegr' => 0,
                    'largeurmm' => 0,
                    'longueurm' => 0,
                    'modereceptionid' => 1,
                    'poids' => 0,
                    'stockorigineid' => 1
                ]);
            }

            // Mettre à jour l'en-tête
            DB::table('bontransferts')->where('bontransfertid', $id)->update([
                'totalqte' => $totalqte,
                'totalbrutht' => $totalht,
                'totalnetht' => $totalht,
                'totalttc' => $totalttc,
                'netapayer' => $totalttc
            ]);
        }

        return redirect()->route('transfert.envoye.index')->with('success', 'Bon de transfert enregistré avec succès !');
    }
}
