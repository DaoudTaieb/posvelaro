<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PosController extends Controller
{
    private function getLoyaltyTier($client)
    {
        if (!$client || empty($client->fidelite) || $client->clientid == 1 || strtoupper($client->nom) === 'PASSAGER') {
            return 0;
        }
        
        $ventes = (int) $client->total_ventes_fidelite;
        
        if ($ventes > 0 && $client->date_premiere_vente) {
            $firstSaleDate = \Carbon\Carbon::parse($client->date_premiere_vente);
            if ($firstSaleDate->diffInYears(now()) > 0) {
                $salesLastYear = DB::table('ctickets')
                    ->where('clientid', $client->clientid)
                    ->where('cticketdate', '>=', now()->subYear())
                    ->count();
                if ($salesLastYear < 2) {
                    $ventes = 0;
                    DB::table('clients')->where('clientid', $client->clientid)->update([
                        'total_ventes_fidelite' => 0,
                        'date_premiere_vente' => null
                    ]);
                }
            }
        }
        
        if ($ventes == 0) return 10;
        if ($ventes == 1) return 20;
        if ($ventes >= 2) return 30;
        return 0;
    }

    public function index()
    {
        $user = auth()->user();

        // Vérifier s'il y a une session de caisse ouverte pour cet utilisateur
        $journalCaisse = DB::table('journalcaisses')
            ->where(function ($q) {
                $q->where('isclosed', false)
                  ->orWhereNull('isclosed');
            })
            ->where('userid', $user->userid)
            ->orderBy('journalcaisseid', 'desc')
            ->first();

        // Sinon, vérifier par site
        if (!$journalCaisse) {
            $journalCaisse = DB::table('journalcaisses')
                ->where(function ($q) {
                    $q->where('isclosed', false)
                      ->orWhereNull('isclosed');
                })
                ->where('siteid', $user->siteid)
                ->orderBy('journalcaisseid', 'desc')
                ->first();
        }

        // Si aucune session n'est ouverte, rediriger vers la page d'ouverture
        if (!$journalCaisse) {
            return redirect()->route('vente.journee.ouverture')
                ->with('error', 'Veuillez ouvrir une journée de caisse avant de commencer les ventes.');
        }

        $client = DB::table('clients')->where('nom', 'PASSAGER')->first();

        // Données pour le modal de sélection produit
        $familles = DB::table('familles')->orderBy('famillelibelle')->get();
        $sousFamilles = DB::table('sousfamilles')->orderBy('sousfamillelibelle')->get();
        $saisons = DB::table('categories4')->orderBy('category4libelle')->get();
        $categories = DB::table('categories')->orderBy('categorylibelle')->get();
        $marques = DB::table('categories2')->orderBy('category2libelle')->get();

        // Vendeur par défaut = employé lié au compte connecté
        $defaultVendeur = null;
        $user = auth()->user();
        if ($user && $user->employeeid) {
            $defaultVendeur = DB::table('employees')->where('employeeid', $user->employeeid)->first();
        }

        // Récupérer le nom du site de l'utilisateur connecté
        $site = null;
        if ($user && $user->siteid) {
            $site = DB::table('sites')->where('siteid', $user->siteid)->first();
        }
        $siteName = $site ? $site->libelle : 'VELARO';

        // Générer un numéro de ticket provisoire (ou afficher le prochain numéro)
        $lastTicket = DB::table('ctickets')->orderBy('cticketid', 'desc')->first();
        $draftId = $lastTicket && $lastTicket->cticketnumero ? (intval($lastTicket->cticketnumero) + 1) : date('y') . '000001';

        $typeChequeCadeaus = DB::table('typechequecadeaus')->orderBy('priorite')->get();

        return view('caisse.pos', compact('client', 'familles', 'sousFamilles', 'saisons', 'categories', 'marques', 'defaultVendeur', 'draftId', 'typeChequeCadeaus', 'siteName'));
    }

    public function searchProducts(Request $request)
    {
        $siteid = auth()->user()->siteid ?? 102;

        $query = DB::table('produit2s')
            ->join('produits', 'produits.produitid', '=', 'produit2s.produitid')
            ->leftJoin('couleurs', 'produit2s.couleurid', '=', 'couleurs.couleurid')
            ->leftJoin('tailles', 'produit2s.tailleid', '=', 'tailles.tailleid')
            ->leftJoin('familles', 'produits.familleid', '=', 'familles.familleid')
            ->leftJoin('sousfamilles', 'produits.sousfamilleid', '=', 'sousfamilles.sousfamilleid')
            ->leftJoin('fournisseurs', 'produits.fournisseurid', '=', 'fournisseurs.fournisseurid')
            ->leftJoin('vproduit2stocks', function($join) use ($siteid) {
                $join->on('vproduit2stocks.produit2id', '=', 'produit2s.produit2id')
                     ->where('vproduit2stocks.siteid', '=', $siteid);
            })
            ->select(
                'produits.produitid',
                'produit2s.produit2id',
                'produits.produitcode',
                'produits.reference',
                DB::raw("COALESCE(produit2s.barcode2, produits.barcode2) as barcode2"),
                'produits.produitlibelle',
                'tailles.taillelibelle',
                'couleurs.couleurlibelle',
                'familles.famillelibelle',
                'sousfamilles.sousfamillelibelle',
                'produits.ttc_vente',
                'fournisseurs.nom as fournisseur',
                DB::raw('COALESCE(vproduit2stocks.qtestock, 0) as total_stock'),
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
            $searchTerm = '%' . strtolower($request->search) . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->whereRaw('LOWER(produits.produitcode) LIKE ?', [$searchTerm])
                  ->orWhereRaw('LOWER(produits.reference) LIKE ?', [$searchTerm])
                  ->orWhereRaw('LOWER(produits.barcode2) LIKE ?', [$searchTerm])
                  ->orWhereRaw('LOWER(produit2s.barcode2) LIKE ?', [$searchTerm])
                  ->orWhereRaw('LOWER(produits.produitlibelle) LIKE ?', [$searchTerm]);
            });
        }

        $products = $query->limit(50)->get();

        return response()->json($products);
    }

    /**
     * Recherche rapide par scan (code-barres, référence, ou code produit)
     */
    public function scanProduct(Request $request)
    {
        $siteid = auth()->user()->siteid ?? 102;
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
                'produits.ttc_vente',
                DB::raw('COALESCE(vproduit2stocks.qtestock, 0) as total_stock'),
                'sousfamilles.is_loyalty_enabled'
            )
            ->first();

        return response()->json($product);
    }
    public function getProductVariants(Request $request)
    {
        $siteid = auth()->user()->siteid ?? 102;
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
            'telephone' => 'required|string|max:255',
        ]);

        try {
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
                'barcode' => $request->num_fidelite,
                'num_fidelite' => $request->num_fidelite, // Just in case, save to both
                'tel' => $request->telephone ?? '',
                'email' => $request->email ?? '',
                'datenaissance' => $request->date_naissance ?? '1900-01-01',
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

            $client = DB::table('clients')->where('clientid', $id)->first();

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
        $client->loyalty_tier = $this->getLoyaltyTier($client);
        return response()->json(['success' => true, 'client' => $client]);
    }

    /**
     * Update existing client
     */
    public function updateClient(Request $request, $id)
    {
        $request->validate([
            'raison' => 'required|string|max:255',
            'telephone' => 'required|string|max:255',
        ]);

        try {
            DB::table('clients')->where('clientid', $id)->update([
                'nom' => $request->raison,
                'prenom' => $request->prenom,
                'bloquercredit' => $request->has('bloque_credit') ? 1 : 0,
                'mf' => $request->matricule_fiscal,
                'fidelite' => $request->has('g_fidelite') ? 1 : 0,
                'barcode' => $request->num_fidelite,
                'num_fidelite' => $request->num_fidelite,
                'tel' => $request->telephone,
                'email' => $request->email,
                'datenaissance' => $request->date_naissance,
                'ville' => $request->ville,
                'adressefacturation' => $request->adresse,
                'adresselivraison' => $request->adresse,
            ]);

            $client = DB::table('clients')->where('clientid', $id)->first();

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
                'clients.nom as client',
                'clients.tel as telephone',
                'clients.num_fidelite as carte',
                'produits.reference',
                'produits.produitlibelle as designation',
                'couleurs.couleurlibelle as couleur',
                'tailles.taillelibelle as taille',
                'detctickets.qte',
                'detctickets.ttc',
                'detctickets.totalttc'
            );

        if ($request->filled('q')) {
            $search = '%' . strtolower($request->q) . '%';
            $query->where(function($q) use ($search) {
                $q->whereRaw('LOWER(clients.nom) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(clients.tel) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(clients.num_fidelite) LIKE ?', [$search]);
            });
        }

        $history = $query->orderBy('ctickets.cticketdate', 'desc')->limit(100)->get();

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
            'telephone' => 'required|string',
            'message' => 'required|string'
        ]);

        $tel = $request->telephone;
        $msg = $request->message;

        // Bip SMS Integration (Mocked or Real HTTP call)
        // Usually: GET https://bipsms.net/api/sendsms.php?user=USER&pass=PASS&sender=SENDER&phone=PHONE&msg=MSG
        // Fetch Bip SMS credentials from database settings (managed via Configuration Général UI)
        $user = DB::table('retailconfigs')->where('libelle', 'bipsms_user')->value('value') ?? 'demo';
        $pass = DB::table('retailconfigs')->where('libelle', 'bipsms_pass')->value('value') ?? 'demo';
        $sender = DB::table('retailconfigs')->where('libelle', 'bipsms_sender')->value('value') ?? 'VELARO';

        try {
            $response = Http::get('https://bipsms.net/api/sendsms.php', [
                'user' => $user,
                'pass' => $pass,
                'sender' => $sender,
                'phone' => $tel,
                'msg' => $msg
            ]);
            
            $success = $response->successful();
            \Log::info("SMS Envoyé à $tel : $msg - Status: " . $response->status());

            if ($success) {
                return response()->json(['success' => true, 'message' => 'SMS envoyé avec succès!']);
            } else {
                return response()->json(['success' => false, 'message' => 'Erreur lors de l\'envoi du SMS.']);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur Exception: ' . $e->getMessage()]);
        }
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
            ->where('numerointerne', $code)
            ->orWhere('cavoirnumero', $code)
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
                  ->orWhereRaw('LOWER(clientcode) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(tel) LIKE ?', [$search])
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

        $clients = $query->select('clientid', 'clientcode', 'nom', 'tel', 'adressefacturation', 'mf', 'remise', 'plafonremise', 'solde', 'ville', 'soldefidelite', 'pointfidelite', 'fidelite', 'total_ventes_fidelite', 'date_premiere_vente')
            ->orderBy('nom')
            ->paginate(15);
            
        // Map over the items to calculate loyalty_tier and override soldefidelite
        $clients->getCollection()->transform(function ($client) {
            $client->loyalty_tier = $this->getLoyaltyTier($client);
            
            // Override soldefidelite using our bons_achat table to bypass DB triggers
            $bonsAchatTotal = DB::table('bons_achat')
                ->where('clientid', $client->clientid)
                ->where('utilise', false)
                ->sum('montant');
            $client->soldefidelite = $bonsAchatTotal;
            
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

        // On affiche seulement l'Admin (ID 1) et l'utilisateur connecté
        $query = DB::table('employees')
            ->whereIn('employeeid', array_filter([1, $userEmployeeId]));

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
