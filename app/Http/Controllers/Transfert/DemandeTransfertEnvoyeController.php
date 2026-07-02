<?php

namespace App\Http\Controllers\Transfert;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DemandeTransfertEnvoyeController extends Controller
{
    public function index(Request $request)
    {
        $siteid = auth()->user()->siteid ?? 102; // Sécurité si non connecté

        // Données pour les filtres
        $sites = DB::table('sites')->orderBy('libelle')->get();
        $etats = DB::table('etatdemandetransferts')->get();

        // Requête principale sur la vue
        $query = DB::table('demandetransfertsviews')
            ->where('siteid', $siteid);

        // Application des filtres de la barre d'outils
        if ($request->filled('datedebut') && $request->filled('datefin')) {
            $query->whereBetween('demandetransfertdate', [$request->datedebut, $request->datefin]);
        } elseif ($request->filled('datedebut')) {
            $query->where('demandetransfertdate', '>=', $request->datedebut);
        } elseif ($request->filled('datefin')) {
            $query->where('demandetransfertdate', '<=', $request->datefin);
        }

        if ($request->filled('siterecepteurid')) {
            $query->where('siterecepteurid', $request->siterecepteurid);
        }

        if ($request->filled('etatid') && $request->etatid !== 'tous') {
            $query->where('etatdemandetransfertid', $request->etatid);
        }

        // --- Filtres de colonnes AJAX ---
        if ($request->filled('f_emetteur')) {
            $query->whereRaw("CAST(site AS text) ILIKE ?", ['%' . $request->f_emetteur . '%']);
        }
        if ($request->filled('f_recepteur')) {
            $query->whereRaw("CAST(siterecepteur AS text) ILIKE ?", ['%' . $request->f_recepteur . '%']);
        }
        if ($request->filled('f_numero')) {
            $query->whereRaw("CAST(demandetransfertnumero AS text) ILIKE ?", ['%' . $request->f_numero . '%']);
        }
        if ($request->filled('f_date')) {
            $query->whereRaw("CAST(demandetransfertdate AS text) ILIKE ?", ['%' . $request->f_date . '%']);
        }
        if ($request->filled('f_etat')) {
            $query->whereRaw("CAST(etatlibelle AS text) ILIKE ?", ['%' . $request->f_etat . '%']);
        }
        if ($request->filled('f_trajet')) {
            $query->whereRaw("CAST(trajet AS text) ILIKE ?", ['%' . $request->f_trajet . '%']);
        }
        if ($request->filled('f_vehicule')) {
            $query->whereRaw("CAST(vehicule AS text) ILIKE ?", ['%' . $request->f_vehicule . '%']);
        }
        if ($request->filled('f_matricule')) {
            $query->whereRaw("CAST(matricule AS text) ILIKE ?", ['%' . $request->f_matricule . '%']);
        }
        if ($request->filled('f_description')) {
            $query->whereRaw("CAST(description AS text) ILIKE ?", ['%' . $request->f_description . '%']);
        }

        // Global search (si utilisé)
        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function($q) use ($search) {
                $q->whereRaw("CAST(demandetransfertnumero AS text) ILIKE ?", [$search])
                  ->orWhereRaw("CAST(description AS text) ILIKE ?", [$search]);
            });
        }

        // --- Calcul des KPIs avant pagination ---
        $totalDemandes = (clone $query)->count();
        $brouillons = (clone $query)->where('etatdemandetransfertid', 1)->count();
        $envoyes = (clone $query)->where('etatdemandetransfertid', 2)->count();

        // Pagination
        $perPage = $request->input('per_page', 20);
        $demandes = $query->orderBy('demandetransfertdate', 'desc')
                          ->orderBy('demandetransfertnumero', 'desc')
                          ->paginate($perPage);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('transfert.demande_envoye.partials.table', compact('demandes'))->render(),
                'pagination' => (string) $demandes->appends($request->all())->links('pagination::bootstrap-4'),
                'kpis' => [
                    'totalDemandes' => number_format($totalDemandes, 0, ',', ' '),
                    'brouillons' => number_format($brouillons, 0, ',', ' '),
                    'envoyes' => number_format($envoyes, 0, ',', ' ')
                ]
            ]);
        }

        // Dates par défaut pour le formulaire
        $defaultDateDebut = $request->datedebut ?? Carbon::now()->format('Y-m-d');
        $defaultDateFin = $request->datefin ?? Carbon::now()->format('Y-m-d');

        return view('transfert.demande_envoye.index', compact('demandes', 'sites', 'etats', 'defaultDateDebut', 'defaultDateFin', 'totalDemandes', 'brouillons', 'envoyes'));
    }

    public function create()
    {
        $site = DB::table('sites')->where('siteid', auth()->user()->siteid ?? 102)->first();
        $sites = DB::table('sites')->orderBy('libelle')->get();
        
        // Données pour le modal de sélection produit
        $familles = DB::table('familles')->orderBy('famillelibelle')->get();
        $sousFamilles = DB::table('sousfamilles')->orderBy('sousfamillelibelle')->get();
        $saisons = DB::table('categories4')->orderBy('category4libelle')->get();
        $categories = DB::table('categories')->orderBy('categorylibelle')->get();
        $marques = DB::table('categories2')->orderBy('category2libelle')->get();

        return view('transfert.demande_envoye.create', compact('site', 'sites', 'familles', 'sousFamilles', 'saisons', 'categories', 'marques'));
    }

    public function store(Request $request)
    {
        $siteid = $request->siteid ?? auth()->user()->siteid ?? 102;
        $siterecepteurid = $request->siterecepteurid;
        $description = $request->description;
        $action_type = $request->input('action_type', 'save');
        
        $nouvel_etat = ($action_type === 'envoyer') ? 2 : 1; // 1 = Brouillon, 2 = Envoyé

        if ($request->filled('demandetransfertid')) {
            $id = $request->demandetransfertid;
            
            DB::table('demandetransferts')
                ->where('demandetransfertid', $id)
                ->update([
                    'description' => $description,
                    'etatdemandetransfertid' => $nouvel_etat
                ]);

            // Clear old lines to replace them
            DB::table('detdemandetransferts')->where('demandetransfertid', $id)->delete();
            $numero = DB::table('demandetransferts')->where('demandetransfertid', $id)->value('demandetransfertnumero');
        } else {
            // Récupérer le prochain ID depuis la séquence
            $id = DB::selectOne("SELECT nextval('demandetransfert_demandetransfertid_seq') as id")->id;

            // Générer un numéro de séquence (simplifié)
            $numero = DB::table('demandetransferts')->max('demandetransfertnumero') + 1;
            $numeroInterne = 'DTE-' . date('Y') . '-' . str_pad($numero, 4, '0', STR_PAD_LEFT);

            DB::table('demandetransferts')->insert([
                'demandetransfertid' => $id,
                'siteid' => $siteid,
                'siterecepteurid' => $siterecepteurid,
                'etatdemandetransfertid' => $nouvel_etat, // 1 = Brouillon, 2 = Envoyé
                'demandetransfertnumero' => $numero,
                'numerointerne' => $numeroInterne,
                'datecreation' => Carbon::now(),
                'demandetransfertdate' => Carbon::now(),
                'datedebut' => Carbon::now(),
                'datefin' => Carbon::now(),
                'userid' => auth()->id() ?? 2,
                'description' => $description,
                'confirmer' => false,
                'totalqte' => 0,
                'totalbrutht' => 0,
                'remise' => 0,
                'vremise' => 0,
                'totalnetht' => 0,
                'totaltva' => 0,
                'totalttc' => 0,
                'acompte' => 0,
                'netapayer' => 0
            ]);
        }

        $lignes = $request->input('lignes', []);
        $totalqte = 0;
        $totalht = 0;
        $totalttc = 0;

        foreach ($lignes as $index => $ligne) {
            $qte = (int) $ligne['qte'];
            $prix = (float) ($ligne['prix'] ?? 0);
            $ht = $prix / 1.19; // Assuming standard 19% TVA for generic calculation if not provided
            $totalqte += $qte;
            $totalttc += ($prix * $qte);
            $totalht += ($ht * $qte);

            DB::table('detdemandetransferts')->insert([
                'demandetransfertid' => $id,
                'siteid' => $siteid,
                'siterecepteurid' => $siterecepteurid,
                'produitid' => $ligne['produitid'],
                'produit2id' => $ligne['produit2id'] ?? $ligne['produitid'],
                'taxefamilleid' => 1,
                'ht' => $ht,
                'ttc' => $prix,
                'qte' => $qte,
                'qteenvoi' => 0,
                'qterecu' => 0,
                'etatdemandetransfertid' => $nouvel_etat,
                'totalht' => $ht * $qte,
                'remise' => 0,
                'remise2' => 0,
                'totalhtnet' => $ht * $qte,
                'taxe1' => 0, 'vtaxe1' => 0,
                'taxe2' => 0, 'vtaxe2' => 0,
                'taxe3' => 0, 'vtaxe3' => 0,
                'taxe4' => 0, 'vtaxe4' => 0,
                'tva' => 19,
                'vtva' => 0,
                'totalttc' => $prix * $qte,
                'totalttcnet' => $prix * $qte,
                'qteecart' => 0,
                'qteenvoi' => 0,
                'qterecu' => 0,
                'date' => Carbon::now(),
                'largeur' => 0,
                'longueur' => 0,
                'surface' => 0,
                'pointer' => false,
                'ordre' => $index + 1,
                'prodid' => 0, // prodid is integer, produitid is bigint, using 0 to bypass constraint
                'modestock' => 1
            ]);
        }

        // Update totals
        if (count($lignes) > 0) {
            DB::table('demandetransferts')
                ->where('demandetransfertid', $id)
                ->update([
                    'totalqte' => $totalqte,
                    'totalbrutht' => $totalht,
                    'totalnetht' => $totalht,
                    'totalttc' => $totalttc,
                    'netapayer' => $totalttc
                ]);
        }

        return redirect()->route('transfert.demande_envoye.index')->with('success', 'En-tête de la demande enregistré avec succès ! (N° '.$numero.')');
    }

    public function edit($id)
    {
        $demande = DB::table('demandetransferts')->where('demandetransfertid', $id)->first();
        if (!$demande) {
            return redirect()->route('transfert.demande_envoye.index')->with('error', 'Demande introuvable.');
        }

        $lignes = DB::table('detdemandetransferts as det')
            ->join('vproduit2stocks as p', function($join) use ($demande) {
                $join->on('p.produit2id', '=', 'det.produit2id')
                     ->where('p.siteid', $demande->siteid);
            })
            ->where('det.demandetransfertid', $id)
            ->select(
                'det.*',
                'p.reference',
                'p.produitcode',
                'p.produitlibelle',
                'p.taillelibelle',
                'p.couleurlibelle',
                'p.ttc_vente'
            )
            ->get();

        $sites = DB::table('sites')->orderBy('libelle')->get();
        $site = DB::table('sites')->where('siteid', auth()->user()->siteid ?? 102)->first();

        // Data for modal
        $sousFamilles = DB::table('sousfamilles')->orderBy('sousfamillelibelle')->get();
        $familles = DB::table('familles')->orderBy('famillelibelle')->get();
        $saisons = DB::table('categories4')->orderBy('category4libelle')->get();
        $categories = DB::table('categories')->orderBy('categorylibelle')->get();
        $marques = DB::table('categories2')->orderBy('category2libelle')->get();

        // For simplicity, reusing the create view and passing the existing data
        return view('transfert.demande_envoye.create', compact('sites', 'site', 'sousFamilles', 'familles', 'saisons', 'categories', 'marques', 'demande', 'lignes'));
    }

    public function destroy($id)
    {
        DB::table('detdemandetransferts')->where('demandetransfertid', $id)->delete();
        DB::table('demandetransferts')->where('demandetransfertid', $id)->delete();

        return redirect()->route('transfert.demande_envoye.index')->with('success', 'Demande supprimée avec succès.');
    }

    public function searchProducts(Request $request)
    {
        $siteid = auth()->user()->siteid ?? 102;

        $query = DB::table('vproduit2stocks')
            ->where('siteid', $siteid)
            ->select(
                'produitid',
                'produit2id',
                'produitcode',
                'reference',
                'barcode2',
                'produitlibelle',
                'taillelibelle',
                'couleurlibelle',
                'famillelibelle',
                'sousfamillelibelle',
                'ttc_vente',
                'fournisseur',
                'qtestock as total_stock'
            );

        if ($request->filled('sousfamilleid')) {
            $query->where('sousfamilleid', $request->sousfamilleid);
        }
        if ($request->filled('familleid')) {
            $query->where('familleid', $request->familleid);
        }
        if ($request->filled('saisonid')) {
            $query->where('category4id', $request->saisonid);
        }
        if ($request->filled('categoryid')) {
            $query->where('categoryid', $request->categoryid);
        }
        if ($request->filled('marqueid')) {
            $query->where('category2id', $request->marqueid);
        }
        if ($request->filled('search')) {
            $searchTerm = '%' . strtolower($request->search) . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->whereRaw('LOWER(produitcode) LIKE ?', [$searchTerm])
                  ->orWhereRaw('LOWER(reference) LIKE ?', [$searchTerm])
                  ->orWhereRaw('LOWER(barcode2) LIKE ?', [$searchTerm])
                  ->orWhereRaw('LOWER(produitlibelle) LIKE ?', [$searchTerm]);
            });
        }

        $products = $query->limit(50)->get();

        return response()->json($products);
    }
}
