<?php

namespace App\Http\Controllers\Vente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class JourneeController extends Controller
{
    /**
     * Valide un identifiant numérique (404 sinon).
     */
    private function validId($id): int
    {
        $value = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($value === false) {
            abort(404);
        }
        return $value;
    }

    /**
     * Affiche la liste des journées de caisse.
     */
    public function index(Request $request)
    {
        $query = DB::table('journalcaisses')
            ->leftJoin('users', 'journalcaisses.userid', '=', 'users.userid')
            ->leftJoin('sites', 'journalcaisses.siteid', '=', 'sites.siteid')
            ->select(
                'journalcaisses.*',
                'users.login as caissier_nom',
                'sites.libelle as agence_nom'
            );

        // Filtre par site par défaut (sauf si admin)
        $user = Auth::user();
        if ($user->siteid) {
            $query->where('journalcaisses.siteid', $user->siteid);
        }

        // Filtre par date
        $dateDu = $request->input('date_du', now()->format('Y-m-d'));
        $dateAu = $request->input('date_au', now()->format('Y-m-d'));

        if ($dateDu) {
            $query->whereDate('journalcaisses.dateouverture', '>=', $dateDu);
        }
        if ($dateAu) {
            $query->whereDate('journalcaisses.dateouverture', '<=', $dateAu);
        }

        $journees = $query->orderBy('journalcaisses.journalcaisseid', 'desc')->get();

        return view('vente.journee.index', compact('journees', 'dateDu', 'dateAu'));
    }

    /**
     * Affiche le ticket de clôture d'une journée.
     */
    public function show($id)
    {
        $id = $this->validId($id);

        $journee = DB::table('journalcaisses')
            ->leftJoin('users', 'journalcaisses.userid', '=', 'users.userid')
            ->leftJoin('caisses', 'journalcaisses.caisseid', '=', 'caisses.caisseid')
            ->select(
                'journalcaisses.*',
                'users.login as caissier_nom',
                'caisses.numero as caisse_numero'
            )
            ->where('journalcaisseid', $id)
            ->first();

        if (!$journee) {
            abort(404);
        }

        // Si l'utilisateur a une restriction de site
        $user = Auth::user();
        if ($user->siteid && $journee->siteid != $user->siteid) {
            abort(403, 'Accès non autorisé.');
        }

        return view('vente.journee.show', compact('journee'));
    }

    /**
     * Affiche le ticket de détails d'une journée (Détails Ventes Journée).
     */
    public function details($id)
    {
        $id = $this->validId($id);

        $journee = DB::table('journalcaisses')
            ->leftJoin('sites', 'journalcaisses.siteid', '=', 'sites.siteid')
            ->leftJoin('caisses', 'journalcaisses.caisseid', '=', 'caisses.caisseid')
            ->select(
                'journalcaisses.*',
                'sites.libelle as agence_nom',
                'caisses.numero as caisse_numero',
                'caisses.libelle as caisse_nom'
            )
            ->where('journalcaisseid', $id)
            ->first();

        if (!$journee) {
            abort(404);
        }

        // Restriction site
        $user = Auth::user();
        if ($user->siteid && $journee->siteid != $user->siteid) {
            abort(403, 'Accès non autorisé.');
        }

        // Récupération des lignes de tickets de la journée
        $lines = DB::table('detctickets')
            ->join('ctickets', 'detctickets.cticketid', '=', 'ctickets.cticketid')
            ->leftJoin('produits', 'detctickets.produitid', '=', 'produits.produitid')
            ->leftJoin('produit2s', 'detctickets.produit2id', '=', 'produit2s.produit2id')
            ->leftJoin('familles', 'produits.familleid', '=', 'familles.familleid')
            ->leftJoin('sousfamilles', 'produits.sousfamilleid', '=', 'sousfamilles.sousfamilleid')
            ->leftJoin('couleurs', 'produit2s.couleurid', '=', 'couleurs.couleurid')
            ->leftJoin('users', 'ctickets.userid', '=', 'users.userid')
            ->where('ctickets.journalcaisseid', $id)
            ->where('ctickets.enattente', false) // Exclure les tickets en attente (non payés)
            ->where('detctickets.qte', '!=', 0) // Ignorer les lignes vides
            ->select(
                'detctickets.qte',
                'produits.produitlibelle',
                'produits.produitcode',
                'familles.famillelibelle',
                'sousfamilles.sousfamillelibelle',
                'couleurs.couleurlibelle',
                'users.login as vendeur_nom'
            )
            ->get();

        $totalQte = $lines->sum('qte');
        // recettebrut n'est renseigné qu'à la clôture : pour une journée ouverte, calcul depuis les tickets
        if ($journee->isclosed) {
            $chiffre = (float) ($journee->recettebrut ?? 0);
        } else {
            $chiffre = (float) DB::table('ctickets')
                ->where('journalcaisseid', $id)
                ->where('enattente', false)
                ->sum('totalttc');
        }

        // 1. Agrégation par Famille > SousFamille > Produit
        $groupedByFamille = [];
        foreach ($lines as $line) {
            $famille = $line->famillelibelle ?: 'SANS FAMILLE';
            $sousFamille = $line->sousfamillelibelle ?: 'SANS SOUS-FAMILLE';
            $produit = $line->produitcode . ' ' . $line->produitlibelle;

            if (!isset($groupedByFamille[$famille])) {
                $groupedByFamille[$famille] = ['total_qte' => 0, 'sous_familles' => []];
            }
            if (!isset($groupedByFamille[$famille]['sous_familles'][$sousFamille])) {
                $groupedByFamille[$famille]['sous_familles'][$sousFamille] = ['total_qte' => 0, 'produits' => []];
            }
            if (!isset($groupedByFamille[$famille]['sous_familles'][$sousFamille]['produits'][$produit])) {
                $groupedByFamille[$famille]['sous_familles'][$sousFamille]['produits'][$produit] = 0;
            }

            $groupedByFamille[$famille]['sous_familles'][$sousFamille]['produits'][$produit] += $line->qte;
            $groupedByFamille[$famille]['sous_familles'][$sousFamille]['total_qte'] += $line->qte;
            $groupedByFamille[$famille]['total_qte'] += $line->qte;
        }

        // 2. Agrégation par Couleur
        $groupedByCouleur = [];
        foreach ($lines as $line) {
            $couleur = $line->couleurlibelle ?: 'SANS COULEUR';
            if (!isset($groupedByCouleur[$couleur])) {
                $groupedByCouleur[$couleur] = 0;
            }
            $groupedByCouleur[$couleur] += $line->qte;
        }

        // 3. Agrégation par Vendeur
        $groupedByVendeur = [];
        foreach ($lines as $line) {
            $vendeur = $line->vendeur_nom ?: 'Inconnu';
            if (!isset($groupedByVendeur[$vendeur])) {
                $groupedByVendeur[$vendeur] = 0;
            }
            $groupedByVendeur[$vendeur] += $line->qte;
        }

        return view('vente.journee.details', compact(
            'journee', 
            'totalQte', 
            'chiffre', 
            'groupedByFamille', 
            'groupedByCouleur', 
            'groupedByVendeur'
        ));
    }
    /**
     * Affiche l'interface d'ouverture de journée.
     */
    public function ouverture()
    {
        // Récupérer les caisses du site de l'utilisateur ou toutes les caisses
        $siteId = Auth::user()->siteid;
        
        $caissesQuery = DB::table('caisses')->select('caisseid', 'libelle', 'numero');
        
        if ($siteId) {
            $caissesQuery->where('siteid', $siteId);
        }
        
        $caisses = $caissesQuery->get();

        return view('vente.journee.ouverture', compact('caisses'));
    }

    /**
     * Enregistre l'ouverture de la journée (création du journalcaisse).
     */
    public function storeOuverture(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'caisseid' => [
                'required',
                'integer',
                // La caisse doit appartenir au site de l'utilisateur
                Rule::exists('caisses', 'caisseid')->where(function ($q) use ($user) {
                    if ($user->siteid) {
                        $q->where('siteid', $user->siteid);
                    }
                }),
            ],
            'fondcaisse' => 'required|numeric|min:0',
        ], [
            'caisseid.exists' => 'Cette caisse n\'existe pas ou n\'appartient pas à votre site.',
        ]);

        // journalcaisses.employeeid est NOT NULL avec FK vers employees : pas de valeur de repli
        if (empty($user->employeeid)) {
            return back()->with('error', 'Aucun employé n\'est associé à votre compte utilisateur. Impossible d\'ouvrir la journée.');
        }

        $error = DB::transaction(function () use ($request, $user) {
            // Verrouiller la caisse pour sérialiser les ouvertures concurrentes
            $caisse = DB::table('caisses')
                ->where('caisseid', $request->caisseid)
                ->lockForUpdate()
                ->first();

            // Vérifier si une journée est déjà ouverte pour cette caisse
            $isAlreadyOpen = DB::table('journalcaisses')
                ->where('caisseid', $caisse->caisseid)
                ->where(function ($q) {
                    $q->where('isclosed', false)
                      ->orWhereNull('isclosed');
                })
                ->exists();

            if ($isAlreadyOpen) {
                return 'Une journée est déjà ouverte pour cette caisse. Veuillez la clôturer d\'abord.';
            }

            // Vérifier si l'utilisateur a déjà une journée ouverte (sur une autre caisse)
            $userHasOpen = DB::table('journalcaisses')
                ->where('userid', $user->userid)
                ->where(function ($q) {
                    $q->where('isclosed', false)
                      ->orWhereNull('isclosed');
                })
                ->exists();

            if ($userHasOpen) {
                return 'Vous avez déjà une journée ouverte. Veuillez la clôturer d\'abord.';
            }

            // Numéro de journée séquentiel par caisse
            $numero = (int) DB::table('journalcaisses')
                ->where('caisseid', $caisse->caisseid)
                ->max('journalcaissenumero') + 1;

            // Créer l'entrée dans journalcaisses avec tous les champs requis (NOT NULL)
            DB::table('journalcaisses')->insert([
                'caisseid' => $caisse->caisseid,
                'journalcaissenumero' => $numero,
                'fondcaisse' => $request->fondcaisse,
                'dateouverture' => now(),
                'userid' => $user->userid,
                'employeeid' => $user->employeeid,
                'caissierclotureid' => 0,
                'siteid' => $caisse->siteid ?? ($user->siteid ?? 0),
                'isclosed' => false,
                'montantcloture' => 0,
                'montanttheorique' => 0,
                'envoyee' => false,
                'agencebid' => $caisse->agencebid ?? ($user->agencebid ?? 0),
            ]);

            return null;
        });

        if ($error) {
            return back()->with('error', $error);
        }

        return back()->with('success', 'La journée a été ouverte avec succès pour cette caisse !');
    }

    /**
     * Affiche l'état de la journée.
     */
    public function etat(Request $request)
    {
        $user = Auth::user();

        // Si un journalcaisseid est passé en paramètre (via le filtre), on l'utilise
        if ($request->has('journalcaisseid')) {
            $journalCaisseId = $this->validId($request->input('journalcaisseid'));

            $journalCaisse = DB::table('journalcaisses')
                ->where('journalcaisseid', $journalCaisseId)
                ->first();

            // Restriction site
            if ($journalCaisse && $user->siteid && $journalCaisse->siteid != $user->siteid) {
                abort(403, 'Accès non autorisé.');
            }
        } else {
            // Recherche de la session de caisse ouverte
            $journalCaisse = DB::table('journalcaisses')
                ->where(function ($q) {
                    $q->where('isclosed', false)
                      ->orWhereNull('isclosed');
                })
                ->where('userid', $user->userid ?? $user->id)
                ->when($user->siteid, fn ($q) => $q->where('siteid', $user->siteid))
                ->orderBy('journalcaisseid', 'desc')
                ->first();

            // Si aucune session ouverte trouvée, chercher par site
            if (!$journalCaisse && !empty($user->siteid)) {
                $journalCaisse = DB::table('journalcaisses')
                    ->where(function ($q) {
                        $q->where('isclosed', false)
                          ->orWhereNull('isclosed');
                    })
                    ->where('siteid', $user->siteid)
                    ->orderBy('journalcaisseid', 'desc')
                    ->first();

                // Si toujours pas trouvé, chercher dans les caisses du site de l'utilisateur
                if (!$journalCaisse) {
                    $caissesSite = DB::table('caisses')->where('siteid', $user->siteid)->pluck('caisseid');
                    if ($caissesSite->isNotEmpty()) {
                        $journalCaisse = DB::table('journalcaisses')
                            ->whereIn('caisseid', $caissesSite)
                            ->where(function ($q) {
                                $q->where('isclosed', false)
                                  ->orWhereNull('isclosed');
                            })
                            ->orderBy('journalcaisseid', 'desc')
                            ->first();
                    }
                }
            }
        }

        if (!$journalCaisse) {
            return redirect()->route('vente.journee.ouverture')
                ->with('warning', 'Aucune journée ouverte trouvée. Veuillez d\'abord ouvrir une journée.');
        }

        $caisse = DB::table('caisses')->where('caisseid', $journalCaisse->caisseid)->first();
        $adminName = DB::table('users')->where('userid', $journalCaisse->userid)->value('login') ?? 'Admin';

        // 1. Ventes & Retours Effectuées
        $ventesRaw = DB::table('detctickets')
            ->join('ctickets', 'detctickets.cticketid', '=', 'ctickets.cticketid')
            ->join('produits', 'detctickets.produitid', '=', 'produits.produitid')
            ->leftJoin('produit2s', 'detctickets.produit2id', '=', 'produit2s.produit2id')
            ->leftJoin('couleurs', 'produit2s.couleurid', '=', 'couleurs.couleurid')
            ->leftJoin('tailles', 'produit2s.tailleid', '=', 'tailles.tailleid')
            ->where('ctickets.journalcaisseid', $journalCaisse->journalcaisseid)
            ->where('ctickets.enattente', false)
            ->select(
                'produits.produitcode as code',
                'produits.reference',
                'produits.produitlibelle as designation',
                'couleurs.couleurlibelle as couleur',
                'tailles.taillelibelle as taille',
                'detctickets.ttc as pv_u_ttc',
                DB::raw('SUM(detctickets.qte) as qtes'),
                // detctickets.remise est un pourcentage : remise en montant = brut - net
                DB::raw('SUM(detctickets.totalttc - detctickets.totalttcnet) as remise'),
                DB::raw('SUM(detctickets.totalttcnet) as montant'),
                // Ventes et retours séparés ligne par ligne (un produit vendu puis retourné ne s'annule pas)
                DB::raw('SUM(CASE WHEN detctickets.qte > 0 THEN detctickets.qte ELSE 0 END) as qte_vente'),
                DB::raw('SUM(CASE WHEN detctickets.qte > 0 THEN detctickets.totalttcnet ELSE 0 END) as montant_vente'),
                DB::raw('SUM(CASE WHEN detctickets.qte < 0 THEN detctickets.qte ELSE 0 END) as qte_retour'),
                DB::raw('SUM(CASE WHEN detctickets.qte < 0 THEN detctickets.totalttcnet ELSE 0 END) as montant_retour'),
                DB::raw('SUM(CASE WHEN detctickets.qte > 0 THEN detctickets.totalttc - detctickets.totalttcnet ELSE 0 END) as remise_vente'),
                DB::raw('SUM(CASE WHEN detctickets.qte < 0 THEN detctickets.totalttc - detctickets.totalttcnet ELSE 0 END) as remise_retour')
            )
            ->groupBy('produits.produitcode', 'produits.reference', 'produits.produitlibelle', 'couleurs.couleurlibelle', 'tailles.taillelibelle', 'detctickets.ttc')
            ->get();

        $totalVentesQte = $ventesRaw->sum('qte_vente');
        $totalVentesMontant = $ventesRaw->sum('montant_vente');
        $totalRetourQte = $ventesRaw->sum('qte_retour');
        $totalRetourMontant = $ventesRaw->sum('montant_retour');
        $totalVentesRemise = $ventesRaw->sum('remise_vente');
        $totalRetourRemise = $ventesRaw->sum('remise_retour');

        // 2. Chiffre d'Affaire / Sous Familles
        $caSousFamilles = DB::table('detctickets')
            ->join('ctickets', 'detctickets.cticketid', '=', 'ctickets.cticketid')
            ->join('produits', 'detctickets.produitid', '=', 'produits.produitid')
            ->leftJoin('sousfamilles', 'produits.sousfamilleid', '=', 'sousfamilles.sousfamilleid')
            ->where('ctickets.journalcaisseid', $journalCaisse->journalcaisseid)
            ->where('ctickets.enattente', false)
            ->select(
                'sousfamilles.sousfamilleid as code',
                'sousfamilles.sousfamillelibelle as libelle',
                DB::raw('MAX(detctickets.ttc) as pv_u_ttc'),
                DB::raw('SUM(detctickets.qte) as qtes'),
                DB::raw('SUM(detctickets.totalttc - detctickets.totalttcnet) as remise'),
                DB::raw('SUM(detctickets.totalttcnet) as montant'),
                DB::raw('SUM(CASE WHEN detctickets.qte > 0 THEN detctickets.qte ELSE 0 END) as qte_vente'),
                DB::raw('SUM(CASE WHEN detctickets.qte > 0 THEN detctickets.totalttcnet ELSE 0 END) as montant_vente'),
                DB::raw('SUM(CASE WHEN detctickets.qte > 0 THEN detctickets.totalttc - detctickets.totalttcnet ELSE 0 END) as remise_vente'),
                DB::raw('SUM(CASE WHEN detctickets.qte < 0 THEN detctickets.qte ELSE 0 END) as qte_retour'),
                DB::raw('SUM(CASE WHEN detctickets.qte < 0 THEN detctickets.totalttcnet ELSE 0 END) as montant_retour'),
                DB::raw('SUM(CASE WHEN detctickets.qte < 0 THEN detctickets.totalttc - detctickets.totalttcnet ELSE 0 END) as remise_retour')
            )
            ->groupBy('sousfamilles.sousfamilleid', 'sousfamilles.sousfamillelibelle')
            ->get();

        // 3. CA / Vendeur
        $caVendeur = DB::table('ctickets')
            ->leftJoin('employees', 'ctickets.employeeid', '=', 'employees.employeeid')
            ->where('ctickets.journalcaisseid', $journalCaisse->journalcaisseid)
            ->where('ctickets.enattente', false)
            ->select(
                'employees.nom',
                'employees.prenom',
                DB::raw('SUM(ctickets.totalqte) as qte'),
                DB::raw('SUM(ctickets.totalttc) as montant')
            )
            ->groupBy('employees.employeeid', 'employees.nom', 'employees.prenom')
            ->get();
            
        $totalVendeurMontant = $caVendeur->sum('montant');

        // 4. Détails Recettes & Caisse
        $reglements = DB::table('creglements')
            ->where('journalcaisseid', $journalCaisse->journalcaisseid)
            ->select('modereglementid', DB::raw('SUM(montant) as total'))
            ->groupBy('modereglementid')
            ->get()
            ->keyBy('modereglementid');

        $mode = fn (int $id) => (float) ($reglements->get($id)->total ?? 0);

        // Total de tous les règlements de la journée, tous modes confondus
        $totalRecettesDetails = (float) $reglements->sum('total');

        $recettes = [
            'espece' => $mode(1),
            'depense' => (float) ($journalCaisse->montantdepense ?? 0),
            'espece_net' => $mode(1) - (float) ($journalCaisse->montantdepense ?? 0),
            'cheque' => $mode(2),
            'traite' => $mode(3),
            'carte_credit' => $mode(4),
            'cheque_cadeau' => $mode(5),
            'ticket_resto' => $mode(6),
            'offre' => $mode(7),
            'bon_convention' => $mode(8),
            'avoir' => $mode(9),
            'bon_achats' => $mode(10),
            'virement' => $mode(11),
            'coupon' => $mode(13),
        ];
        // "Autres" = mode 12 + tout mode non listé ci-dessus, pour que le détail égale le total
        $recettes['autres'] = $totalRecettesDetails
            - array_sum(array_diff_key($recettes, array_flip(['depense', 'espece_net'])));

        // Les règlements de complément d'acompte (creglements.iscomplement) sont déjà inclus
        // dans $totalRecettesDetails : ils sont seulement ventilés, pas ajoutés une seconde fois.
        $acomptes_nv = (float) ($journalCaisse->acomptenewticket ?? 0);
        $acomptes_av = (float) DB::table('creglements')
            ->where('journalcaisseid', $journalCaisse->journalcaisseid)
            ->where('iscomplement', true)
            ->sum('montant');
        $acomptes_personnels = (float) ($journalCaisse->acomptepersonnel ?? 0);
        $commissions = (float) ($journalCaisse->totalcommission ?? 0);

        // Recette Brute & Nette calculations
        $recetteBrute = $totalRecettesDetails;
        $recetteNette = $recetteBrute - $recettes['depense'] - $acomptes_personnels - $commissions;

        $caisseTotaux = [
            'ventes_reglees' => $totalRecettesDetails - $acomptes_nv - $acomptes_av,
            'acomptes_nv' => $acomptes_nv,
            'acomptes_av' => $acomptes_av,
            'recette_brute' => $recetteBrute,
            'depenses_divers' => $recettes['depense'],
            'acomptes_personnels' => $acomptes_personnels,
            'commissions' => $commissions,
            'recette_nette' => $recetteNette,
        ];

        return view('vente.journee.etat', compact(
            'journalCaisse', 'caisse', 'adminName',
            'ventesRaw', 'totalVentesQte', 'totalVentesMontant', 'totalRetourQte', 'totalRetourMontant', 'totalVentesRemise', 'totalRetourRemise',
            'caSousFamilles', 'caVendeur', 'totalVendeurMontant',
            'recettes', 'caisseTotaux', 'totalRecettesDetails'
        ));
    }

    /**
     * Filtre AJAX pour rechercher les sessions de caisse par date.
     */
    public function etatFilter(Request $request)
    {
        $du = $request->input('du');
        $au = $request->input('au');

        $query = DB::table('journalcaisses')
            ->leftJoin('caisses', 'journalcaisses.caisseid', '=', 'caisses.caisseid')
            ->select(
                'journalcaisses.journalcaisseid',
                'journalcaisses.journalcaissenumero',
                'journalcaisses.dateouverture',
                'journalcaisses.datecloture',
                'journalcaisses.montanttheorique',
                'journalcaisses.montantcloture',
                'journalcaisses.isclosed',
                'caisses.libelle as caisse_libelle'
            )
            ->orderBy('journalcaisses.dateouverture', 'desc');

        // Restriction au site de l'utilisateur
        $user = Auth::user();
        if ($user->siteid) {
            $query->where('journalcaisses.siteid', $user->siteid);
        }

        if ($du) {
            $query->whereDate('journalcaisses.dateouverture', '>=', $du);
        }
        if ($au) {
            $query->whereDate('journalcaisses.dateouverture', '<=', $au);
        }

        $sessions = $query->limit(60)->get();

        return response()->json($sessions);
    }

    /**
     * Affiche l'interface de clôture de journée.
     */
    public function cloture()
    {
        $user = Auth::user();

        // Recherche de la session de caisse ouverte (isclosed = false ou NULL)
        $journalCaisse = DB::table('journalcaisses')
            ->where(function ($q) {
                $q->where('isclosed', false)
                  ->orWhereNull('isclosed');
            })
            ->where('userid', $user->userid ?? $user->id)
            ->when($user->siteid, fn ($q) => $q->where('siteid', $user->siteid))
            ->orderBy('journalcaisseid', 'desc')
            ->first();

        // Si aucune session ouverte trouvée, chercher par site
        if (!$journalCaisse && !empty($user->siteid)) {
            $journalCaisse = DB::table('journalcaisses')
                ->where(function ($q) {
                    $q->where('isclosed', false)
                      ->orWhereNull('isclosed');
                })
                ->where('siteid', $user->siteid)
                ->orderBy('journalcaisseid', 'desc')
                ->first();
                
            // Si toujours pas trouvé, chercher dans les caisses du site de l'utilisateur
            if (!$journalCaisse) {
                $caissesSite = DB::table('caisses')->where('siteid', $user->siteid)->pluck('caisseid');
                if ($caissesSite->isNotEmpty()) {
                    $journalCaisse = DB::table('journalcaisses')
                        ->whereIn('caisseid', $caissesSite)
                        ->where(function ($q) {
                            $q->where('isclosed', false)
                              ->orWhereNull('isclosed');
                        })
                        ->orderBy('journalcaisseid', 'desc')
                        ->first();
                }
            }
        }

        if (!$journalCaisse) {
            return redirect()->route('vente.journee.ouverture')
                ->with('warning', 'Aucune journée ouverte trouvée. Veuillez d\'abord ouvrir une journée.');
        }

        // Récupérer le libellé de la caisse
        $caisse = DB::table('caisses')
            ->where('caisseid', $journalCaisse->caisseid)
            ->first();

        // Totaux théoriques (règlements) et totaux des tickets de cette session
        $theoriques = $this->clotureTheoriques($journalCaisse);
        $ticketsTotals = $this->clotureTicketsTotals($journalCaisse->journalcaisseid);

        return view('vente.journee.cloture', compact(
            'journalCaisse',
            'caisse',
            'theoriques',
            'ticketsTotals'
        ));
    }

    /**
     * Totaux théoriques par mode de paiement pour la clôture.
     *
     * Modes comptés (théorique ET physique) : 1 Espèce, 2 Chèque, 4 Carte Bancaire,
     * 5 Chèque Cadeaux, 6 Ticket Resto, 8 Bon de convention, 9 Avoir, 13 Coupon,
     * et "Autres" = 3 Traite, 10 Bon d'achat, 11 Virement, 12 Autre (+ tout mode inconnu).
     * 7 Offre n'est pas un moyen de paiement physique : affiché et stocké (totaloffre)
     * mais exclu des deux côtés de l'écart.
     */
    private function clotureTheoriques($journalCaisse): array
    {
        $reglements = DB::table('creglements')
            ->where('journalcaisseid', $journalCaisse->journalcaisseid)
            ->select('modereglementid', DB::raw('SUM(montant) as total'))
            ->groupBy('modereglementid')
            ->get()
            ->keyBy('modereglementid');

        $mode = fn (int $id) => (float) ($reglements->get($id)->total ?? 0);

        $theoriques = [
            'fondcaisse'     => (float) $journalCaisse->fondcaisse,
            'espece'         => $mode(1),
            'cheque'         => $mode(2),
            'carte_bancaire' => $mode(4),
            'cheque_cadeaux' => $mode(5),
            'ticket_resto'   => $mode(6),
            'offre'          => $mode(7),
            'bon_convention' => $mode(8),
            'avoir'          => $mode(9),
            'coupon'         => $mode(13),
        ];

        // Autres = tout ce qui n'est pas ventilé ci-dessus (3, 10, 11, 12, modes inconnus)
        $theoriques['autre'] = (float) $reglements->sum('total')
            - array_sum(array_diff_key($theoriques, ['fondcaisse' => true]));

        // Dépenses payées en espèces depuis la caisse (dont retours d'argent aux clients) : sorties du tiroir
        $theoriques['espece'] -= (float) DB::table('depenses')
            ->where('journalcaisseid', $journalCaisse->journalcaisseid)
            ->where('modereglementid', 1)
            ->sum('montant');

        return $theoriques;
    }

    /**
     * Totaux des tickets (hors tickets en attente) pour la clôture.
     */
    private function clotureTicketsTotals($journalCaisseId)
    {
        return DB::table('ctickets')
            ->where('journalcaisseid', $journalCaisseId)
            ->where('enattente', false)
            ->select(
                DB::raw('COUNT(*) as nbreticket'),
                DB::raw('SUM(totalttc) as recettebrut'),
                DB::raw('SUM(totalqte) as totalqtevente'),
                DB::raw('SUM(acompte) as ventereglee'),
                DB::raw('SUM(vremise) as vtotalremise')
            )
            ->first();
    }

    /**
     * Enregistre la clôture de la journée.
     */
    public function storeCloture(Request $request)
    {
        $request->validate([
            'journalcaisseid' => 'required|integer',
            'totalespecephys' => 'nullable|numeric|min:0',
            'totalchequephys' => 'nullable|numeric|min:0',
            'totaltpephys' => 'nullable|numeric|min:0',
            'totalcontrebonphys' => 'nullable|numeric|min:0',
            'totaltkrestophys' => 'nullable|numeric|min:0',
            'totalbonconventionphys' => 'nullable|numeric|min:0',
            'totalregavoirphys' => 'nullable|numeric|min:0',
            'totalcouponphys' => 'nullable|numeric|min:0',
            'totalregautrephys' => 'nullable|numeric|min:0',
        ]);

        $user = Auth::user();

        $closed = DB::transaction(function () use ($request, $user) {
            // Verrouiller la session pour éviter une double clôture concurrente
            $journalCaisse = DB::table('journalcaisses')
                ->where('journalcaisseid', $request->journalcaisseid)
                ->where(function ($q) {
                    $q->where('isclosed', false)
                      ->orWhereNull('isclosed');
                })
                ->lockForUpdate()
                ->first();

            if (!$journalCaisse) {
                return false;
            }

            // Seul un utilisateur du site de la journée peut la clôturer
            if ($journalCaisse->siteid != $user->siteid) {
                abort(403, 'Vous ne pouvez pas clôturer une journée d\'un autre site.');
            }

            // Recalculer les totaux théoriques et les totaux des tickets
            $theoriques    = $this->clotureTheoriques($journalCaisse);
            $ticketsTotals = $this->clotureTicketsTotals($journalCaisse->journalcaisseid);

            // Valeurs théoriques
            $fondCaisse     = $theoriques['fondcaisse'];
            $totalEspece    = $theoriques['espece'];
            $totalCheque    = $theoriques['cheque'];
            $totalTpe       = $theoriques['carte_bancaire'];
            $totalContrebon = $theoriques['cheque_cadeaux'];
            $totalTkResto   = $theoriques['ticket_resto'];
            $totalOffre     = $theoriques['offre'];
            $totalBonConv   = $theoriques['bon_convention'];
            $totalAvoir     = $theoriques['avoir'];
            $totalCoupon    = $theoriques['coupon'];
            $totalAutre     = $theoriques['autre'];

            // Valeurs physiques (saisies par le caissier). L'espèce saisie est hors fond de caisse :
            // la vue affiche le fond sur une ligne séparée dont le montant physique est le fond lui-même.
            $especePhys    = (float) ($request->totalespecephys ?? 0);
            $chequePhys    = (float) ($request->totalchequephys ?? 0);
            $tpePhys       = (float) ($request->totaltpephys ?? 0);
            $contrebonPhys = (float) ($request->totalcontrebonphys ?? 0);
            $tkRestoPhys   = (float) ($request->totaltkrestophys ?? 0);
            $bonConvPhys   = (float) ($request->totalbonconventionphys ?? 0);
            $avoirPhys     = (float) ($request->totalregavoirphys ?? 0);
            $couponPhys    = (float) ($request->totalcouponphys ?? 0);
            $autrePhys     = (float) ($request->totalregautrephys ?? 0);

            // Calculs agrégés : le fond de caisse est inclus des deux côtés (théorique et physique)
            $recettebrut      = (float) ($ticketsTotals->recettebrut ?? 0);
            $recettenet       = $recettebrut;
            $ventereglee      = (float) ($ticketsTotals->ventereglee ?? 0);
            $montanttheorique = $totalEspece + $fondCaisse;
            $montantcloture   = $especePhys + $fondCaisse;
            $totalTheoriqueTousModes = $montanttheorique + $totalCheque + $totalTpe + $totalContrebon
                + $totalTkResto + $totalBonConv + $totalAvoir + $totalCoupon + $totalAutre;
            $recettephysique = $montantcloture + $chequePhys + $tpePhys + $contrebonPhys
                + $tkRestoPhys + $bonConvPhys + $avoirPhys + $couponPhys + $autrePhys;
            $ecart      = $recettephysique - $totalTheoriqueTousModes;
            $totalecart = $montantcloture - $montanttheorique;

            // Mise à jour de l'enregistrement journalcaisses
            DB::table('journalcaisses')
                ->where('journalcaisseid', $journalCaisse->journalcaisseid)
                ->update([
                    'isclosed'         => true,
                    'datecloture'      => now(),
                    'caissierclotureid' => $user->userid,

                    // Totaux théoriques (depuis les règlements)
                    'totalespece'           => $totalEspece,
                    'totalcheque'           => $totalCheque,
                    'totaltpe'              => $totalTpe,
                    'totalcontrebon'        => $totalContrebon,
                    'totaltkresto'          => $totalTkResto,
                    'totaloffre'            => $totalOffre,
                    'totalbonconvention'    => $totalBonConv,
                    'totalregavoir'         => $totalAvoir,
                    'totalcoupon'           => $totalCoupon,
                    'totalregautre'         => $totalAutre,
                    'totalespecenet'        => $totalEspece,

                    // Totaux physiques (comptés par le caissier)
                    'totalespecephys'       => $especePhys,
                    'totalchequephys'       => $chequePhys,
                    'totaltpephys'          => $tpePhys,
                    'totalcontrebonphys'    => $contrebonPhys,
                    'totaltkrestophys'      => $tkRestoPhys,
                    'totalbonconventionphys' => $bonConvPhys,
                    'totalregavoirphys'     => $avoirPhys,
                    'totalregautrephys'     => $autrePhys,

                    // Écarts par mode (pas de colonne "phys" pour le coupon : l'écart est conservé)
                    'totalecartchequecadeau'  => $contrebonPhys - $totalContrebon,
                    'totalecartbonconvention' => $bonConvPhys - $totalBonConv,
                    'totalecartcoupon'        => $couponPhys - $totalCoupon,

                    // Agrégats
                    'montantcloture'    => $montantcloture,
                    'montanttheorique'  => $montanttheorique,
                    'recettebrut'       => $recettebrut,
                    'recettenet'        => $recettenet,
                    'recettephysique'   => $recettephysique,
                    'ventereglee'       => $ventereglee,
                    'ecart'             => $ecart,
                    'totalecart'        => $totalecart,

                    // Tickets
                    'nbreticket'        => (float) ($ticketsTotals->nbreticket ?? 0),
                    'totalqtevente'     => (float) ($ticketsTotals->totalqtevente ?? 0),
                    'vtotalremise'      => (float) ($ticketsTotals->vtotalremise ?? 0),
                ]);

            return true;
        });

        if (!$closed) {
            return back()->with('error', 'Session de caisse introuvable ou déjà clôturée.');
        }

        return redirect()->route('vente.journee.cloture')
            ->with('success', 'La journée a été clôturée avec succès !');
    }
}
