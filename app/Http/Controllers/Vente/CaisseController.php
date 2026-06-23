<?php

namespace App\Http\Controllers\Vente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CaisseController extends Controller
{
    /**
     * Affiche l'interface principale de la caisse (POS).
     */
    public function index()
    {
        // Logique backend pour charger les données de la caisse (catégories, produits, etc.)
        // return view('vente.caisse.index');
        return response()->json(['message' => 'Backend Caisse prêt']);
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $user = auth()->user();
            $siteid = $user->siteid ?? 102;
            $caissierid = $user->userid;
            $employeeid = $request->input('vendeurid', $user->employeeid ?? 1);

            $journal = DB::table('journalcaisses')
                ->where('userid', $caissierid)
                ->where('isclosed', 0) // 0 = ouvert
                ->orderBy('journalcaisseid', 'desc')
                ->first();
            $journalcaisseid = $journal ? $journal->journalcaisseid : 1;
            $agencebid = $journal ? $journal->agencebid : 2;

            // Générer le numéro de ticket
            $lastTicket = DB::table('ctickets')->orderBy('cticketid', 'desc')->first();
            // Assuming format YYxxxxxx, simple increment:
            $nextNumero = $lastTicket && $lastTicket->cticketnumero ? (intval($lastTicket->cticketnumero) + 1) : intval(date('y') . '000001');

            $clientid = $request->input('clientid');
            if (!$clientid) {
                $client = DB::table('clients')->where('nom', 'PASSAGER')->first();
                $clientid = $client ? $client->clientid : 1;
            }

            $enAttente = $request->input('en_attente') ? 1 : 0;
            
            $lignes = $request->input('lignes', []);
            if (empty($lignes)) {
                return response()->json(['success' => false, 'message' => 'Le ticket est vide.']);
            }

            // Calculer les totaux
            $totalqte = 0;
            $totalttc = 0;
            $totalht = 0;
            $totaltva = 0;
            $remise_totale = 0;

            foreach ($lignes as $ligne) {
                $totalqte += floatval($ligne['qte']);
                $totalttc += floatval($ligne['total']);
                
                // Calcul approximatif HT (supposons TVA 19%)
                $tva_rate = 19;
                $ht = floatval($ligne['total']) / (1 + ($tva_rate / 100));
                $totalht += $ht;
                $totaltva += (floatval($ligne['total']) - $ht);
                $remise_totale += (floatval($ligne['prix']) * floatval($ligne['qte'])) - floatval($ligne['total']);
            }

            $now = now();

            // Insérer le ticket
            $cticketid = DB::table('ctickets')->insertGetId([
                'clientid' => $clientid,
                'siteid' => $siteid,
                'cticketnumero' => $nextNumero,
                'modepieceid' => 1,
                'etatblid' => 1,
                'transfere' => 0,
                'datecreation' => $now,
                'cticketdate' => $now,
                'totalbrutht' => $totalht + $remise_totale,
                'remise' => $remise_totale > 0 ? 1 : 0,
                'vremise' => $remise_totale,
                'totalnetht' => $totalht,
                'totaltva' => $totaltva,
                'totalttc' => $totalttc,
                'acompte' => floatval($request->input('acompte', 0)),
                'netapayer' => floatval($request->input('netapayer', $totalttc)),
                'totalqte' => $totalqte,
                'caissierid' => $caissierid,
                'employeeid' => $employeeid,
                'journalcaisseid' => $journalcaisseid,
                'retour' => 0,
                'totalavremise' => $totalttc + $remise_totale,
                'userid' => $caissierid,
                'vierge' => 0,
                'totalfidelite' => 0,
                'totalhtachat' => 0,
                'totalttcachat' => 0,
                'margeht' => 0,
                'margettc' => 0,
                'pmargeht' => 0,
                'pmargettc' => 0,
                'enattente' => $enAttente,
                'modestock' => 1,
                'totalqtettans' => $totalqte
            ], 'cticketid');

            // Insérer les lignes
            $ordre = 1;
            foreach ($lignes as $ligne) {
                // Find product ID from produit2id
                $produit2id = $ligne['produit2id'] ?? null;
                if (!$produit2id && isset($ligne['produitid'])) {
                    $p2 = DB::table('produit2s')->where('produitid', $ligne['produitid'])->first();
                    if ($p2) $produit2id = $p2->produit2id;
                }
                if (!$produit2id && isset($ligne['articleid'])) {
                    $p2 = DB::table('produit2s')->where('produitid', $ligne['articleid'])->first();
                    if ($p2) $produit2id = $p2->produit2id;
                }
                if (!$produit2id && isset($ligne['code'])) {
                    $p2 = DB::table('produit2s')
                        ->join('produits', 'produit2s.produitid', '=', 'produits.produitid')
                        ->where('produits.reference', $ligne['code'])
                        ->orWhere('produit2s.barcode2', $ligne['code'])
                        ->select('produit2s.produit2id')
                        ->first();
                    if ($p2) $produit2id = $p2->produit2id;
                }

                if (!$produit2id) {
                    return response()->json([
                        'success' => false,
                        'message' => "Erreur : La variante de l'article " . ($ligne['reference'] ?? '') . " est introuvable."
                    ]);
                }

                $produit2 = DB::table('produit2s')->where('produit2id', $produit2id)->first();
                $produitid = $produit2 ? $produit2->produitid : 1;

                $tva_rate = 19;
                $l_total = floatval($ligne['total'] ?? 0);
                $l_qte = floatval($ligne['qte'] ?? 1);
                if ($l_qte == 0) $l_qte = 1;
                $l_prixNet = floatval($ligne['prixNet'] ?? ($ligne['prix'] ?? 0));
                $l_remise = floatval($ligne['remise'] ?? 0);

                $ht = $l_total / (1 + ($tva_rate / 100));
                
                DB::table('detctickets')->insert([
                    'cticketid' => $cticketid,
                    'produitid' => $produitid,
                    'produit2id' => $produit2id,
                    'taxefamilleid' => 1,
                    'ht' => $ht / $l_qte,
                    'ttc' => $l_prixNet,
                    'qte' => $l_qte,
                    'totalht' => $ht,
                    'remise' => $l_remise,
                    'remise2' => 0,
                    'totalhtnet' => $ht,
                    'taxe1' => 0, 'vtaxe1' => 0,
                    'taxe2' => 0, 'vtaxe2' => 0,
                    'taxe3' => 0, 'vtaxe3' => 0,
                    'taxe4' => 0, 'vtaxe4' => 0,
                    'tva' => $tva_rate,
                    'vtva' => $l_total - $ht,
                    'totalttc' => $l_total,
                    'totalttcnet' => $l_total,
                    'qtetrans' => 0,
                    'siteid' => $siteid,
                    'date' => $now,
                    'largeur' => 0,
                    'longueur' => 0,
                    'surface' => 0,
                    'ordre' => $ordre++,
                    'retour' => 0,
                    'stocknegatif' => 0,
                    'modestock' => 1
                ]);
            }

            // Si ce n'est pas une mise en attente, enregistrer le règlement
            if (!$enAttente) {
                $reglements = $request->input('reglements', []);
                foreach ($reglements as $reglement) {
                    DB::table('creglements')->insert([
                        'clientid' => $clientid,
                        'siteid' => $siteid,
                        'date' => $now,
                        'datecreation' => $now,
                        'echeance' => $now,
                        'montant' => floatval($reglement['montant']),
                        'modereglementid' => $reglement['modereglementid'], // 1=Espèce, 4=CB...
                        'statusreglementid' => 1,
                        'documentid' => $cticketid, // Lié au ticket
                        'typeid' => 1, // 1 = Ticket de caisse
                        'numero' => $nextNumero,
                        'montantnet' => floatval($reglement['montant']),
                        'montantrs' => 0,
                        'tauxrs' => 0,
                        'ismultiple' => 0,
                        'retenuevalide' => 0,
                        'employeeid' => $employeeid,
                        'userid' => $caissierid,
                        'agencebid' => $agencebid,
                        'cours' => 1,
                        'nonaffecte' => 0,
                        'iscomplement' => 0,
                        'journalcaisseid' => $journalcaisseid,
                        'rendu' => floatval($request->input('rendu', 0)),
                        'deviseid' => 1,
                        'typechequecadeauid' => $reglement['typechequecadeauid'] ?? null
                    ]);
                }
            }

            // Loyalty Logic
            if (!$enAttente && $clientid != 1) { // 1 = PASSAGER
                $clientInfo = DB::table('clients')->where('clientid', $clientid)->first();
                if ($clientInfo && $clientInfo->fidelite == 1) {
                    $ventes = (int) $clientInfo->total_ventes_fidelite;
                    $ventes++;
                    
                    $updateData = ['total_ventes_fidelite' => $ventes];
                    if ($ventes == 1 || empty($clientInfo->date_premiere_vente)) {
                        $updateData['date_premiere_vente'] = $now;
                    }
                    
                    if ($ventes >= 4) {
                        $paires = 0;
                        foreach ($lignes as $ligne) {
                            $prodId = DB::table('produit2s')->where('produit2id', $ligne['produit2id'])->value('produitid');
                            $prod = DB::table('produits')->where('produitid', $prodId)->first();
                            if ($prod && $prod->familleid == 1) {
                                $paires += floatval($ligne['qte']);
                            }
                        }
                        
                        if ($paires > 0) {
                            $montantBon = $paires * 10;
                            DB::table('bons_achat')->insert([
                                'clientid' => $clientid,
                                'montant' => $montantBon,
                                'date_emission' => $now,
                                'date_expiration' => now()->addMonths(3),
                                'utilise' => false,
                                'ticketid_source' => $cticketid,
                                'created_at' => $now,
                                'updated_at' => $now
                            ]);
                            $updateData['soldefidelite'] = floatval($clientInfo->soldefidelite) + $montantBon;
                        }
                    }
                    
                    DB::table('clients')->where('clientid', $clientid)->update($updateData);
                }
            }

            DB::commit();
            return response()->json([
                'success' => true, 
                'message' => $enAttente ? 'Ticket mis en attente.' : 'Ticket validé avec succès.',
                'cticketid' => $cticketid,
                'cticketnumero' => $nextNumero
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur Caisse Store: ' . $e->getMessage() . ' - Ligne: ' . $e->getLine());
            return response()->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * Récupérer la liste des tickets en attente
     */
    public function getEnAttente()
    {
        $siteid = auth()->user()->siteid ?? 102;
        $tickets = DB::table('ctickets')
            ->leftJoin('clients', 'ctickets.clientid', '=', 'clients.clientid')
            ->where('ctickets.enattente', 1)
            ->where('ctickets.siteid', $siteid)
            ->select('ctickets.cticketid', 'ctickets.cticketnumero', 'ctickets.totalttc', 'ctickets.datecreation', 'clients.nom as client_nom')
            ->orderBy('ctickets.cticketid', 'desc')
            ->get();
            
        return response()->json(['success' => true, 'tickets' => $tickets]);
    }

    /**
     * Data for Journal Vente Modal in POS
     */
    public function journalVenteData()
    {
        $tickets = \Illuminate\Support\Facades\DB::table('ctickets')
            ->leftJoin('clients', 'ctickets.clientid', '=', 'clients.clientid')
            ->leftJoin('users as caissiers', 'ctickets.caissierid', '=', 'caissiers.userid')
            ->leftJoin('employees as vendeurs', 'ctickets.employeeid', '=', 'vendeurs.employeeid')
            ->leftJoin('statutdocuments', 'ctickets.statutdocumentid', '=', 'statutdocuments.statutdocumentid')
            ->select(
                'ctickets.cticketid',
                'ctickets.cticketnumero',
                'ctickets.datecreation',
                'ctickets.totalttc',
                'ctickets.acompte',
                'ctickets.netapayer',
                'clients.nom as client_nom',
                'clients.tel as client_tel',
                'clients.code as client_code',
                'caissiers.login as caissier_nom',
                'vendeurs.nom as vendeur_nom',
                'vendeurs.code as vendeur_code',
                'statutdocuments.libelle as statut_libelle',
                'statutdocuments.statutdocumentid'
            )
            ->where('ctickets.enattente', false)
            ->orderBy('ctickets.cticketid', 'desc')
            ->limit(1000)
            ->get();

        $clients = \Illuminate\Support\Facades\DB::table('clients')->select('clientid', 'nom')->orderBy('nom')->get();
        $vendeurs = \Illuminate\Support\Facades\DB::table('employees')->select('employeeid', 'nom')->where('isvendeur', true)->get();

        return response()->json([
            'tickets' => $tickets,
            'clients' => $clients,
            'vendeurs' => $vendeurs
        ]);
    }

    public function ticketDetails($numero)
    {
        $ticket = DB::table('ctickets')->where('cticketnumero', $numero)->first();
        if (!$ticket) {
            return response()->json(['success' => false, 'message' => 'Ticket introuvable.']);
        }
        
        $lignes = DB::table('detctickets')
            ->join('produits', 'detctickets.produitid', '=', 'produits.produitid')
            ->join('produit2s', 'detctickets.produit2id', '=', 'produit2s.produit2id')
            ->leftJoin('couleurs', 'produit2s.couleurid', '=', 'couleurs.couleurid')
            ->leftJoin('tailles', 'produit2s.tailleid', '=', 'tailles.tailleid')
            ->where('detctickets.cticketid', $ticket->cticketid)
            ->select(
                'detctickets.*', 
                'produits.reference as article_ref', 
                'produits.produitlibelle as article_designation',
                'detctickets.ttc as prix',
                'couleurs.couleurlibelle as couleur',
                'tailles.taillelibelle as taille'
            )
            ->get();

        return response()->json([
            'success' => true,
            'ticket' => $ticket,
            'lines' => $lignes
        ]);
    }

    public function getMouvements(Request $request)
    {
        $du = $request->query('du', date('Y-m-d'));
        $au = $request->query('au', date('Y-m-d'));
        $clientid = $request->query('clientid');

        $query = DB::table('detctickets')
            ->join('ctickets', 'detctickets.cticketid', '=', 'ctickets.cticketid')
            ->join('produits', 'detctickets.produitid', '=', 'produits.produitid')
            ->join('produit2s', 'detctickets.produit2id', '=', 'produit2s.produit2id')
            ->leftJoin('couleurs', 'produit2s.couleurid', '=', 'couleurs.couleurid')
            ->leftJoin('tailles', 'produit2s.tailleid', '=', 'tailles.tailleid')
            ->whereBetween(DB::raw('DATE(ctickets.datecreation)'), [$du, $au]);

        if ($clientid) {
            $query->where('ctickets.clientid', $clientid);
        }

        $mouvements = $query->select(
            'ctickets.cticketnumero',
            'ctickets.datecreation as date',
            'produits.reference',
            'produits.produitlibelle as designation',
            'tailles.taillelibelle as taille',
            'couleurs.couleurlibelle as couleur',
            'detctickets.qte',
            'detctickets.ttc as prix',
            'detctickets.remise',
            'detctickets.produitid as articleid',
            'detctickets.produit2id'
        )->get();

        return response()->json(['success' => true, 'mouvements' => $mouvements]);
    }

    /**
     * Charger un ticket en attente dans la caisse
     */
    public function reprise($id)
    {
        $ticket = DB::table('ctickets')->where('cticketid', $id)->first();
        if (!$ticket) {
            return response()->json(['success' => false, 'message' => 'Ticket introuvable.']);
        }
        
        $lignes = DB::table('detctickets')
            ->join('produits', 'detctickets.produitid', '=', 'produits.produitid')
            ->join('produit2s', 'detctickets.produit2id', '=', 'produit2s.produit2id')
            ->leftJoin('couleurs', 'produit2s.couleurid', '=', 'couleurs.couleurid')
            ->leftJoin('tailles', 'produit2s.tailleid', '=', 'tailles.tailleid')
            ->where('detctickets.cticketid', $id)
            ->select(
                'produits.produitcode as code',
                'produits.reference as ref',
                'produits.produitlibelle as designation',
                'couleurs.couleurlibelle as couleur',
                'tailles.taillelibelle as taille',
                'detctickets.produit2id',
                'detctickets.qte',
                'detctickets.ttc as prixNet',
                'detctickets.remise',
                'detctickets.totalttc as total'
            )
            ->get();
            
        // Convert to match POS frontend format
        $formattedLignes = [];
        foreach ($lignes as $ligne) {
            // Reconstruct original prix before remise
            $prix = $ligne->prixNet / (1 - ($ligne->remise / 100));
            if ($ligne->remise == 100) $prix = $ligne->prixNet; // Prevent division by zero
            
            $formattedLignes[] = [
                'code' => $ligne->code,
                'ref' => $ligne->ref,
                'designation' => $ligne->designation,
                'couleur' => $ligne->couleur,
                'taille' => $ligne->taille,
                'qte' => floatval($ligne->qte),
                'prix' => round(floatval($prix), 3),
                'remise' => floatval($ligne->remise),
                'prixNet' => floatval($ligne->prixNet),
                'total' => floatval($ligne->total),
                'produit2id' => $ligne->produit2id
            ];
        }
        
        // Supprimer le ticket en attente original (car on le reprend dans la caisse)
        // Optionnellement, on pourrait juste le mettre à jour à la fin, mais c'est plus simple de le supprimer
        DB::table('detctickets')->where('cticketid', $id)->delete();
        DB::table('ctickets')->where('cticketid', $id)->delete();
        
        return response()->json([
            'success' => true,
            'clientid' => $ticket->clientid,
            'lignes' => $formattedLignes
        ]);
    }

    /**
     * Récupérer les tickets avec un reste à payer (netapayer > 0)
     */
    public function getTicketsWithReste(Request $request)
    {
        $du = $request->query('du', date('Y-m-d'));
        $au = $request->query('au', date('Y-m-d'));
        $clientid = $request->query('clientid');
        $numero = $request->query('numero');

        $query = DB::table('ctickets')
            ->leftJoin('clients', 'ctickets.clientid', '=', 'clients.clientid')
            ->leftJoin('users as caissiers', 'ctickets.caissierid', '=', 'caissiers.userid')
            ->leftJoin('employees as vendeurs', 'ctickets.employeeid', '=', 'vendeurs.employeeid')
            ->where('ctickets.netapayer', '>', 0)
            ->where('ctickets.enattente', 0)
            ->whereBetween(DB::raw('DATE(ctickets.datecreation)'), [$du, $au]);

        if ($clientid) {
            $query->where('ctickets.clientid', $clientid);
        }
        if ($numero) {
            $query->where('ctickets.cticketnumero', 'like', '%' . $numero . '%');
        }

        $tickets = $query->select(
            'ctickets.cticketid',
            'ctickets.cticketnumero',
            'ctickets.datecreation',
            'ctickets.totalttc',
            'ctickets.totalqte',
            'ctickets.acompte',
            'ctickets.netapayer',
            'clients.nom as client_nom',
            'clients.clientid',
            'caissiers.login as caissier_nom',
            'vendeurs.nom as vendeur_nom'
        )
        ->orderBy('ctickets.cticketid', 'desc')
        ->limit(500)
        ->get();

        // Récupérer la liste des clients pour le filtre
        $clients = DB::table('clients')->select('clientid', 'nom')->orderBy('nom')->get();

        return response()->json([
            'success' => true,
            'tickets' => $tickets,
            'clients' => $clients
        ]);
    }

    /**
     * Enregistrer un complément d'acompte
     */
    public function storeComplementAcompte(Request $request)
    {
        try {
            DB::beginTransaction();

            $user = auth()->user();
            $siteid = $user->siteid ?? 102;
            $caissierid = $user->userid;
            $employeeid = $user->employeeid ?? 1;

            $cticketid = $request->input('cticketid');
            $reglements = $request->input('reglements', []);
            if (empty($reglements)) {
                // Fallback for single payment
                $reglements = [
                    [
                        'modereglementid' => intval($request->input('modereglementid', 1)),
                        'montant' => floatval($request->input('montant'))
                    ]
                ];
            }

            $totalAdded = 0;
            foreach ($reglements as $reg) {
                $montant = floatval($reg['montant'] ?? 0);
                $modereglementid = intval($reg['modereglementid'] ?? 1);

                if ($montant <= 0) continue;
                if ($montant > $ticket->netapayer) {
                    $montant = $ticket->netapayer; // Plafonner au reste à payer
                }

                // Insérer le règlement complement
                DB::table('creglements')->insert([
                    'clientid' => $ticket->clientid,
                    'siteid' => $siteid,
                    'date' => $now,
                    'datecreation' => $now,
                    'echeance' => $now,
                    'montant' => $montant,
                    'modereglementid' => $modereglementid,
                    'statusreglementid' => 1,
                    'documentid' => $cticketid,
                    'typeid' => 1,
                    'numero' => $ticket->cticketnumero,
                    'montantnet' => $montant,
                    'montantrs' => 0,
                    'tauxrs' => 0,
                    'ismultiple' => 0,
                    'retenuevalide' => 0,
                    'employeeid' => $employeeid,
                'userid' => $caissierid,
                'agencebid' => $agencebid,
                    'userid' => $caissierid,
                    'agencebid' => $agencebid,
                    'cours' => 1,
                    'nonaffecte' => 0,
                    'iscomplement' => 1,
                    'journalcaisseid' => $journalcaisseid,
                    'rendu' => 0,
                    'deviseid' => 1
                ]);

                $totalAdded += $montant;
                // Update netapayer for next iteration if multiple
                $ticket->netapayer -= $montant;
            }

            if ($totalAdded <= 0) {
                return response()->json(['success' => false, 'message' => 'Veuillez saisir un montant valide.']);
            }

            // Mettre à jour le ticket avec le total des acomptes ajoutés
            DB::table('ctickets')
                ->where('cticketid', $cticketid)
                ->update([
                    'acompte' => DB::raw("acompte + $totalAdded"),
                    'netapayer' => DB::raw("netapayer - $totalAdded")
                ]);

            // Mettre à jour le journal caisse
            DB::table('journalcaisses')->where('journalcaisseid', $journalcaisseid)->increment('complementacompte', $totalAdded);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Complément acompte enregistré avec succès.',
                'nouveau_reste' => $ticket->netapayer - $montant
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur Complement Acompte: ' . $e->getMessage() . ' - Ligne: ' . $e->getLine());
            return response()->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }
}
