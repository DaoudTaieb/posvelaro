<?php

namespace App\Http\Controllers\Vente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class JourneeController extends Controller
{
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
        $request->validate([
            'caisseid' => 'required|integer',
            'fondcaisse' => 'required|numeric|min:0',
        ]);

        $user = Auth::user();
        
        // Vérifier si une journée est déjà ouverte pour cette caisse
        $isAlreadyOpen = DB::table('journalcaisses')
            ->where('caisseid', $request->caisseid)
            ->where('isclosed', false)
            ->exists();

        if ($isAlreadyOpen) {
            return back()->with('error', 'Une journée est déjà ouverte pour cette caisse. Veuillez la clôturer d\'abord.');
        }

        $caisse = DB::table('caisses')->where('caisseid', $request->caisseid)->first();

        // Créer l'entrée dans journalcaisses avec tous les champs requis (NOT NULL)
        DB::table('journalcaisses')->insert([
            'caisseid' => $request->caisseid,
            'fondcaisse' => $request->fondcaisse,
            'dateouverture' => now(),
            'userid' => $user->userid,
            'employeeid' => $user->employeeid ?? 0,
            'caissierclotureid' => 0,
            'siteid' => $user->siteid ?? ($caisse->siteid ?? 0),
            'isclosed' => false,
            'montantcloture' => 0,
            'montanttheorique' => 0,
            'envoyee' => false,
            'agencebid' => $user->agencebid ?? ($caisse->agencebid ?? 0),
        ]);

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
            $journalCaisse = DB::table('journalcaisses')
                ->where('journalcaisseid', $request->journalcaisseid)
                ->first();
        } else {
            // Recherche de la session de caisse ouverte
            $journalCaisse = DB::table('journalcaisses')
                ->where(function ($q) {
                    $q->where('isclosed', false)
                      ->orWhereNull('isclosed');
                })
                ->where('userid', $user->userid)
                ->orderBy('journalcaisseid', 'desc')
                ->first();

            // Si aucune session ouverte trouvée, chercher par site
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
        }

        if (!$journalCaisse) {
            return redirect()->route('vente.journee.ouverture')
                ->with('error', 'Aucune journée ouverte trouvée. Veuillez d\'abord ouvrir une journée.');
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
            ->select(
                'produits.produitcode as code',
                'produits.reference',
                'produits.produitlibelle as designation',
                'couleurs.couleurlibelle as couleur',
                'tailles.taillelibelle as taille',
                'detctickets.ttc as pv_u_ttc',
                DB::raw('SUM(detctickets.qte) as qtes'),
                DB::raw('SUM(detctickets.remise) as remise'),
                DB::raw('SUM(detctickets.totalttc) as montant')
            )
            ->groupBy('produits.produitcode', 'produits.reference', 'produits.produitlibelle', 'couleurs.couleurlibelle', 'tailles.taillelibelle', 'detctickets.ttc')
            ->get();

        $totalVentesQte = 0;
        $totalVentesMontant = 0;
        $totalRetourQte = 0;
        $totalRetourMontant = 0;

        foreach($ventesRaw as $v) {
            if ($v->qtes >= 0) {
                $totalVentesQte += $v->qtes;
                $totalVentesMontant += $v->montant;
            } else {
                $totalRetourQte += $v->qtes;
                $totalRetourMontant += $v->montant;
            }
        }

        // 2. Chiffre d'Affaire / Sous Familles
        $caSousFamilles = DB::table('detctickets')
            ->join('ctickets', 'detctickets.cticketid', '=', 'ctickets.cticketid')
            ->join('produits', 'detctickets.produitid', '=', 'produits.produitid')
            ->leftJoin('sousfamilles', 'produits.sousfamilleid', '=', 'sousfamilles.sousfamilleid')
            ->where('ctickets.journalcaisseid', $journalCaisse->journalcaisseid)
            ->select(
                'sousfamilles.sousfamilleid as code',
                'sousfamilles.sousfamillelibelle as libelle',
                DB::raw('MAX(detctickets.ttc) as pv_u_ttc'),
                DB::raw('SUM(detctickets.qte) as qtes'),
                DB::raw('SUM(detctickets.remise) as remise'),
                DB::raw('SUM(detctickets.totalttc) as montant')
            )
            ->groupBy('sousfamilles.sousfamilleid', 'sousfamilles.sousfamillelibelle')
            ->get();

        // 3. CA / Vendeur
        $caVendeur = DB::table('ctickets')
            ->leftJoin('employees', 'ctickets.employeeid', '=', 'employees.employeeid')
            ->where('ctickets.journalcaisseid', $journalCaisse->journalcaisseid)
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

        $recettes = [
            'espece' => (float) ($reglements->get(1)->total ?? 0),
            'depense' => (float) ($journalCaisse->montantdepense ?? 0),
            'espece_net' => (float) ($reglements->get(1)->total ?? 0) - (float) ($journalCaisse->montantdepense ?? 0),
            'cheque' => (float) ($reglements->get(2)->total ?? 0),
            'carte_credit' => (float) ($reglements->get(4)->total ?? 0),
            'bon_achats' => (float) ($reglements->get(8)->total ?? 0), // Convention or Achat
            'cheque_cadeau' => (float) ($reglements->get(5)->total ?? 0),
            'autres' => (float) ($reglements->get(12)->total ?? 0),
        ];
        
        $recetteNette = array_sum($recettes) - $recettes['depense'] * 2; // Adjust since depense is positive but shouldn't add to sum
        // More accurate Recette Nette calculation
        $recetteBrute = $recettes['espece'] + $recettes['cheque'] + $recettes['carte_credit'] + $recettes['bon_achats'] + $recettes['cheque_cadeau'] + $recettes['autres'];
        
        $caisseTotaux = [
            'ventes_reglees' => $recetteBrute,
            'acomptes_nv' => 0,
            'acomptes_av' => 0,
            'recette_brute' => $recetteBrute,
            'depenses_divers' => $recettes['depense'],
            'acomptes_personnels' => 0,
            'commissions' => 0,
            'recette_nette' => $recetteBrute - $recettes['depense'],
        ];

        return view('vente.journee.etat', compact(
            'journalCaisse', 'caisse', 'adminName',
            'ventesRaw', 'totalVentesQte', 'totalVentesMontant', 'totalRetourQte', 'totalRetourMontant',
            'caSousFamilles', 'caVendeur', 'totalVendeurMontant',
            'recettes', 'caisseTotaux'
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
            ->where('userid', $user->userid)
            ->orderBy('journalcaisseid', 'desc')
            ->first();

        // Si aucune session ouverte trouvée, chercher par site
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

        if (!$journalCaisse) {
            return redirect()->route('vente.journee.ouverture')
                ->with('error', 'Aucune journée ouverte trouvée. Veuillez d\'abord ouvrir une journée.');
        }

        // Récupérer le libellé de la caisse
        $caisse = DB::table('caisses')
            ->where('caisseid', $journalCaisse->caisseid)
            ->first();

        // Calculer les totaux théoriques depuis les règlements de cette session
        $reglements = DB::table('creglements')
            ->where('journalcaisseid', $journalCaisse->journalcaisseid)
            ->select('modereglementid', DB::raw('SUM(montant) as total'))
            ->groupBy('modereglementid')
            ->get()
            ->keyBy('modereglementid');

        // Calculer les totaux depuis les tickets de cette session
        $ticketsTotals = DB::table('ctickets')
            ->where('journalcaisseid', $journalCaisse->journalcaisseid)
            ->select(
                DB::raw('COUNT(*) as nbreticket'),
                DB::raw('SUM(totalttc) as recettebrut'),
                DB::raw('SUM(totalqte) as totalqtevente'),
                DB::raw('SUM(acompte) as ventereglee'),
                DB::raw('SUM(remise) as vtotalremise')
            )
            ->first();

        // Construire les totaux théoriques par mode de paiement
        $theoriques = [
            'fondcaisse'       => (float) $journalCaisse->fondcaisse,
            'espece'           => (float) ($reglements->get(1)->total ?? 0),
            'cheque'           => (float) ($reglements->get(2)->total ?? 0),
            'carte_bancaire'   => (float) ($reglements->get(4)->total ?? 0),
            'cheque_cadeaux'   => (float) ($reglements->get(5)->total ?? 0),
            'bon_convention'   => (float) ($reglements->get(8)->total ?? 0),
            'avoir'            => (float) ($reglements->get(9)->total ?? 0),
        ];

        return view('vente.journee.cloture', compact(
            'journalCaisse',
            'caisse',
            'theoriques',
            'ticketsTotals'
        ));
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
            'totalbonconventionphys' => 'nullable|numeric|min:0',
            'totalregavoirphys' => 'nullable|numeric|min:0',
        ]);

        $user = Auth::user();

        // Vérifier que la session existe et est bien ouverte
        $journalCaisse = DB::table('journalcaisses')
            ->where('journalcaisseid', $request->journalcaisseid)
            ->where(function ($q) {
                $q->where('isclosed', false)
                  ->orWhereNull('isclosed');
            })
            ->first();

        if (!$journalCaisse) {
            return back()->with('error', 'Session de caisse introuvable ou déjà clôturée.');
        }

        // Recalculer les totaux théoriques depuis les règlements
        $reglements = DB::table('creglements')
            ->where('journalcaisseid', $journalCaisse->journalcaisseid)
            ->select('modereglementid', DB::raw('SUM(montant) as total'))
            ->groupBy('modereglementid')
            ->get()
            ->keyBy('modereglementid');

        // Recalculer les totaux depuis les tickets
        $ticketsTotals = DB::table('ctickets')
            ->where('journalcaisseid', $journalCaisse->journalcaisseid)
            ->select(
                DB::raw('COUNT(*) as nbreticket'),
                DB::raw('SUM(totalttc) as recettebrut'),
                DB::raw('SUM(totalqte) as totalqtevente'),
                DB::raw('SUM(acompte) as ventereglee'),
                DB::raw('SUM(remise) as vtotalremise')
            )
            ->first();

        // Valeurs théoriques
        $totalEspece       = (float) ($reglements->get(1)->total ?? 0);
        $totalCheque       = (float) ($reglements->get(2)->total ?? 0);
        $totalTpe          = (float) ($reglements->get(4)->total ?? 0);
        $totalContrebon    = (float) ($reglements->get(5)->total ?? 0);
        $totalBonConv      = (float) ($reglements->get(8)->total ?? 0);
        $totalAvoir        = (float) ($reglements->get(9)->total ?? 0);
        $totalAutre        = (float) ($reglements->get(12)->total ?? 0);

        // Valeurs physiques (saisies par le caissier)
        $especePhys     = (float) ($request->totalespecephys ?? 0);
        $chequePhys     = (float) ($request->totalchequephys ?? 0);
        $tpePhys        = (float) ($request->totaltpephys ?? 0);
        $contrebonPhys  = (float) ($request->totalcontrebonphys ?? 0);
        $bonConvPhys    = (float) ($request->totalbonconventionphys ?? 0);
        $avoirPhys      = (float) ($request->totalregavoirphys ?? 0);
        $autrePhys      = (float) ($request->totalregautrephys ?? 0);

        // Calculs agrégés
        $fondCaisse      = (float) $journalCaisse->fondcaisse;
        $recettebrut     = (float) ($ticketsTotals->recettebrut ?? 0);
        $recettenet      = $recettebrut;
        $ventereglee     = (float) ($ticketsTotals->ventereglee ?? 0);
        $montanttheorique = $totalEspece + $fondCaisse;
        $recettephysique = $especePhys + $chequePhys + $tpePhys + $contrebonPhys + $bonConvPhys + $avoirPhys + $autrePhys;
        $montantcloture  = $especePhys;
        $ecart           = $recettephysique - $montanttheorique;
        $totalecart      = $especePhys - ($totalEspece + $fondCaisse);

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
                'totalbonconvention'    => $totalBonConv,
                'totalregavoir'         => $totalAvoir,
                'totalregautre'         => $totalAutre,
                'totalespecenet'        => $totalEspece,

                // Totaux physiques (comptés par le caissier)
                'totalespecephys'       => $especePhys,
                'totalchequephys'       => $chequePhys,
                'totaltpephys'          => $tpePhys,
                'totalcontrebonphys'    => $contrebonPhys,
                'totalbonconventionphys' => $bonConvPhys,
                'totalregavoirphys'     => $avoirPhys,
                'totalregautrephys'     => $autrePhys + $fondCaisse,

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

        return redirect()->route('vente.journee.cloture')
            ->with('success', 'La journée a été clôturée avec succès !');
    }
}
