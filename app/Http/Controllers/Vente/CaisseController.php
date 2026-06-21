<?php

namespace App\Http\Controllers\Vente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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

            // Fetch journal de caisse ouvert
            $journal = DB::table('journalcaisses')
                ->where('caissierid', $caissierid)
                ->where('etat', 0) // Supposons 0 = ouvert
                ->orderBy('journalcaisseid', 'desc')
                ->first();
            $journalcaisseid = $journal ? $journal->journalcaisseid : 1;

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
                'etatblid' => 0,
                'transfere' => 0,
                'datecreation' => $now,
                'cticketdate' => $now,
                'totalbrutht' => $totalht + $remise_totale,
                'remise' => $remise_totale > 0 ? 1 : 0,
                'vremise' => $remise_totale,
                'totalnetht' => $totalht,
                'totaltva' => $totaltva,
                'totalttc' => $totalttc,
                'acompte' => 0,
                'netapayer' => $totalttc,
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
                $produit2 = DB::table('produit2s')->where('produit2id', $ligne['produit2id'])->first();
                $produitid = $produit2 ? $produit2->produitid : 1;

                $tva_rate = 19;
                $ht = floatval($ligne['total']) / (1 + ($tva_rate / 100));
                
                DB::table('detctickets')->insert([
                    'cticketid' => $cticketid,
                    'produitid' => $produitid,
                    'produit2id' => $ligne['produit2id'],
                    'taxefamilleid' => 1,
                    'ht' => $ht / floatval($ligne['qte']),
                    'ttc' => floatval($ligne['prixNet']),
                    'qte' => floatval($ligne['qte']),
                    'totalht' => $ht,
                    'remise' => floatval($ligne['remise']),
                    'remise2' => 0,
                    'totalhtnet' => $ht,
                    'taxe1' => 0, 'vtaxe1' => 0,
                    'taxe2' => 0, 'vtaxe2' => 0,
                    'taxe3' => 0, 'vtaxe3' => 0,
                    'taxe4' => 0, 'vtaxe4' => 0,
                    'tva' => $tva_rate,
                    'vtva' => floatval($ligne['total']) - $ht,
                    'totalttc' => floatval($ligne['total']),
                    'totalttcnet' => floatval($ligne['total']),
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
                        'cours' => 1,
                        'nonaffecte' => 0,
                        'iscomplement' => 0,
                        'journalcaisseid' => $journalcaisseid,
                        'rendu' => floatval($request->input('rendu', 0)),
                        'deviseid' => 1
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
}
