<?php

namespace App\Http\Controllers\Transfert;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\View;

class TransfertEnvoyeController extends Controller
{
    // Clé du verrou consultatif PostgreSQL qui sérialise la numérotation des bons (max()+1)
    private const LOCK_NUMEROTATION_BON = 730101;

    public function index(Request $request)
    {
        $siteid = \App\Support\SiteCourant::id(); // Expéditeur

        $query = DB::table('bontransferts as b')
            ->join('sites as rec', 'rec.siteid', '=', 'b.siterecepteurid')
            ->join('sites as emet', 'emet.siteid', '=', 'b.siteid')
            ->leftJoin('etatbontranferts as e', 'e.etatbontransfertid', '=', 'b.etatbontransfertid')
            ->where('b.siteid', $siteid); // On est l'émetteur

        // Indicateurs (sans les filtres de recherche) : les trois compteurs en une seule requête
        $kpi = (clone $query)->selectRaw(
            'count(*) AS total, count(*) FILTER (WHERE b.etatbontransfertid = 1) AS brouillons, count(*) FILTER (WHERE b.etatbontransfertid = 2) AS envoyes'
        )->first();
        $totalBons = (int) $kpi->total;
        $brouillons = (int) $kpi->brouillons;
        $envoyes = (int) $kpi->envoyes;

        $query->select(
            'b.bontransfertid',
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
                $q->whereRaw('CAST(b.bontransfertnumero AS TEXT) ILIKE ?', [$search])
                  ->orWhereRaw('LOWER(rec.libelle) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(b.description) LIKE ?', [$search]);
            });
        }

        // Advanced Filters (whereDate pour inclure toute la journée de fin)
        if ($request->filled('datedebut')) {
            $query->whereDate('b.bontransfertdate', '>=', $request->datedebut);
        }
        if ($request->filled('datefin')) {
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
                               ->paginerVite($request->get('per_page', 20));

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

        // États (filtre) et sites en une seule requête
        ['etats' => $etats, 'sites' => $sites] = \App\Support\LectureGroupee::listes([
            'etats' => DB::table('etatbontranferts'),
            'sites' => DB::table('sites'),
        ]);
        $siteExpediteur = $sites->firstWhere('siteid', $siteid);
        $siteLibelle = $siteExpediteur ? $siteExpediteur->libelle : 'Velaro';

        return view('transfert.envoye.index', compact('bontransferts', 'etats', 'defaultDateDebut', 'defaultDateFin', 'sites', 'siteLibelle', 'totalBons', 'brouillons', 'envoyes'));
    }

    public function create()
    {
        $siteid = \App\Support\SiteCourant::id();
        $site = DB::table('sites')->where('siteid', $siteid)->first();
        $sites = DB::table('sites')->get();

        $demandes_validees = $this->demandesAImporter($siteid);

        // Données pour le modal de sélection produit
        // Listes de référence lues en une seule requête (base distante : un aller-retour au lieu de 7)
        ['familles' => $familles, 'sousFamilles' => $sousFamilles, 'categories' => $categories, 'marques' => $marques, 'saisons' => $saisons, 'employees' => $employees, 'vehicules' => $vehicules] = \App\Support\LectureGroupee::listes([
            'familles' => DB::table('familles')->select('familleid', 'famillelibelle')->orderBy('famillelibelle'),
            'sousFamilles' => DB::table('sousfamilles')->select('sousfamilleid', 'sousfamillelibelle', 'familleid', 'is_loyalty_enabled')->orderBy('sousfamillelibelle'),
            'categories' => DB::table('categories')->orderBy('categorylibelle'),
            'marques' => DB::table('categories2')->orderBy('category2libelle'), // Marque
            'saisons' => DB::table('categories4')->orderBy('category4libelle'), // Saison
            'employees' => DB::table('employees')->select('employeeid', 'code', 'nom', 'prenom', 'siteid', 'isvendeur', 'bloque')->orderBy('nom'),
            'vehicules' => DB::table('vehicules')->orderBy('libelle'),
        ]);

        return view('transfert.envoye.create', compact('site', 'sites', 'demandes_validees', 'familles', 'sousFamilles', 'categories', 'marques', 'saisons', 'employees', 'vehicules'));
    }

    public function impressionMultiple(Request $request)
    {
        $siteid = \App\Support\SiteCourant::id();
        $sites = DB::table('sites')->get();
        $siteExpediteur = DB::table('sites')->where('siteid', $siteid)->first();
        // Listes de référence lues en une seule requête (base distante : un aller-retour au lieu de 2)
        ['employees' => $employees, 'vehicules' => $vehicules] = \App\Support\LectureGroupee::listes([
            'employees' => DB::table('employees')->select('employeeid', 'code', 'nom', 'prenom', 'siteid', 'isvendeur', 'bloque')->orderBy('nom'),
            'vehicules' => DB::table('vehicules')->orderBy('libelle'),
        ]);

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
        $request->validate(array_merge([
            'siterecepteurid' => 'required|integer',
        ], $this->reglesLignes()), array_merge([
            'siterecepteurid.required' => 'Veuillez sélectionner un récepteur.',
        ], $this->messagesLignes()));

        // Le site expéditeur est toujours celui de l'utilisateur connecté
        $siteid = \App\Support\SiteCourant::id();
        $siterecepteurid = (int) $request->siterecepteurid;

        if ($siteid == $siterecepteurid) {
            return back()
                ->with('error', "Le site expéditeur et le site récepteur ne peuvent pas être identiques.")
                ->withInput();
        }

        $description = $request->description;
        $trajet = $request->trajet;
        $vehiculeid = $request->vehiculeid;
        $chauffeurid = $request->chauffeurid;
        $action_type = $request->input('action_type', 'save');

        $nouvel_etat = ($action_type === 'envoyer') ? 2 : 1; // 1 = Brouillon, 2 = Envoyé

        $lignes = $this->normaliserLignes($request->input('lignes', []));
        if ($erreur = $this->verifierQuantites($lignes)) {
            return back()->with('error', $erreur)->withInput();
        }
        if ($nouvel_etat === 2 && ($erreur = $this->verifierStock($siteid, $lignes))) {
            return back()->with('error', $erreur)->withInput();
        }

        DB::transaction(function () use ($request, $siteid, $siterecepteurid, $nouvel_etat, $description, $trajet, $vehiculeid, $chauffeurid, $lignes) {
            // Sérialiser le calcul max()+1 pour éviter les doublons d'ID / de numéro
            DB::statement('SELECT pg_advisory_xact_lock(?)', [self::LOCK_NUMEROTATION_BON]);

            [$lignes, $demandetransfertid] = $this->lierDemandes($lignes, $siteid, $siterecepteurid, $request->input('demandetransfertid'));

            // Identifiant sûr pour le logiciel de bureau (max()+1 et séquence, séquence réalignée)
            $id = \App\Support\Identifiants::prochain('bontransferts', 'bontransfertid');

            // Numéro de séquence
            $numero = (DB::table('bontransferts')->max('bontransfertnumero') ?? 0) + 1;
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
                'demandetransfertid' => $demandetransfertid,
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

            if (count($lignes) > 0) {
                $totaux = $this->insererLignes($id, $siteid, $siterecepteurid, $nouvel_etat, $lignes);

                // Mettre à jour l'en-tête
                DB::table('bontransferts')->where('bontransfertid', $id)->update($totaux);
            }
        });

        return redirect()->route('transfert.envoye.index')->with('success', 'Bon de transfert enregistré avec succès !');
    }

    public function edit($id)
    {
        $siteid = \App\Support\SiteCourant::id();

        $bon = DB::table('bontransferts')->where('bontransfertid', $id)->first();
        if (!$bon) {
            return redirect()->route('transfert.envoye.index')->with('error', 'Bon de transfert introuvable.');
        }
        if ($bon->siteid != $siteid || $bon->etatbontransfertid != 1) {
            return back()->with('error', 'Seuls les bons de transfert en brouillon de votre site peuvent être modifiés.');
        }

        $lignes = DB::table('detbontransferts as d')
            ->leftJoin('vproduit2stocks as p', function($join) use ($bon) {
                $join->on('p.produit2id', '=', 'd.produit2id')
                     ->where('p.siteid', $bon->siteid);
            })
            ->where('d.bontransfertid', $id)
            ->select(
                'd.*',
                'p.produitcode',
                'p.produitlibelle as designation',
                'p.taillelibelle as taille',
                'p.couleurlibelle as couleur'
            )
            ->orderBy('d.ordre')
            ->get();

        $sites = DB::table('sites')->get();
        $site = DB::table('sites')->where('siteid', $bon->siteid)->first();

        // Listes de référence lues en une seule requête (base distante : un aller-retour au lieu de 2)
        ['employees' => $employees, 'vehicules' => $vehicules] = \App\Support\LectureGroupee::listes([
            'employees' => DB::table('employees')->select('employeeid', 'code', 'nom', 'prenom', 'siteid', 'isvendeur', 'bloque')->orderBy('nom'),
            'vehicules' => DB::table('vehicules')->orderBy('libelle'),
        ]);

        // Pour la modal
        // Listes de référence lues en une seule requête (base distante : un aller-retour au lieu de 5)
        ['familles' => $familles, 'sousFamilles' => $sousFamilles, 'categories' => $categories, 'marques' => $marques, 'saisons' => $saisons] = \App\Support\LectureGroupee::listes([
            'familles' => DB::table('familles')->select('familleid', 'famillelibelle')->orderBy('famillelibelle'),
            'sousFamilles' => DB::table('sousfamilles')->select('sousfamilleid', 'sousfamillelibelle', 'familleid', 'is_loyalty_enabled')->orderBy('sousfamillelibelle'),
            'categories' => DB::table('categories')->orderBy('categorylibelle'),
            'marques' => DB::table('categories2')->orderBy('category2libelle'), // Marque
            'saisons' => DB::table('categories4')->orderBy('category4libelle'), // Saison
        ]);

        $demandes_validees = $this->demandesAImporter($siteid);

        return view('transfert.envoye.edit', compact('bon', 'lignes', 'sites', 'site', 'employees', 'vehicules', 'familles', 'sousFamilles', 'categories', 'marques', 'saisons', 'demandes_validees'));
    }

    public function update(Request $request, $id)
    {
        $request->validate($this->reglesLignes(), $this->messagesLignes());

        $siteid = \App\Support\SiteCourant::id();
        $description = $request->description;
        $trajet = $request->trajet;
        $vehiculeid = $request->vehiculeid;
        $chauffeurid = $request->chauffeurid;
        $action_type = $request->input('action_type', 'save');

        $nouvel_etat = ($action_type === 'envoyer') ? 2 : 1; // 1 = Brouillon, 2 = Envoyé

        $lignes = $this->normaliserLignes($request->input('lignes', []));
        if ($erreur = $this->verifierQuantites($lignes)) {
            return back()->with('error', $erreur)->withInput();
        }

        return DB::transaction(function () use ($request, $id, $siteid, $description, $trajet, $vehiculeid, $chauffeurid, $nouvel_etat, $lignes) {
            $bon = DB::table('bontransferts')->where('bontransfertid', $id)->lockForUpdate()->first();
            if (!$bon) {
                return redirect()->route('transfert.envoye.index')->with('error', 'Bon de transfert introuvable.');
            }
            if ($bon->siteid != $siteid || $bon->etatbontransfertid != 1) {
                return back()->with('error', 'Seuls les bons de transfert en brouillon de votre site peuvent être modifiés.');
            }

            if ($nouvel_etat === 2 && ($erreur = $this->verifierStock($bon->siteid, $lignes))) {
                return back()->with('error', $erreur)->withInput();
            }

            [$lignes, $demandetransfertid] = $this->lierDemandes($lignes, $bon->siteid, $bon->siterecepteurid, $request->input('demandetransfertid', $bon->demandetransfertid), $bon->bontransfertid);

            DB::table('bontransferts')->where('bontransfertid', $id)->update([
                'description' => $description,
                'trajet' => $trajet,
                'vehiculeid' => $vehiculeid ? (int)$vehiculeid : null,
                'chauffeurid' => $chauffeurid ? (int)$chauffeurid : null,
                'demandetransfertid' => $demandetransfertid,
                'etatbontransfertid' => $nouvel_etat,
            ]);

            // Supprimer les anciennes lignes (brouillon : aucun impact sur le stock)
            DB::table('detbontransferts')->where('bontransfertid', $id)->delete();

            $totaux = count($lignes) > 0
                ? $this->insererLignes($bon->bontransfertid, $bon->siteid, $bon->siterecepteurid, $nouvel_etat, $lignes)
                : ['totalqte' => 0, 'totalbrutht' => 0, 'totalnetht' => 0, 'totaltva' => 0, 'totalttc' => 0, 'netapayer' => 0];

            DB::table('bontransferts')->where('bontransfertid', $id)->update($totaux);

            return redirect()->route('transfert.envoye.index')->with('success', 'Bon de transfert mis à jour avec succès !');
        });
    }

    public function destroy($id)
    {
        $siteid = \App\Support\SiteCourant::id();

        try {
            return DB::transaction(function () use ($id, $siteid) {
                $bon = DB::table('bontransferts')->where('bontransfertid', $id)->lockForUpdate()->first();
                if (!$bon) {
                    return redirect()->route('transfert.envoye.index')->with('error', 'Bon de transfert introuvable.');
                }
                if ($bon->siteid != $siteid || $bon->etatbontransfertid != 1) {
                    return back()->with('error', 'Seuls les bons de transfert en brouillon de votre site peuvent être supprimés.');
                }

                // Delete lines first (foreign key constraint might require this)
                DB::table('detbontransferts')->where('bontransfertid', $id)->delete();

                // Delete the main bon
                DB::table('bontransferts')->where('bontransfertid', $id)->delete();

                return redirect()->route('transfert.envoye.index')->with('success', 'Le bon de transfert a été supprimé avec succès.');
            });
        } catch (\Exception $e) {
            return redirect()->route('transfert.envoye.index')->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }

    public function getDemandeLignes($id)
    {
        // Seules les demandes adressées à notre site (nous fournissons la marchandise) sont importables
        $demande = ctype_digit((string) $id)
            ? DB::table('demandetransferts')
                ->where('demandetransfertid', $id)
                ->where('siterecepteurid', auth()->user()->siteid)
                ->first()
            : null;
        if (!$demande) {
            return response()->json(['error' => 'Demande introuvable'], 404);
        }

        // Lignes validées (qteenvoi > 0) et pas encore expédiées dans un bon de transfert.
        // produitctviews : une ligne par produit2id, indépendante du stock d'un site (aucune ligne perdue).
        $lignes = DB::table('detdemandetransferts as det')
            ->leftJoin('produitctviews as p', 'p.produit2id', '=', 'det.produit2id')
            ->where('det.demandetransfertid', $id)
            ->where('det.qteenvoi', '>', 0)
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('detbontransferts as db')
                  ->whereColumn('db.detdemandetransfertid', 'det.detdemandetransfertid');
            })
            ->select(
                'det.detdemandetransfertid',
                'det.produitid',
                'det.produit2id',
                'det.qte as qte_demandee',
                'det.qteenvoi as qte_validee',
                'p.produitcode',
                'p.produitlibelle as designation',
                'p.taillelibelle as taille',
                'p.couleurlibelle as couleur',
                'p.ttc_vente as prix'
            )
            ->orderBy('det.ordre')
            ->get();

        return response()->json($lignes);
    }

    /**
     * Demandes validées adressées à notre site et dont aucune ligne n'a encore été expédiée.
     */
    private function demandesAImporter($siteid)
    {
        return DB::table('demandetransferts as d')
            ->where('d.siterecepteurid', $siteid)
            ->where('d.etatdemandetransfertid', 3)
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('detbontransferts as db')
                  ->join('detdemandetransferts as det', 'det.detdemandetransfertid', '=', 'db.detdemandetransfertid')
                  ->whereColumn('det.demandetransfertid', 'd.demandetransfertid');
            })
            ->select('d.*')
            ->get();
    }

    private function reglesLignes(): array
    {
        return [
            'lignes' => 'nullable|array',
            'lignes.*.qte' => 'nullable|integer|min:0',
            'lignes.*.qteenvoi' => 'nullable|integer|min:1',
            'lignes.*.detdemandetransfertid' => 'nullable|integer',
        ];
    }

    private function messagesLignes(): array
    {
        return [
            'lignes.*.qte.integer' => 'La quantité doit être un nombre entier.',
            'lignes.*.qte.min' => 'La quantité ne peut pas être négative.',
            'lignes.*.qteenvoi.integer' => 'La quantité à envoyer doit être un nombre entier.',
            'lignes.*.qteenvoi.min' => 'La quantité à envoyer doit être au moins 1.',
        ];
    }

    private function normaliserLignes($lignes): array
    {
        $resultat = [];
        foreach ((array) $lignes as $ligne) {
            if (empty($ligne['produitid'])) continue;

            $qte = (int) ($ligne['qte'] ?? 0);
            $qteenvoi = (isset($ligne['qteenvoi']) && $ligne['qteenvoi'] !== '') ? (int) $ligne['qteenvoi'] : $qte;

            $resultat[] = [
                'produitid' => $ligne['produitid'],
                'produit2id' => !empty($ligne['produit2id']) ? $ligne['produit2id'] : $ligne['produitid'],
                'qte' => $qte,
                'qteenvoi' => $qteenvoi,
                'prix' => (float) ($ligne['prix'] ?? 0),
                'detdemandetransfertid' => !empty($ligne['detdemandetransfertid']) ? (int) $ligne['detdemandetransfertid'] : null,
            ];
        }
        return $resultat;
    }

    private function verifierQuantites(array $lignes): ?string
    {
        foreach ($lignes as $ligne) {
            if ($ligne['qteenvoi'] < 1) {
                return 'La quantité à envoyer doit être au moins 1.';
            }
        }
        return null;
    }

    /**
     * Vérifie que le site expéditeur dispose du stock pour chaque produit envoyé.
     */
    private function verifierStock($siteid, array $lignes): ?string
    {
        $parProduit = [];
        foreach ($lignes as $ligne) {
            $parProduit[$ligne['produit2id']] = ($parProduit[$ligne['produit2id']] ?? 0) + $ligne['qteenvoi'];
        }

        foreach ($parProduit as $produit2id => $qteenvoi) {
            $stock = DB::table('vproduit2stocks')
                ->where('produit2id', $produit2id)
                ->where('siteid', $siteid)
                ->value('qtestock');

            $stockAvailable = $stock ? (float) $stock : 0.0;

            if ($qteenvoi > $stockAvailable) {
                $reference = DB::table('produits as p')
                    ->join('produit2s as p2', 'p2.produitid', '=', 'p.produitid')
                    ->where('p2.produit2id', $produit2id)
                    ->value('p.reference') ?? 'Produit';

                return "Stock insuffisant pour le produit {$reference} sur le site expéditeur. À envoyer: {$qteenvoi}, Disponible: {$stockAvailable}";
            }
        }
        return null;
    }

    /**
     * Ne conserve que les liens vers des lignes de demande émises par le site récepteur vers notre site
     * et pas déjà expédiées dans un autre bon. Retourne [lignes, demandetransfertid de l'en-tête].
     */
    private function lierDemandes(array $lignes, $siteid, $siterecepteurid, $demandetransfertid = null, $bontransfertid = null): array
    {
        $detIds = array_values(array_filter(array_column($lignes, 'detdemandetransfertid')));
        $valides = [];

        if (count($detIds) > 0) {
            $valides = DB::table('detdemandetransferts as det')
                ->join('demandetransferts as d', 'd.demandetransfertid', '=', 'det.demandetransfertid')
                ->whereIn('det.detdemandetransfertid', $detIds)
                ->where('d.siteid', $siterecepteurid)       // Le demandeur reçoit le bon
                ->where('d.siterecepteurid', $siteid)       // Nous fournissons la marchandise
                ->whereNotExists(function ($q) use ($bontransfertid) {
                    $q->select(DB::raw(1))
                      ->from('detbontransferts as db')
                      ->whereColumn('db.detdemandetransfertid', 'det.detdemandetransfertid');
                    if ($bontransfertid) {
                        $q->where('db.bontransfertid', '!=', $bontransfertid);
                    }
                })
                ->pluck('det.demandetransfertid', 'det.detdemandetransfertid')
                ->all();
        }

        $dejaLiees = [];
        foreach ($lignes as &$ligne) {
            $detId = $ligne['detdemandetransfertid'];
            if ($detId && (!isset($valides[$detId]) || isset($dejaLiees[$detId]))) {
                $ligne['detdemandetransfertid'] = null;
            }
            if ($ligne['detdemandetransfertid']) {
                $dejaLiees[$detId] = true;
            }
        }
        unset($ligne);

        if ($demandetransfertid) {
            $demandeValide = DB::table('demandetransferts')
                ->where('demandetransfertid', $demandetransfertid)
                ->where('siteid', $siterecepteurid)
                ->where('siterecepteurid', $siteid)
                ->exists();
            if (!$demandeValide) {
                $demandetransfertid = null;
            }
        }
        if (!$demandetransfertid && count($dejaLiees) > 0) {
            $demandetransfertid = $valides[array_key_first($dejaLiees)];
        }

        return [$lignes, $demandetransfertid ? (int) $demandetransfertid : null];
    }

    /**
     * Insère les lignes du bon. Le stock est mis à jour uniquement par le trigger calcul_stock (cbl_stock)
     * du logiciel de bureau, tel qu'il existe en base :
     *  - l'expéditeur est débité de detbontransferts.qte dès que la ligne est à l'état > 1 (envoyé / confirmé) ;
     *  - le récepteur est crédité de detbontransferts.qte quand la ligne passe à l'état 3 (confirmé).
     * Le trigger ne lit ni qteenvoi ni qterecu : la colonne qte doit donc contenir la quantité réellement
     * expédiée (convention du bureau : qte = qteenvoi = qterecu). La quantité demandée reste sur la demande.
     */
    private function insererLignes($bontransfertid, $siteid, $siterecepteurid, $etat, array $lignes): array
    {
        $totalqte = 0;
        $totalht = 0;
        $totalttc = 0;
        $ordre = 1;

        foreach ($lignes as $ligne) {
            $qteenvoi = $ligne['qteenvoi'];
            $qte = $qteenvoi; // quantité lue par le trigger de stock = quantité expédiée
            $prix = $ligne['prix'];
            $taxe = \App\Support\TaxeProduit::pour($ligne['produitid']); // TVA réelle du produit
            $ht = \App\Support\TaxeProduit::ht($prix, $taxe['tva']);

            $totalqte += $qteenvoi; // on compte ce qu'on envoie
            $totalttc += ($prix * $qteenvoi);
            $totalht += ($ht * $qteenvoi);

            // Need detbontransfertid sequence
            $detid = DB::selectOne("SELECT nextval('detbontransferts_detbontransfertid_seq') as id")->id;

            DB::table('detbontransferts')->insert([
                'detbontransfertid' => $detid,
                'bontransfertid' => $bontransfertid,
                'siteid' => $siteid,
                'siterecepteurid' => $siterecepteurid,
                'produitid' => $ligne['produitid'],
                'produit2id' => $ligne['produit2id'],
                'detdemandetransfertid' => $ligne['detdemandetransfertid'],
                'taxefamilleid' => $taxe['taxefamilleid'],
                'ht' => $ht,
                'ttc' => $prix,
                'qte' => $qte,
                'qteenvoi' => $qteenvoi,
                'qterecu' => 0,
                'qteecart' => 0,
                'etatbontransfertid' => $etat,
                'totalht' => $ht * $qteenvoi,
                'remise' => 0,
                'remise2' => 0,
                'totalhtnet' => $ht * $qteenvoi,
                'taxe1' => 0, 'vtaxe1' => 0,
                'taxe2' => 0, 'vtaxe2' => 0,
                'taxe3' => 0, 'vtaxe3' => 0,
                'taxe4' => 0, 'vtaxe4' => 0,
                'tva' => $taxe['tva'],
                'vtva' => round(($prix - $ht) * $qteenvoi, 3),
                'totalttc' => $prix * $qteenvoi,
                'totalttcnet' => $prix * $qteenvoi,
                'date' => Carbon::now(),
                'largeur' => 0, 'longueur' => 0, 'surface' => 0,
                'pointer' => false,
                'ordre' => $ordre++,
                // prodid est un integer alors que produitid est un bigint (ex. 1000000000001) : 0 comme la demande de transfert
                'prodid' => ((int) $ligne['produitid'] <= 2147483647) ? (int) $ligne['produitid'] : 0,
                'modestock' => 1,
                'grammagegr' => 0,
                'largeurmm' => 0,
                'longueurm' => 0,
                'modereceptionid' => 1,
                'poids' => 0,
                'stockorigineid' => 1
            ]);
        }

        return [
            'totalqte' => $totalqte,
            'totalbrutht' => $totalht,
            'totalnetht' => $totalht,
            'totaltva' => round($totalttc - $totalht, 3),
            'totalttc' => $totalttc,
            'netapayer' => $totalttc
        ];
    }
}
