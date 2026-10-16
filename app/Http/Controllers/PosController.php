<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\FideliteService;
use App\Services\SmsService;

class PosController extends Controller
{
    public function __construct(
        private readonly FideliteService $fidelite,
        private readonly SmsService $sms,
    ) {
    }

    /**
     * Remise fidélité (%) applicable à la prochaine vente du client. Lecture seule : l'état de la
     * carte (ventes, expiration) est calculé depuis les tickets par FideliteService.
     */
    public function getLoyaltyTier($client)
    {
        return $this->fidelite->palier($client);
    }

    /**
     * Complète une fiche client (stdClass) avec les informations fidélité renvoyées à la caisse.
     */
    private function enrichirClient(object $client): object
    {
        $etat = $this->fidelite->etat($client);
        $client->loyalty_tier = $etat['palier'];
        $client->fidelite_ventes = $etat['ventes'];
        $client->fidelite_expiree = $etat['expiree'];
        $client->num_fidelite = $client->barcode ?? null; // n° de carte = colonne barcode (partagée avec le bureau)
        $client->soldefidelite = $etat['bons_disponibles'];
        $client->bons_disponibles = $etat['eligible'] ? $this->fidelite->bonsDisponibles((int) $client->clientid)->values() : [];

        return $client;
    }

    public function index()
    {
        $user = auth()->user();

        // Journée de caisse ouverte (même règle que la validation des tickets)
        $journalCaisse = app(\App\Services\JourneeCaisseService::class)->journeeOuverte($user);

        // Si aucune session n'est ouverte, rediriger vers la page d'ouverture
        if (!$journalCaisse) {
            return redirect()->route('vente.journee.ouverture')
                ->with('warning', 'Veuillez ouvrir une journée de caisse avant de commencer les ventes.');
        }

        // Tout ce qu'affiche l'écran de caisse en UNE requête (base distante : un seul aller-retour) :
        // client passager, vendeur du compte, prochain numéro, listes de la fenêtre de choix d'article
        // (familles, sous-familles, saisons, catégories, marques), types de chèques cadeaux et
        // compteurs de transferts du site.
        $siteId = \App\Support\SiteCourant::id();
        $d = DB::selectOne(<<<'SQL'
            SELECT
              (SELECT row_to_json(c)::text FROM (
                  SELECT clientid, clientcode, nom, solde, soldefidelite, pointfidelite
                    FROM clients WHERE nom = 'PASSAGER' ORDER BY clientid LIMIT 1) c) AS client,
              (SELECT row_to_json(e)::text FROM (
                  SELECT employeeid, nom, prenom FROM employees WHERE employeeid = ?::int) e) AS vendeur,
              (SELECT max(cticketnumero) FROM ctickets) AS max_numero,
              (SELECT COALESCE(json_agg(c ORDER BY c.categorylibelle), '[]')::text FROM (
                  SELECT categoryid, categorylibelle FROM categories) c) AS categories,
              (SELECT COALESCE(json_agg(f ORDER BY f.famillelibelle), '[]')::text FROM (
                  SELECT familleid, famillelibelle FROM familles) f) AS familles,
              (SELECT COALESCE(json_agg(f ORDER BY f.sousfamillelibelle), '[]')::text FROM (
                  SELECT sousfamilleid, sousfamillelibelle, familleid FROM sousfamilles) f) AS sous_familles,
              (SELECT COALESCE(json_agg(c ORDER BY c.category4libelle), '[]')::text FROM (
                  SELECT category4id, category4libelle FROM categories4) c) AS saisons,
              (SELECT COALESCE(json_agg(c ORDER BY c.category2libelle), '[]')::text FROM (
                  SELECT category2id, category2libelle FROM categories2) c) AS marques,
              (SELECT COALESCE(json_agg(t ORDER BY t.priorite), '[]')::text FROM (
                  SELECT typechequecadeauid, libelle, priorite FROM typechequecadeaus) t) AS types_cheques,
              (SELECT count(*) FROM demandetransferts
                WHERE (siterecepteurid = ?::int OR siteid = ?::int) AND etatdemandetransfertid IN (1, 2, 5)) AS demandes,
              (SELECT count(*) FROM bontransferts
                WHERE (siterecepteurid = ?::int OR siteid = ?::int) AND confirmer = false) AS bons
            SQL, [(int) ($user->employeeid ?? 0), $siteId, $siteId, $siteId, $siteId]);

        $client = json_decode($d->client ?? 'null');
        // Vendeur par défaut = employé lié au compte connecté
        $defaultVendeur = $user->employeeid ? json_decode($d->vendeur ?? 'null') : null;
        $categories = collect(json_decode($d->categories, false) ?: []);
        $familles = collect(json_decode($d->familles, false) ?: []);
        $sousFamilles = collect(json_decode($d->sous_familles, false) ?: []);
        $saisons = collect(json_decode($d->saisons, false) ?: []);
        $marques = collect(json_decode($d->marques, false) ?: []);
        $typeChequeCadeaus = collect(json_decode($d->types_cheques, false) ?: []);
        $demandeTransfCount = (int) $d->demandes; // demandes en cours, envoyées ou en attente
        $bonTransfCount = (int) $d->bons;         // bons non confirmés

        // Numéro de ticket provisoire affiché (le vrai numéro est attribué à la validation)
        $draftId = $d->max_numero ? ((int) $d->max_numero + 1) : date('y') . '000001';

        // Nom du site : déjà chargé avec l'utilisateur
        $siteName = optional($user->siteRecord())->libelle ?? 'VELARO';

        // Mode d'impression de la caisse de la journée (affiché dans la caisse ; l'impression elle-même est faite par le serveur)
        $configImpression = app(\App\Services\ImpressionTicketService::class)->configuration((int) $journalCaisse->caisseid);
        $impression = ['mode' => $configImpression['mode']];

        return view('caisse.pos', compact(
            'client', 'familles', 'sousFamilles', 'saisons', 'categories', 'marques',
            'defaultVendeur', 'draftId', 'typeChequeCadeaus', 'siteName',
            'demandeTransfCount', 'bonTransfCount', 'impression'
        ));
    }

    public function searchProducts(Request $request)
    {
        $siteid = \App\Support\SiteCourant::id();

        $query = DB::table('produits')
            ->leftJoin('familles', 'produits.familleid', '=', 'familles.familleid')
            ->leftJoin('sousfamilles', 'produits.sousfamilleid', '=', 'sousfamilles.sousfamilleid')
            ->leftJoin('fournisseurs', 'produits.fournisseurid', '=', 'fournisseurs.fournisseurid')
            ->leftJoin('produit2s', 'produit2s.produitid', '=', 'produits.produitid')
            ->leftJoin('vproduit2stocks', function($join) use ($siteid) {
                $join->on('vproduit2stocks.produit2id', '=', 'produit2s.produit2id')
                     ->where('vproduit2stocks.siteid', '=', $siteid);
            })
            ->select(
                'produits.produitid',
                'produits.produitcode',
                'produits.reference',
                DB::raw("MAX(COALESCE(produits.barcode2, vproduit2stocks.barcode2, produit2s.barcode2, produit2s.produit2code)) as barcode2"),
                'produits.produitlibelle',
                'familles.famillelibelle',
                'sousfamilles.sousfamillelibelle',
                'produits.ttc_vente',
                'fournisseurs.nom as fournisseur',
                'produits.qtestock as total_stock',
                'sousfamilles.is_loyalty_enabled'
            )
            ->groupBy(
                'produits.produitid',
                'produits.produitcode',
                'produits.reference',
                'produits.produitlibelle',
                'familles.famillelibelle',
                'sousfamilles.sousfamillelibelle',
                'produits.ttc_vente',
                'fournisseurs.nom',
                'produits.qtestock',
                'sousfamilles.is_loyalty_enabled'
            );

        if ($request->filled('sousfamilleid')) {
            $query->where('produits.sousfamilleid', $request->sousfamilleid);
        }
        if ($request->filled('familleid')) {
            $query->where('produits.familleid', $request->familleid);
        }
        if ($request->filled('saisonid')) {
            $query->where('produits.category4id', $request->saisonid);
        }
        if ($request->filled('categoryid')) {
            $query->where('produits.categoryid', $request->categoryid);
        }
        if ($request->filled('marqueid')) {
            $query->where('produits.category2id', $request->marqueid);
        }
        if ($request->filled('search')) {
            $exactSearch = strtolower($request->search);
            $searchTerm = '%' . $exactSearch . '%';
            $query->where(function($q) use ($searchTerm, $exactSearch) {
                $q->whereRaw('LOWER(produits.produitcode) LIKE ?', [$searchTerm])
                  ->orWhereRaw('LOWER(produits.reference) LIKE ?', [$searchTerm])
                  ->orWhereRaw('LOWER(produits.barcode2) LIKE ?', [$searchTerm])
                  ->orWhereRaw('LOWER(produit2s.barcode2) LIKE ?', [$searchTerm])
                  ->orWhereRaw('LOWER(vproduit2stocks.barcode2) LIKE ?', [$searchTerm])
                  ->orWhereRaw('LOWER(produit2s.produit2code) = ?', [$exactSearch])
                  ->orWhereRaw('LOWER(produits.produitlibelle) LIKE ?', [$searchTerm])
                  ->orWhereRaw('LOWER(familles.famillelibelle) LIKE ?', [$searchTerm])
                  ->orWhereRaw('LOWER(sousfamilles.sousfamillelibelle) LIKE ?', [$searchTerm])
                  ->orWhereRaw('LOWER(fournisseurs.nom) LIKE ?', [$searchTerm])
                  ->orWhereRaw('CAST(produits.ttc_vente AS TEXT) LIKE ?', [$searchTerm]);
            });
        }

        $products = $query->paginerVite(20);

        return response()->json($products);
    }

    /**
     * Recherche rapide par scan (code-barres, référence, ou code produit)
     */
    public function scanProduct(Request $request)
    {
        $siteid = \App\Support\SiteCourant::id();
        $code = $request->input('code', '');

        if (empty($code)) {
            return response()->json(null);
        }

        // Chercher par barcode2, produitcode, ou reference (correspondance exacte)
        $product = DB::table('produit2s')
            ->join('produits', 'produits.produitid', '=', 'produit2s.produitid')
            ->leftJoin('couleurs', 'produit2s.couleurid', '=', 'couleurs.couleurid')
            ->leftJoin('tailles', 'produit2s.tailleid', '=', 'tailles.tailleid')
            ->leftJoin('sousfamilles', 'produits.sousfamilleid', '=', 'sousfamilles.sousfamilleid')
            ->leftJoin('vproduit2stocks', function($join) use ($siteid) {
                $join->on('vproduit2stocks.produit2id', '=', 'produit2s.produit2id')
                     ->where('vproduit2stocks.siteid', '=', $siteid);
            })
            ->where(function($q) use ($code) {
                $q->where('produits.barcode2', $code)
                  ->orWhere('produits.produitcode', $code)
                  ->orWhere('produits.reference', $code)
                  ->orWhere('produit2s.barcode2', $code)
                  ->orWhere('produit2s.produit2code', $code);
            })
            ->select(
                'produit2s.produit2id',
                'produits.produitid',
                'produits.reference',
                'produits.produitcode',
                'produits.produitlibelle',
                'produits.familleid',
                'couleurs.couleurlibelle as couleur',
                'tailles.taillelibelle as taille',
                'couleurs.couleurlibelle',
                'tailles.taillelibelle',
                'produits.ttc_vente',
                DB::raw('COALESCE(vproduit2stocks.qtestock, 0) as total_stock'),
                'sousfamilles.is_loyalty_enabled'
            )
            // Variante exacte et nombre de variantes lus dans la même requête (un seul aller-retour par scan)
            ->selectRaw('COALESCE(produit2s.barcode2 = ? OR produit2s.produit2code = ?, false) AS gp_variante_exacte', [$code, $code])
            ->selectRaw('(SELECT count(*) FROM produit2s v WHERE v.produitid = produits.produitid) AS gp_nb_variantes')
            ->orderByRaw('CASE WHEN produit2s.barcode2 = ? OR produit2s.produit2code = ? THEN 0 ELSE 1 END', [$code, $code])
            ->first();

        // Code commun à toutes les variantes (référence / code article) : laisser le caissier choisir
        if ($product) {
            if ($product->produit2id && !$product->gp_variante_exacte && (int) $product->gp_nb_variantes > 1) {
                $product->choisir_variante = true;
            }
            unset($product->gp_variante_exacte, $product->gp_nb_variantes);
        }

        return response()->json($product);
    }
    public function getProductVariants(Request $request)
    {
        $siteid = \App\Support\SiteCourant::id();
        $produitid = $request->produitid;

        if (!$produitid) {
            return response()->json([]);
        }

        $variants = DB::table('produit2s')
            ->join('produits', 'produits.produitid', '=', 'produit2s.produitid')
            ->leftJoin('couleurs', 'produit2s.couleurid', '=', 'couleurs.couleurid')
            ->leftJoin('tailles', 'produit2s.tailleid', '=', 'tailles.tailleid')
            ->leftJoin('sousfamilles', 'produits.sousfamilleid', '=', 'sousfamilles.sousfamilleid')
            ->leftJoin('vproduit2stocks', function($join) use ($siteid) {
                $join->on('vproduit2stocks.produit2id', '=', 'produit2s.produit2id')
                     ->where('vproduit2stocks.siteid', '=', $siteid);
            })
            ->where('produit2s.produitid', $produitid)
            ->select(
                'produits.produitid',
                'produit2s.produit2id',
                'produits.produitcode',
                'produits.reference',
                DB::raw("COALESCE(produit2s.barcode2, produits.barcode2) as barcode2"),
                'produits.produitlibelle',
                'tailles.taillelibelle',
                'couleurs.couleurlibelle',
                'produits.ttc_vente',
                DB::raw('COALESCE(vproduit2stocks.qtestock, 0) as total_stock'),
                'sousfamilles.is_loyalty_enabled'
            )
            ->orderBy('tailles.taillelibelle')
            ->orderBy('couleurs.couleurlibelle')
            ->get();

        return response()->json($variants);
    }

    /**
     * Store a new client via AJAX
     */
    public function storeClient(Request $request)
    {
        $request->validate([
            'raison' => 'required|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'telephone' => 'required|string|max:255',
            'num_fidelite' => 'nullable|string|max:50',
            'date_naissance' => 'nullable|date',
        ]);

        try {
            $numFidelite = trim((string) $request->input('num_fidelite', ''));
            if ($numFidelite !== '' && DB::table('clients')->where('barcode', $numFidelite)->exists()) {
                return response()->json(['success' => false, 'message' => "Le n° de carte fidélité {$numFidelite} est déjà attribué à un autre client."], 422);
            }

            $nextId = DB::table('clients')->max('clientid') + 1;
            $clientCode = '411' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            $id = DB::table('clients')->insertGetId([
                'nom' => $request->raison,
                'prenom' => $request->prenom,
                'clientcode' => $clientCode,
                'code' => $clientCode,
                'bloquercredit' => $request->has('bloque_credit') ? 1 : 0,
                'bloque' => 0,
                'credit' => 1,
                'mf' => $request->matricule_fiscal,
                'fidelite' => $request->has('g_fidelite') ? 1 : 0,
                'barcode' => $numFidelite !== '' ? $numFidelite : null, // n° carte fidélité (colonne du bureau)
                'tel' => $request->telephone ?? '',
                'email' => $request->email ?? '',
                'datenaissance' => $request->filled('date_naissance') ? $request->date_naissance : '1900-01-01',
                'ville' => $request->ville ?? '',
                'adressefacturation' => $request->adresse,
                'adresselivraison' => $request->adresse,
                'clientfamilleid' => \DB::table('clientfamilles')->value('clientfamilleid') ?? \DB::table('clientfamilles')->insertGetId(['libelle' => 'Général', 'bloque' => 0], 'clientfamilleid'),
                'clientcategorieid' => \DB::table('clientcategories')->value('clientcategorieid') ?? \DB::table('clientcategories')->insertGetId(['libelle' => 'Standard', 'bloque' => 0], 'clientcategorieid'),
                'echeance' => 0,
                'impaye' => 0,
                'plafonecheance' => 0,
                'plafonsolde' => 0,
                'plafonremise' => 0,
                'autofacturation' => 0,
                'isventecomptoir' => 1,
                'isconvention' => 0,
                'solde' => 0,
                'soldeinitial' => 0,
                'remise' => 0,
                'soldefidelite' => 0,
                'cumulfidelite' => 0,
                'pointfidelite' => 0,
            ], 'clientid');

            $client = $this->enrichirClient(DB::table('clients')->where('clientid', $id)->first());

            return response()->json([
                'success' => true,
                'client' => $client
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single client
     */
    public function getClient($id)
    {
        $client = DB::table('clients')->where('clientid', $id)->first();
        if (!$client) {
            return response()->json(['success' => false, 'message' => 'Client introuvable'], 404);
        }
        return response()->json(['success' => true, 'client' => $this->enrichirClient($client)]);
    }

    /**
     * Update existing client
     */
    public function updateClient(Request $request, $id)
    {
        $request->validate([
            'raison' => 'required|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'telephone' => 'required|string|max:255',
            'num_fidelite' => 'nullable|string|max:50',
            'date_naissance' => 'nullable|date',
        ]);

        try {
            $numFidelite = trim((string) $request->input('num_fidelite', ''));
            if ($numFidelite !== '' && DB::table('clients')->where('barcode', $numFidelite)->where('clientid', '!=', $id)->exists()) {
                return response()->json(['success' => false, 'message' => "Le n° de carte fidélité {$numFidelite} est déjà attribué à un autre client."], 422);
            }

            $data = [
                'nom' => $request->raison,
                'prenom' => $request->prenom,
                'bloquercredit' => $request->has('bloque_credit') ? 1 : 0,
                'mf' => $request->matricule_fiscal,
                'fidelite' => $request->has('g_fidelite') ? 1 : 0,
                'barcode' => $numFidelite !== '' ? $numFidelite : null,
                'tel' => $request->telephone,
                'email' => $request->email,
                'ville' => $request->ville,
                'adressefacturation' => $request->adresse,
                'adresselivraison' => $request->adresse,
            ];
            // datenaissance est NOT NULL en base : on ne l'écrase que si une date est fournie
            if ($request->filled('date_naissance')) {
                $data['datenaissance'] = $request->date_naissance;
            }
            DB::table('clients')->where('clientid', $id)->update($data);

            $client = $this->enrichirClient(DB::table('clients')->where('clientid', $id)->first());

            return response()->json([
                'success' => true,
                'client' => $client
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get client purchase history
     */
    public function clientHistory($id)
    {
        $tickets = DB::table('ctickets')
            ->where('clientid', $id)
            ->where('enattente', false)
            ->select('cticketid', 'cticketnumero', 'cticketdate', 'totalqte', 'totalttc')
            ->orderBy('cticketdate', 'desc')
            ->take(50)
            ->get();

        return response()->json([
            'success' => true,
            'history' => $tickets
        ]);
    }

    /**
     * Recherche avancée de l'historique des articles (filtre par client, tel, carte)
     */
    public function searchArticleHistory(Request $request)
    {
        $query = DB::table('detctickets')
            ->join('ctickets', 'detctickets.cticketid', '=', 'ctickets.cticketid')
            ->join('clients', 'ctickets.clientid', '=', 'clients.clientid')
            ->join('produits', 'detctickets.produitid', '=', 'produits.produitid')
            ->join('produit2s', 'detctickets.produit2id', '=', 'produit2s.produit2id')
            ->leftJoin('couleurs', 'produit2s.couleurid', '=', 'couleurs.couleurid')
            ->leftJoin('tailles', 'produit2s.tailleid', '=', 'tailles.tailleid')
            ->select(
                'ctickets.cticketdate as date',
                'ctickets.cticketnumero as ticket',
                DB::raw("TRIM(clients.nom || ' ' || COALESCE(clients.prenom, '')) as client"),
                'clients.tel as telephone',
                'clients.barcode as carte',
                'produits.reference',
                'produits.produitlibelle as designation',
                'couleurs.couleurlibelle as couleur',
                'tailles.taillelibelle as taille',
                'detctickets.qte',
                'detctickets.ttc',
                'detctickets.totalttcnet as totalttc'
            )
            ->where('ctickets.enattente', false)
            ->where('ctickets.clientid', '!=', (int) config('pos.client_passager_id'));

        // Filtre client : nom, prénom, téléphone ou n° de carte fidélité (carte / téléphone : correspondance exacte prioritaire)
        if ($request->filled('q')) {
            $brut = trim((string) $request->q);
            $search = '%' . strtolower($brut) . '%';
            $chiffres = preg_replace('/\D+/', '', $brut) ?? '';
            $query->where(function($q) use ($search, $chiffres) {
                $q->whereRaw('LOWER(clients.nom) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(COALESCE(clients.prenom, \'\')) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(COALESCE(clients.barcode, \'\')) LIKE ?', [$search]);
                if ($chiffres !== '') {
                    $q->orWhereRaw("regexp_replace(COALESCE(clients.tel, ''), '\\D', '', 'g') LIKE ?", ['%' . $chiffres . '%']);
                }
            });
        }
        if ($request->filled('du')) {
            $query->whereDate('ctickets.cticketdate', '>=', $request->du);
        }
        if ($request->filled('au')) {
            $query->whereDate('ctickets.cticketdate', '<=', $request->au);
        }

        $history = $query->orderBy('ctickets.cticketdate', 'desc')->limit(200)->get();

        return response()->json([
            'success' => true,
            'history' => $history
        ]);
    }

    /**
     * Send SMS via Bip SMS API
     */
    public function sendSms(Request $request)
    {
        $request->validate([
            'telephone' => ['required', 'string', 'regex:/^\+?[0-9 .\-]{8,20}$/'],
            'message' => 'required|string|max:480',
            'clientid' => 'nullable|integer',
            'cticketid' => 'nullable|integer',
        ]);

        $resultat = $this->sms->envoyer($request->telephone, $request->message, [
            'clientid' => $request->input('clientid'),
            'cticketid' => $request->input('cticketid'),
        ]);

        return response()->json($resultat);
    }

    /**
     * Vérifie un bon d'achat saisi ou scanné dans la caisse (mode de règlement « Bon d'achat »).
     */
    public function checkBonAchat(Request $request)
    {
        $code = trim((string) $request->input('code', ''));
        if ($code === '') {
            return response()->json(['success' => false, 'message' => 'Veuillez saisir ou scanner le numéro du bon.']);
        }
        $clientid = $request->filled('clientid') ? (int) $request->input('clientid') : null;
        if ($clientid === (int) config('pos.client_passager_id')) {
            $clientid = null;
        }

        $verif = $this->fidelite->verifierBon($code, $clientid);
        if ($verif['erreur']) {
            return response()->json(['success' => false, 'message' => $verif['erreur']]);
        }
        $bon = $verif['bon'];
        $client = DB::table('clients')->where('clientid', $bon->clientid)->first();

        return response()->json([
            'success' => true,
            'bon' => [
                'id' => $bon->id,
                'numero' => $bon->numero,
                'montant' => (float) $bon->montant,
                'date_expiration' => $bon->date_expiration,
                'clientid' => $bon->clientid,
                'client_nom' => trim(($client->nom ?? '') . ' ' . ($client->prenom ?? '')),
                'client_tel' => $client->tel ?? '',
            ],
        ]);
    }

    /**
     * Bons d'achat utilisables d'un client (liste proposée dans la caisse).
     */
    public function bonsClient($id)
    {
        $client = DB::table('clients')->where('clientid', $id)->first();
        if (!$client) {
            return response()->json(['success' => false, 'message' => 'Client introuvable'], 404);
        }
        $etat = $this->fidelite->etat($client);

        return response()->json([
            'success' => true,
            'eligible' => $etat['eligible'],
            'palier' => $etat['palier'],
            'ventes' => $etat['ventes'],
            'solde' => $etat['bons_disponibles'],
            'bons' => $etat['eligible'] ? $this->fidelite->bonsDisponibles((int) $client->clientid)->values() : [],
        ]);
    }

    /**
     * Check stock for a specific variant across all stores
     */
    public function checkStock($produit2id)
    {
        $stock = DB::table('vproduit2stocks')
            ->join('sites', 'sites.siteid', '=', 'vproduit2stocks.siteid')
            ->where('vproduit2stocks.produit2id', $produit2id)
            ->where('sites.isstock', 1)
            ->select('sites.libelle as site_nom', 'vproduit2stocks.qtestock')
            ->orderBy('sites.siteid')
            ->get();

        return response()->json([
            'success' => true,
            'stock' => $stock
        ]);
    }

    /**
     * Advanced stock check for the Consultation Stock Modal
     */
    public function advancedCheckStock(Request $request)
    {
        $query = DB::table('stock2s')
            ->join('sites', 'stock2s.siteid', '=', 'sites.siteid')
            ->join('produits', 'stock2s.produitid', '=', 'produits.produitid')
            ->join('produit2s', 'stock2s.produit2id', '=', 'produit2s.produit2id')
            ->leftJoin('categories', 'produits.categoryid', '=', 'categories.categoryid') // Rayon
            ->leftJoin('couleurs', 'produit2s.couleurid', '=', 'couleurs.couleurid')
            ->leftJoin('tailles', 'produit2s.tailleid', '=', 'tailles.tailleid')
            ->select(
                'produits.reference',
                'produits.produitlibelle as designation',
                'categories.categorylibelle as rayon',
                'couleurs.couleurlibelle as couleur',
                'tailles.taillelibelle as taille',
                'sites.libelle as site_nom',
                'stock2s.qtestock as reel',
                'stock2s.stockvirtuel as virtuel',
                'stock2s.stockreserve as reserve'
            )
            ->where('sites.isstock', 1);

        if ($request->filled('reference')) {
            $query->where('produits.reference', 'like', '%' . $request->reference . '%');
        }
        if ($request->filled('rayonid')) {
            $query->where('produits.categoryid', $request->rayonid);
        }
        if ($request->filled('couleur')) {
            $query->where('couleurs.couleurlibelle', 'like', '%' . $request->couleur . '%');
        }
        if ($request->filled('taille')) {
            $query->where('tailles.taillelibelle', 'like', '%' . $request->taille . '%');
        }

        $stock = $query->orderBy('produits.reference')
            ->orderBy('sites.siteid')
            ->limit(100)
            ->get();

        return response()->json([
            'success' => true,
            'stock' => $stock
        ]);
    }

    /**
     * Check avoir details by barcode/number
     */
    public function checkAvoir(Request $request)
    {
        $code = $request->input('code');
        if (empty($code)) {
            return response()->json(['success' => false, 'message' => 'Veuillez saisir un code à barre.']);
        }

        // Search in cavoirs table
        $avoir = DB::table('cavoirs')
            ->where(function ($q) use ($code) {
                $q->where('numerointerne', $code);
                if (ctype_digit((string) $code)) {
                    $q->orWhere('cavoirnumero', $code);
                }
            })
            ->first();

        if (!$avoir) {
            return response()->json(['success' => false, 'message' => 'Avoir introuvable.']);
        }

        if ($avoir->cloture) {
            return response()->json(['success' => false, 'message' => 'Cet avoir est déjà clôturé (consommé).']);
        }

        $montant = floatval($avoir->netapayer ?? $avoir->totalttc ?? 0);
        if ($montant <= 0) {
            return response()->json(['success' => false, 'message' => 'Cet avoir n\'a pas de solde restant à consommer.']);
        }

        // Get client name
        $clientName = 'Inconnu';
        if ($avoir->clientid) {
            $client = DB::table('clients')->where('clientid', $avoir->clientid)->first();
            if ($client) {
                $clientName = $client->nom . ' ' . ($client->prenom ?? '');
            }
        }

        return response()->json([
            'success' => true,
            'avoir' => [
                'cavoirid' => $avoir->cavoirid,
                'cavoirnumero' => $avoir->cavoirnumero,
                'numerointerne' => $avoir->numerointerne,
                'montant' => $montant,
                'clientid' => $avoir->clientid,
                'client_name' => trim($clientName)
            ]
        ]);
    }


    /**
     * Recherche de clients pour le POS
     */
    public function searchClients(Request $request)
    {
        $query = DB::table('clients');

        // Global search
        if ($request->filled('q')) {
            $search = '%' . strtolower($request->q) . '%';
            $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(nom) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(COALESCE(prenom, \'\')) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(clientcode) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(tel) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(COALESCE(barcode, \'\')) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(CAST(clientid AS TEXT)) LIKE ?', [$search]);
            });
        }

        // Column filters
        if ($request->filled('f_code')) $query->whereRaw('LOWER(clientcode) LIKE ?', ['%' . strtolower($request->f_code) . '%']);
        if ($request->filled('f_nom')) $query->whereRaw('LOWER(nom) LIKE ?', ['%' . strtolower($request->f_nom) . '%']);
        if ($request->filled('f_tel')) $query->whereRaw('LOWER(tel) LIKE ?', ['%' . strtolower($request->f_tel) . '%']);
        if ($request->filled('f_adresse')) $query->whereRaw('LOWER(adressefacturation) LIKE ?', ['%' . strtolower($request->f_adresse) . '%']);
        if ($request->filled('f_mf')) $query->whereRaw('LOWER(mf) LIKE ?', ['%' . strtolower($request->f_mf) . '%']);
        if ($request->filled('f_remise')) $query->where('remise', $request->f_remise);
        if ($request->filled('f_plafonremise')) $query->where('plafonremise', $request->f_plafonremise);
        if ($request->filled('f_solde')) $query->where('solde', $request->f_solde);

        $clients = $query->select('clientid', 'clientcode', 'nom', 'prenom', 'tel', 'adressefacturation', 'mf', 'remise', 'plafonremise', 'solde', 'ville', 'soldefidelite', 'pointfidelite', 'fidelite', 'barcode', 'fidelite_debut')
            ->orderBy('nom')
            ->paginerVite(15);

        // Palier de remise et bons d'achat disponibles : calcul groupé pour toute la page (2 requêtes)
        $etats = $this->fidelite->etatsEnLot($clients->getCollection());
        $clients->getCollection()->transform(function ($client) use ($etats) {
            $etat = $etats[(int) $client->clientid];
            $client->loyalty_tier = $etat['palier'];
            $client->fidelite_ventes = $etat['ventes'];
            $client->fidelite_expiree = $etat['expiree'];
            $client->num_fidelite = $client->barcode ?? null;
            $client->soldefidelite = $etat['bons_disponibles'];
            $client->bons_disponibles = $etat['eligible'] ? $etat['bons'] : [];

            return $client;
        });

        return response()->json($clients);
    }

    /**
     * Recherche de vendeurs pour le POS
     */
    public function searchVendeurs(Request $request)
    {
        $userEmployeeId = auth()->user()->employeeid ?? null;

        // Vendeurs actifs du site (gérés dans Paramètres > Gestion des Vendeurs).
        // Rôle avec le droit « choix.vendeur » bloqué : uniquement le vendeur lié au compte.
        $query = DB::table('employees')
            ->where('siteid', \App\Support\SiteCourant::id())
            ->where('isvendeur', true)
            ->where('bloque', false);
        if (\App\Support\Droits::bloque('choix.vendeur')) {
            $query->where('employeeid', $userEmployeeId ?: 0);
        }

        if ($request->filled('q')) {
            $search = '%' . strtolower($request->q) . '%';
            $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(nom) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(code) LIKE ?', [$search])
                  ->orWhereRaw('CAST(employeeid AS TEXT) LIKE ?', [$search]);
            });
        }

        $vendeurs = $query->select('employeeid', 'code', 'nom')
            ->orderBy('employeeid')
            ->limit(50)
            ->get()
            ->map(function ($v) {
                if (empty($v->code)) {
                    $v->code = str_pad($v->employeeid, 3, '0', STR_PAD_LEFT);
                }
                return $v;
            });

        return response()->json($vendeurs);
    }


}
