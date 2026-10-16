<?php

namespace App\Http\Controllers\Transfert;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DemandeTransfertEnvoyeController extends Controller
{
    // Clé du verrou consultatif PostgreSQL qui sérialise la numérotation des demandes (max()+1)
    private const LOCK_NUMEROTATION_DEMANDE = 730102;

    public function index(Request $request)
    {
        $siteid = \App\Support\SiteCourant::id();

        // Données pour les filtres
        // Listes de référence lues en une seule requête (base distante : un aller-retour au lieu de 2)
        ['sites' => $sites, 'etats' => $etats] = \App\Support\LectureGroupee::listes([
            'sites' => DB::table('sites')->orderBy('libelle'),
            'etats' => DB::table('etatdemandetransferts'),
        ]);

        // Requête principale sur la vue
        $query = DB::table('demandetransfertsviews')
            ->where('siteid', $siteid);

        // Application des filtres de la barre d'outils (whereDate pour inclure toute la journée de fin)
        if ($request->filled('datedebut')) {
            $query->whereDate('demandetransfertdate', '>=', $request->datedebut);
        }
        if ($request->filled('datefin')) {
            $query->whereDate('demandetransfertdate', '<=', $request->datefin);
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
                          ->paginerVite($perPage);

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
        $site = DB::table('sites')->where('siteid', \App\Support\SiteCourant::id())->first();
        $sites = DB::table('sites')->orderBy('libelle')->get();
        
        // Données pour le modal de sélection produit
        // Listes de référence lues en une seule requête (base distante : un aller-retour au lieu de 5)
        ['familles' => $familles, 'sousFamilles' => $sousFamilles, 'saisons' => $saisons, 'categories' => $categories, 'marques' => $marques] = \App\Support\LectureGroupee::listes([
            'familles' => DB::table('familles')->select('familleid', 'famillelibelle')->orderBy('famillelibelle'),
            'sousFamilles' => DB::table('sousfamilles')->select('sousfamilleid', 'sousfamillelibelle', 'familleid', 'is_loyalty_enabled')->orderBy('sousfamillelibelle'),
            'saisons' => DB::table('categories4')->orderBy('category4libelle'),
            'categories' => DB::table('categories')->orderBy('categorylibelle'),
            'marques' => DB::table('categories2')->orderBy('category2libelle'),
        ]);

        return view('transfert.demande_envoye.create', compact('site', 'sites', 'familles', 'sousFamilles', 'saisons', 'categories', 'marques'));
    }

    public function store(Request $request)
    {
        // Le site demandeur est toujours celui de l'utilisateur connecté
        $siteid = \App\Support\SiteCourant::id();
        $siterecepteurid = $request->siterecepteurid;

        if ($siteid == $siterecepteurid) {
            return back()
                ->with('error', "Le site expéditeur et le site récepteur ne peuvent pas être identiques.")
                ->withInput();
        }

        $action_type = $request->input('action_type', 'save');

        $nouvel_etat = ($action_type === 'envoyer') ? 2 : 1; // 1 = Brouillon, 2 = Envoyé

        // Ignorer les lignes sans produit
        $lignes = array_values(array_filter((array) $request->input('lignes', []), function ($ligne) {
            return !empty($ligne['produitid']);
        }));

        return DB::transaction(function () use ($request, $siteid, $siterecepteurid, $nouvel_etat, $lignes) {
            $demande = null;

            if ($request->filled('demandetransfertid')) {
                $demande = DB::table('demandetransferts')
                    ->where('demandetransfertid', $request->demandetransfertid)
                    ->lockForUpdate()
                    ->first();

                if (!$demande) {
                    return redirect()->route('transfert.demande_envoye.index')->with('error', 'Demande introuvable.');
                }
                if ($demande->siteid != $siteid || $demande->etatdemandetransfertid != 1) {
                    return back()->with('error', 'Seules les demandes en brouillon de votre site peuvent être modifiées.');
                }

                $siterecepteurid = $demande->siterecepteurid;
            }

            // Validate stock availability on the receptor site (the site supplying the stock) before any DB inserts/updates
            if ($nouvel_etat === 2) {
                foreach ($lignes as $ligne) {
                    $qte = (int) $ligne['qte'];
                    $produit2id = $ligne['produit2id'] ?? $ligne['produitid'];

                    $stock = DB::table('vproduit2stocks')
                        ->where('produit2id', $produit2id)
                        ->where('siteid', $siterecepteurid)
                        ->value('qtestock');

                    $stockAvailable = $stock ? (float)$stock : 0.0;

                    if ($qte > $stockAvailable) {
                        $reference = DB::table('produits as p')
                            ->join('produit2s as p2', 'p2.produitid', '=', 'p.produitid')
                            ->where('p2.produit2id', $produit2id)
                            ->value('p.reference') ?? 'Produit';

                        return back()
                            ->with('error', "Stock insuffisant pour le produit {$reference} sur le site destinataire. Demandé: {$qte}, Disponible: {$stockAvailable}")
                            ->withInput();
                    }
                }
            }

            if ($demande) {
                $id = $demande->demandetransfertid;

                $entete = ['etatdemandetransfertid' => $nouvel_etat];
                // Ne pas écraser l'observation si le champ n'est pas envoyé
                if ($request->has('description')) {
                    $entete['description'] = $request->description;
                }

                DB::table('demandetransferts')
                    ->where('demandetransfertid', $id)
                    ->update($entete);

                // Clear old lines to replace them
                DB::table('detdemandetransferts')->where('demandetransfertid', $id)->delete();
                $numero = $demande->demandetransfertnumero;
            } else {
                // Sérialiser le calcul max()+1 du numéro pour éviter les doublons
                DB::statement('SELECT pg_advisory_xact_lock(?)', [self::LOCK_NUMEROTATION_DEMANDE]);

                // Récupérer le prochain ID depuis la séquence
                $id = DB::selectOne("SELECT nextval('demandetransfert_demandetransfertid_seq') as id")->id;

                // Générer un numéro de séquence (simplifié)
                $numero = (DB::table('demandetransferts')->max('demandetransfertnumero') ?? 0) + 1;
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
                    'description' => $request->description,
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

            $totalqte = 0;
            $totalht = 0;
            $totalttc = 0;

            foreach ($lignes as $index => $ligne) {
                $qte = (int) $ligne['qte'];
                $prix = (float) ($ligne['prix'] ?? 0);
                $taxe = \App\Support\TaxeProduit::pour($ligne['produitid']); // TVA réelle du produit
                $ht = \App\Support\TaxeProduit::ht($prix, $taxe['tva']);
                $totalqte += $qte;
                $totalttc += ($prix * $qte);
                $totalht += ($ht * $qte);

                DB::table('detdemandetransferts')->insert([
                    'demandetransfertid' => $id,
                    'siteid' => $siteid,
                    'siterecepteurid' => $siterecepteurid,
                    'produitid' => $ligne['produitid'],
                    'produit2id' => $ligne['produit2id'] ?? $ligne['produitid'],
                    'taxefamilleid' => $taxe['taxefamilleid'],
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
                    'tva' => $taxe['tva'],
                    'vtva' => round(($prix - $ht) * $qte, 3),
                    'totalttc' => $prix * $qte,
                    'totalttcnet' => $prix * $qte,
                    'qteecart' => 0,
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
                        'totaltva' => round($totalttc - $totalht, 3),
                        'totalttc' => $totalttc,
                        'netapayer' => $totalttc
                    ]);
            }

            return redirect()->route('transfert.demande_envoye.index')->with('success', 'En-tête de la demande enregistré avec succès ! (N° '.$numero.')');
        });
    }

    public function edit($id)
    {
        $siteid = \App\Support\SiteCourant::id();

        $demande = DB::table('demandetransferts')->where('demandetransfertid', $id)->first();
        if (!$demande) {
            return redirect()->route('transfert.demande_envoye.index')->with('error', 'Demande introuvable.');
        }
        if ($demande->siteid != $siteid || $demande->etatdemandetransfertid != 1) {
            return back()->with('error', 'Seules les demandes en brouillon de votre site peuvent être modifiées.');
        }

        // produitctviews : une ligne par produit2id, indépendante du stock d'un site (aucune ligne perdue)
        $lignes = DB::table('detdemandetransferts as det')
            ->leftJoin('produitctviews as p', 'p.produit2id', '=', 'det.produit2id')
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
            ->orderBy('det.ordre')
            ->get();

        $sites = DB::table('sites')->orderBy('libelle')->get();
        $site = DB::table('sites')->where('siteid', $siteid)->first();

        // Data for modal
        // Listes de référence lues en une seule requête (base distante : un aller-retour au lieu de 5)
        ['sousFamilles' => $sousFamilles, 'familles' => $familles, 'saisons' => $saisons, 'categories' => $categories, 'marques' => $marques] = \App\Support\LectureGroupee::listes([
            'sousFamilles' => DB::table('sousfamilles')->select('sousfamilleid', 'sousfamillelibelle', 'familleid', 'is_loyalty_enabled')->orderBy('sousfamillelibelle'),
            'familles' => DB::table('familles')->select('familleid', 'famillelibelle')->orderBy('famillelibelle'),
            'saisons' => DB::table('categories4')->orderBy('category4libelle'),
            'categories' => DB::table('categories')->orderBy('categorylibelle'),
            'marques' => DB::table('categories2')->orderBy('category2libelle'),
        ]);

        // For simplicity, reusing the create view and passing the existing data
        return view('transfert.demande_envoye.create', compact('sites', 'site', 'sousFamilles', 'familles', 'saisons', 'categories', 'marques', 'demande', 'lignes'));
    }

    public function destroy($id)
    {
        $siteid = \App\Support\SiteCourant::id();

        return DB::transaction(function () use ($id, $siteid) {
            $demande = DB::table('demandetransferts')->where('demandetransfertid', $id)->lockForUpdate()->first();
            if (!$demande) {
                return redirect()->route('transfert.demande_envoye.index')->with('error', 'Demande introuvable.');
            }
            if ($demande->siteid != $siteid || $demande->etatdemandetransfertid != 1) {
                return back()->with('error', 'Seules les demandes en brouillon de votre site peuvent être supprimées.');
            }

            DB::table('detdemandetransferts')->where('demandetransfertid', $id)->delete();
            DB::table('demandetransferts')->where('demandetransfertid', $id)->delete();

            return redirect()->route('transfert.demande_envoye.index')->with('success', 'Demande supprimée avec succès.');
        });
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
                'produits.qtestock as total_stock'
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
                'produits.qtestock'
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
                  ->orWhereRaw('LOWER(produits.produitlibelle) LIKE ?', [$searchTerm]);
            });
        }

        $products = $query->paginerVite(20);

        return response()->json($products);
    }
}
