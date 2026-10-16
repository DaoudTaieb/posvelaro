<?php

namespace App\Http\Controllers\Vente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Support\Droits;
use App\Exceptions\VenteRefusee;
use App\Http\Requests\StoreTicketRequest;
use App\Services\FideliteService;
use App\Services\JourneeCaisseService;
use App\Services\NumerotationService;
use App\Services\ReglementService;
use App\Services\SmsService;
use App\Services\TicketService;
use App\Support\SiteCourant;

class CaisseController extends Controller
{
    private const MODE_AVOIR = 9;
    private const MODE_BON_ACHAT = 10;
    private const FAMILLE_DEPENSE_RETOUR_ARGENT = 10;

    public function __construct(
        private readonly FideliteService $fidelite,
        private readonly SmsService $sms,
        private readonly JourneeCaisseService $journees,
        private readonly ReglementService $reglementsService,
        private readonly TicketService $tickets,
        private readonly NumerotationService $numerotation,
    ) {
    }

    /**
     * Validation d'un ticket : vente, retour (lignes négatives) ou mise en attente.
     * Orchestration : droits, journée, lignes (TicketService), numéro (NumerotationService),
     * règlements (ReglementService), fidélité (FideliteService), SMS proposé (SmsService).
     */
    public function store(StoreTicketRequest $request)
    {
        if ($refus = $this->verifierDroitsTicket($request)) {
            return response()->json(['success' => false, 'message' => $refus]);
        }

        try {
            DB::beginTransaction();

            $user = auth()->user();
            $siteid = SiteCourant::id();
            $caissierid = $user->userid;
            $employeeid = $request->input('vendeurid') ?: $user->employeeid;
            if (!$employeeid) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Veuillez sélectionner un vendeur (aucun employé n\'est associé à votre compte).']);
            }

            $journal = $this->journees->journeeOuverte($user);
            if (!$journal) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Aucune journée ouverte. Veuillez ouvrir une journée avant de valider le ticket.']);
            }

            // Ticket repris depuis la liste « en attente » : il n'est supprimé qu'ici, dans la même
            // transaction que le nouveau ticket (aucune perte si la caisse est fermée entre-temps).
            if ($request->filled('reprise_cticketid')) {
                $enAttenteOrigine = DB::table('ctickets')
                    ->where('cticketid', (int) $request->input('reprise_cticketid'))
                    ->where('siteid', $siteid)
                    ->where('enattente', true)
                    ->lockForUpdate()
                    ->first();
                if (!$enAttenteOrigine) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'Ce ticket en attente a déjà été repris et validé sur un autre poste. Annulez le ticket en cours pour éviter une double vente.']);
                }
                DB::table('detctickets')->where('cticketid', $enAttenteOrigine->cticketid)->delete();
                DB::table('ctickets')->where('cticketid', $enAttenteOrigine->cticketid)->delete();
            }

            $journalcaisseid = $journal->journalcaisseid;
            $agencebid = $journal->agencebid;

            $passagerId = (int) config('pos.client_passager_id');
            $clientid = $request->input('clientid');
            if (!$clientid) {
                $client = DB::table('clients')->where('nom', 'PASSAGER')->first();
                $clientid = $client ? $client->clientid : $passagerId;
            }

            $enAttente = $request->input('en_attente') ? 1 : 0;
            $lignes = $request->input('lignes', []);

            // Lignes : variante, TVA réelle de l'article, montants arrondis au millime, cohérence quantité × prix net
            $detLignes = $this->tickets->preparerLignes($lignes);
            $totaux = $this->tickets->totaux($detLignes);
            $totalqte = $totaux['totalqte'];
            $totalttc = $totaux['totalttc'];
            $totalht = $totaux['totalnetht'];
            $totaltva = $totaux['totaltva'];
            $totalbrutht = $totaux['totalbrutht'];
            $remise_totale = $totaux['vremise'];

            $remboursement = !$enAttente && $totalttc < -0.0005;
            $reglements = ($enAttente || $remboursement) ? [] : $request->input('reglements', []);
            foreach ($reglements as $reglement) {
                if (floatval($reglement['montant'] ?? 0) < 0) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'Un règlement ne peut pas être négatif.']);
                }
            }
            $totalRegle = $remboursement
                ? $totalttc
                : round(array_sum(array_map(fn ($r) => floatval($r['montant'] ?? 0), $reglements)), 3);
            if (!$remboursement && $totalRegle > $totalttc + 0.005) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Le montant réglé dépasse le total du ticket.']);
            }

            // Numéro de ticket (série du logiciel de bureau, sous verrou)
            $nextNumero = $this->numerotation->prochainNumeroTicket();

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
                'totalbrutht' => $totalbrutht,
                'remise' => $remise_totale > 0 ? 1 : 0,
                'vremise' => $remise_totale,
                'totalnetht' => $totalht,
                'totaltva' => $totaltva,
                'totalttc' => $totalttc,
                'acompte' => $totalRegle,
                'netapayer' => $totalttc - $totalRegle,
                'totalqte' => $totalqte,
                'caissierid' => $caissierid,
                'employeeid' => $employeeid,
                'journalcaisseid' => $journalcaisseid,
                'caisseid' => $journal->caisseid,
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
                'totalqtettans' => $totalqte,
                'description' => $enAttente ? json_encode(['enattente_lignes' => $this->lignesPourReprise($lignes, $detLignes)]) : null,
            ], 'cticketid');

            // Insérer les lignes (pas pour un ticket en attente : le stock ne doit pas bouger avant la validation)
            $ordre = 1;
            foreach ($enAttente ? [] : $detLignes as $det) {
                DB::table('detctickets')->insert(array_merge($det, [
                    'cticketid' => $cticketid,
                    'remise2' => 0,
                    'taxe1' => 0, 'vtaxe1' => 0,
                    'taxe2' => 0, 'vtaxe2' => 0,
                    'taxe3' => 0, 'vtaxe3' => 0,
                    'taxe4' => 0, 'vtaxe4' => 0,
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
                ]));
            }

            // Si ce n'est pas une mise en attente, enregistrer le règlement
            foreach ($reglements as $reglement) {
                $montant = round(floatval($reglement['montant']), 3);
                if ($montant <= 0) continue;
                $modereglementid = intval($reglement['modereglementid']);

                // Bon d'achat fidélité : doit exister, être valide, non utilisé et appartenir au client
                if ($modereglementid == self::MODE_BON_ACHAT) {
                    $erreur = $this->fidelite->consommerBon($reglement['numero'] ?? null, $montant, $clientid != $passagerId ? (int) $clientid : null, (int) $cticketid, $now);
                    if ($erreur) {
                        DB::rollBack();
                        return response()->json(['success' => false, 'message' => $erreur]);
                    }
                }

                $avoir = null;
                if ($modereglementid == self::MODE_AVOIR && !empty($reglement['cavoirid'])) {
                    $avoir = DB::table('cavoirs')->where('cavoirid', $reglement['cavoirid'])->lockForUpdate()->first();
                    if (!$avoir || $avoir->cloture || floatval($avoir->netapayer) + 0.0005 < $montant) {
                        DB::rollBack();
                        return response()->json(['success' => false, 'message' => "L'avoir n'a pas un solde suffisant ou est déjà consommé."]);
                    }
                }

                // Format du logiciel de bureau : creglements (documentid = 0) + creglementdets (documentid = ticket).
                // Le trigger creglement_calc recalcule ctickets.acompte / netapayer.
                $creglementid = $this->reglementsService->enregistrerPourTicket((int) $cticketid, [
                    'clientid' => $clientid,
                    'siteid' => $siteid,
                    'date' => $now,
                    'montant' => $montant,
                    'modereglementid' => $modereglementid,
                    'employeeid' => $employeeid,
                    'userid' => $caissierid,
                    'agencebid' => $agencebid,
                    'journalcaisseid' => $journalcaisseid,
                    'rendu' => floatval($request->input('rendu', 0)),
                    'typechequecadeauid' => $reglement['typechequecadeauid'] ?? null,
                ] + $this->detailsReglement($reglement, $nextNumero, $now));

                // Consommer l'avoir (verrouillé plus haut) : on diminue son reste et on le clôture s'il est épuisé.
                if ($avoir) {
                    $acompteAvoir = round(floatval($avoir->acompte) + $montant, 3);
                    $resteAvoir = round(floatval($avoir->totalttc) - $acompteAvoir, 3);
                    DB::table('cavoirs')->where('cavoirid', $avoir->cavoirid)->update([
                        'acompte' => $acompteAvoir,
                        'netapayer' => $resteAvoir,
                        'cloture' => $resteAvoir <= 0.0005,
                    ]);
                    DB::table('creglements')->where('creglementid', $creglementid)->update([
                        'documentavid' => $avoir->cavoirid,
                    ]);
                }
            }

            // Remboursement en espèces : dépense « Retour Argent » liée au ticket.
            // Le trigger depense_calc met à jour ctickets.acompte / netapayer (ticket soldé).
            if ($remboursement) {
                $montantRendu = round(-$totalttc, 3);
                DB::table('depenses')->insert([
                    'siteid' => $siteid,
                    'libelle' => 'Retour argent ticket N° ' . $nextNumero,
                    'date' => $now,
                    'datecreation' => $now,
                    'echeance' => $now,
                    'montant' => $montantRendu,
                    'modereglementid' => 1, // Espèce
                    'statusreglementid' => 1,
                    'agencebid' => $agencebid,
                    'familledepenseid' => self::FAMILLE_DEPENSE_RETOUR_ARGENT,
                    'numero' => (string) $nextNumero,
                    'caisseid' => $journal->caisseid,
                    'clientid' => $clientid,
                    'cticketid' => $cticketid,
                    'journalcaisseid' => $journalcaisseid,
                    'employeeid' => $employeeid,
                ]);
                DB::table('journalcaisses')->where('journalcaisseid', $journalcaisseid)->increment('montantdepense', $montantRendu);
            }

            // Fidélité : paliers, bon d'achat à partir de la Nième vente, annulation des bons sur retour
            $isFirstSale = false;
            $clientTel = null;
            $clientNom = null;
            $smsMessage = null;
            $bonGagne = null;
            $messagesFidelite = [];
            if (!$enAttente) {
                // Un retour annule (au prorata) les bons issus du ticket d'origine
                $messagesFidelite = $this->fidelite->annulerPourRetour($detLignes, $now);

                if ($clientid != $passagerId) {
                    $clientInfo = DB::table('clients')->where('clientid', $clientid)->first();
                    if ($clientInfo) {
                        $clientTel = $clientInfo->tel;
                        $clientNom = trim($clientInfo->nom . ' ' . ($clientInfo->prenom ?? ''));

                        // Toute première vente du client (tous cycles confondus), pour le SMS de bienvenue
                        $isFirstSale = DB::table('ctickets')
                            ->where('clientid', $clientid)
                            ->where('enattente', false)
                            ->where('totalttc', '>', 0)
                            ->where('cticketid', '!=', $cticketid)
                            ->doesntExist();

                        if ($totalttc > 0.0005) {
                            $resFid = $this->fidelite->appliquerVente($clientInfo, $detLignes, (int) $cticketid, $now);
                            if ($resFid) {
                                $bonGagne = $resFid['bon'];
                                if ($resFid['expiree']) {
                                    $messagesFidelite[] = 'Carte fidélité expirée (moins de ' . $this->fidelite->parametres()['ventes_par_an'] . ' ventes sur l\'année) : nouveau cycle démarré.';
                                }
                                if ($bonGagne) {
                                    $messagesFidelite[] = sprintf("Bon d'achat %s de %s DT gagné, valable jusqu'au %s.", $bonGagne->numero, number_format((float) $bonGagne->montant, 3, '.', ''), \Carbon\Carbon::parse($bonGagne->date_expiration)->format('d/m/Y'));
                                }
                            }
                        }

                        if (!empty($clientTel) && $totalttc > 0.0005) {
                            $smsMessage = $this->sms->messageApresVente($clientNom, $nextNumero, (float) $totalttc, $isFirstSale, $bonGagne);
                        }
                    }
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => $enAttente
                    ? 'Ticket mis en attente.'
                    : ($remboursement
                        ? 'Retour enregistré : ' . number_format(-$totalttc, 3, '.', ' ') . ' DT à rendre au client.'
                        : 'Ticket validé avec succès.'),
                'cticketid' => $cticketid,
                'cticketnumero' => $nextNumero,
                'clientid' => $clientid,
                'client_tel' => $clientTel,
                'client_nom' => $clientNom,
                'totalttc' => $totalttc,
                'is_first_sale' => $isFirstSale,
                'sms_message' => $smsMessage,
                'sms_disponible' => $this->sms->estConfigure(),
                'bon_achat' => $bonGagne ? [
                    'numero' => $bonGagne->numero,
                    'montant' => (float) $bonGagne->montant,
                    'date_expiration' => $bonGagne->date_expiration,
                ] : null,
                'fidelite_messages' => $messagesFidelite,
                'print_url' => $enAttente ? null : route('vente.tickets.show', $cticketid)
            ]);

        } catch (VenteRefusee $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur Caisse Store: ' . $e->getMessage() . ' - Ligne: ' . $e->getLine());
            return response()->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * Droits du rôle sur le contenu du ticket. Retourne le message de refus, ou null.
     */
    private function verifierDroitsTicket(Request $request): ?string
    {
        $lignes = $request->input('lignes', []);
        $enAttente = (bool) $request->input('en_attente');
        $reglements = $enAttente ? [] : $request->input('reglements', []);

        if ($enAttente && Droits::bloque('tick.attente')) {
            return Droits::message('tick.attente');
        }
        foreach ($lignes as $ligne) {
            if (floatval($ligne['qte'] ?? 0) < 0 && Droits::bloque('retour.art')) {
                return Droits::message('retour.art');
            }
        }

        // Retours : chaque ligne négative liée à une vente ne peut pas dépasser la quantité restant à retourner
        $retoursParLigne = [];
        foreach ($lignes as $ligne) {
            if (floatval($ligne['qte'] ?? 0) < 0 && !empty($ligne['detventeid'])) {
                $retoursParLigne[$ligne['detventeid']] = ($retoursParLigne[$ligne['detventeid']] ?? 0) - floatval($ligne['qte']);
            }
        }
        foreach ($retoursParLigne as $detventeid => $qteRetour) {
            $origine = DB::table('detctickets')
                ->join('ctickets', 'ctickets.cticketid', '=', 'detctickets.cticketid')
                ->where('detctickets.detcticketid', $detventeid)
                ->where('ctickets.siteid', \App\Support\SiteCourant::id())
                ->where('detctickets.qte', '>', 0)
                ->first(['detctickets.qte']);
            if (!$origine) {
                return 'Retour impossible : la ligne du ticket d\'origine est introuvable.';
            }
            $dejaRetourne = (float) DB::table('detctickets')->where('detventeid', $detventeid)->where('qte', '<', 0)->sum(DB::raw('-qte'));
            if ($qteRetour > floatval($origine->qte) - $dejaRetourne + 0.0005) {
                return 'Retour impossible : quantité retournée supérieure à la quantité restante (' . (floatval($origine->qte) - $dejaRetourne) . ').';
            }
        }

        // Vente sans stock suffisant sur le site (droit « autoriser stock négatif »)
        if (Droits::bloque('autorise.stock.négatif')) {
            $siteid = \App\Support\SiteCourant::id();
            $quantites = [];
            foreach ($lignes as $ligne) {
                if (!empty($ligne['produit2id']) && floatval($ligne['qte'] ?? 0) > 0) {
                    $quantites[$ligne['produit2id']] = ($quantites[$ligne['produit2id']] ?? 0) + floatval($ligne['qte']);
                }
            }
            foreach ($quantites as $produit2id => $qte) {
                $stock = (float) DB::table('stock2s')->where('produit2id', $produit2id)->where('siteid', $siteid)->value('qtestock');
                if ($qte > $stock + 0.0005) {
                    return Droits::message('autorise.stock.négatif');
                }
            }
        }

        // Remises et modification du prix de vente
        $bloqueRemise = Droits::bloque('rem.art%');
        $bloquePrix = Droits::bloque('mod.puttc');
        if ($bloqueRemise || $bloquePrix) {
            $clientid = $request->input('clientid');
            $client = $clientid ? DB::table('clients')->where('clientid', $clientid)->first() : null;
            $tauxFidelite = $client ? app(\App\Http\Controllers\PosController::class)->getLoyaltyTier($client) : 0;

            foreach ($lignes as $ligne) {
                $produit = !empty($ligne['produit2id'])
                    ? DB::table('produit2s')
                        ->join('produits', 'produits.produitid', '=', 'produit2s.produitid')
                        ->leftJoin('sousfamilles', 'sousfamilles.sousfamilleid', '=', 'produits.sousfamilleid')
                        ->where('produit2s.produit2id', $ligne['produit2id'])
                        ->first(['produits.ttc_vente', 'sousfamilles.is_loyalty_enabled'])
                    : null;
                if (!$produit) {
                    continue;
                }

                if ($bloquePrix && abs(floatval($ligne['prix'] ?? 0) - floatval($produit->ttc_vente)) > 0.0005) {
                    return Droits::message('mod.puttc');
                }

                $remise = floatval($ligne['remise'] ?? 0);
                $remiseFidelite = $produit->is_loyalty_enabled && abs($remise - $tauxFidelite) < 0.0005;
                if ($bloqueRemise && $remise > 0.0005 && !$remiseFidelite) {
                    return Droits::message('rem.art%');
                }
            }
        }

        $modesDroits = [2 => 'reg.cheque', 5 => 'reg.tick.cadeau', 6 => 'reg.tick.resto', 8 => 'reg.bon.conv', 9 => 'reg.avoir', 10 => 'reg.avoir'];
        foreach ($reglements as $reg) {
            $droit = $modesDroits[intval($reg['modereglementid'] ?? 0)] ?? null;
            if ($droit && Droits::bloque($droit)) {
                return Droits::message($droit);
            }
        }

        if (!$enAttente) {
            $total = round(array_sum(array_map(fn ($l) => floatval($l['total'] ?? 0), $lignes)), 3);
            if ($total < -0.0005 && Droits::bloque('retour.argent')) {
                return Droits::message('retour.argent');
            }
            $regle = round(array_sum(array_map(fn ($r) => floatval($r['montant'] ?? 0), $reglements)), 3);
            if ($total > 0 && $regle + 0.005 < $total) {
                $droit = $regle <= 0 ? 'regl.credit' : 'acompte.clt';
                if (Droits::bloque($droit)) {
                    return Droits::message($droit);
                }
            }
        }

        return null;
    }

    /**
     * Lignes d'un ticket en attente, au format attendu par la caisse lors de la reprise
     */
    private function lignesPourReprise(array $lignes, array $detLignes): array
    {
        $result = [];
        foreach (array_values($lignes) as $i => $ligne) {
            $det = $detLignes[$i] ?? [];
            $result[] = [
                'code' => $ligne['code'] ?? ($ligne['reference'] ?? ''),
                'ref' => $ligne['ref'] ?? ($ligne['reference'] ?? ''),
                'designation' => $ligne['designation'] ?? '',
                'couleur' => $ligne['couleur'] ?? '',
                'taille' => $ligne['taille'] ?? '',
                'qte' => floatval($det['qte'] ?? ($ligne['qte'] ?? 1)),
                'prix' => floatval($det['ttc'] ?? ($ligne['prix'] ?? 0)),
                'remise' => floatval($det['remise'] ?? ($ligne['remise'] ?? 0)),
                'prixNet' => floatval($det['puttcnet'] ?? ($ligne['prixNet'] ?? 0)),
                'total' => floatval($det['totalttcnet'] ?? ($ligne['total'] ?? 0)),
                'produit2id' => $det['produit2id'] ?? ($ligne['produit2id'] ?? null),
                'produitid' => $det['produitid'] ?? ($ligne['produitid'] ?? null),
            ];
        }
        return $result;
    }

    /**
     * Numéro, banque, échéance et titulaire d'un règlement, rangés comme le logiciel de bureau :
     * numero = n° du chèque / bon (sinon n° du ticket), agencereglement = banque,
     * echeance = date du chèque (sinon aujourd'hui), raisonreglement = titulaire.
     */
    private function detailsReglement(array $reglement, $numeroTicket, $now): array
    {
        $numero = trim((string) ($reglement['numero'] ?? ''));
        $banque = trim((string) ($reglement['banque'] ?? ''));
        $titulaire = trim((string) ($reglement['proprietaire'] ?? ''));

        $echeance = $now;
        if (!empty($reglement['date'])) {
            try {
                $echeance = \Carbon\Carbon::parse($reglement['date']);
            } catch (\Exception $e) {
                $echeance = $now;
            }
        }

        return [
            'numero' => $numero !== '' ? mb_substr($numero, 0, 50) : (string) $numeroTicket,
            'agencereglement' => $banque !== '' ? mb_substr($banque, 0, 100) : null,
            'raisonreglement' => $titulaire !== '' ? mb_substr($titulaire, 0, 100) : null,
            'echeance' => $echeance,
        ];
    }

    /**
     * Récupérer la liste des tickets en attente
     */
    public function getEnAttente()
    {
        $siteid = \App\Support\SiteCourant::id();
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
        $siteid = \App\Support\SiteCourant::id();
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
                'ctickets.clientid',
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
            ->where('ctickets.siteid', $siteid)
            ->when(Droits::bloque('cons.allticket'), fn ($q) => $q->where('ctickets.caissierid', auth()->user()->userid))
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

    public function ticketDetails(Request $request, $id)
    {
        // Par défaut recherche par identifiant (les numéros de ticket ne sont pas garantis uniques) ;
        // ?par=numero pour un numéro saisi par le caissier (retour) : on prend le ticket le plus récent.
        $ticket = null;
        if (ctype_digit((string) $id)) {
            $query = DB::table('ctickets')
                ->where('siteid', \App\Support\SiteCourant::id())
                ->where('enattente', false);
            $ticket = $request->query('par') === 'numero'
                ? $query->where('cticketnumero', $id)->orderByDesc('cticketid')->first()
                : $query->where('cticketid', $id)->first();
        }
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
                'tailles.taillelibelle as taille',
                DB::raw('(SELECT COALESCE(SUM(-r.qte), 0) FROM detctickets r WHERE r.detventeid = detctickets.detcticketid AND r.qte < 0) as qte_retournee')
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
            ->where('ctickets.siteid', \App\Support\SiteCourant::id())
            ->where('ctickets.enattente', false)
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
        return DB::transaction(function () use ($id) {
            // Seul un ticket EN ATTENTE du site peut être repris
            $ticket = ctype_digit((string) $id)
                ? DB::table('ctickets')
                    ->where('cticketid', $id)
                    ->where('enattente', true)
                    ->where('siteid', \App\Support\SiteCourant::id())
                    ->lockForUpdate()
                    ->first()
                : null;
            if (!$ticket) {
                return response()->json(['success' => false, 'message' => 'Ticket en attente introuvable.']);
            }

            $enAttenteJson = json_decode($ticket->description ?? '', true);
            $formattedLignes = $enAttenteJson['enattente_lignes'] ?? null;

            if ($formattedLignes === null) {
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
                    'detctickets.ttc as prix',
                    'detctickets.puttcnet as prixNet',
                    'detctickets.remise',
                    'detctickets.totalttcnet as total'
                )
                ->get();

            // Convert to match POS frontend format
            // detctickets.ttc = prix unitaire brut, puttcnet = prix net, totalttcnet = total net de la ligne
            $formattedLignes = [];
            foreach ($lignes as $ligne) {
                $prix = $ligne->prix;

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
            }
            
            // Le ticket en attente n'est PAS supprimé ici : il reste dans la liste tant que la vente reprise
            // n'est pas validée. La caisse renvoie son identifiant (reprise_cticketid) à la validation, et
            // store() le supprime dans la même transaction que le nouveau ticket.
            return response()->json([
                'success' => true,
                'cticketid' => $ticket->cticketid,
                'clientid' => $ticket->clientid,
                'vendeurid' => $ticket->employeeid,
                'lignes' => $formattedLignes
            ]);
        });
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
            ->where('ctickets.netapayer', '>', 0.0005)
            ->where('ctickets.enattente', 0)
            ->where('ctickets.siteid', \App\Support\SiteCourant::id())
            ->when(Droits::bloque('cons.allticket'), fn ($q) => $q->where('ctickets.caissierid', auth()->user()->userid))
            ->whereBetween(DB::raw('DATE(ctickets.datecreation)'), [$du, $au]);

        if ($clientid) {
            $query->where('ctickets.clientid', $clientid);
        }
        if ($numero) {
            $query->whereRaw('CAST(ctickets.cticketnumero AS TEXT) LIKE ?', ['%' . $numero . '%']);
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
            $siteid = SiteCourant::id();
            $caissierid = $user->userid;

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

            $ticket = DB::table('ctickets')
                ->where('cticketid', $cticketid)
                ->where('siteid', $siteid)
                ->where('enattente', false)
                ->lockForUpdate()
                ->first();
            if (!$ticket) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Ticket introuvable.']);
            }

            $now = now();

            $journal = $this->journees->journeeOuverte($user);
            if (!$journal) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Aucune journée ouverte. Veuillez ouvrir une journée avant d\'encaisser.']);
            }
            $journalcaisseid = $journal->journalcaisseid;
            $agencebid = $journal->agencebid;
            // Vendeur du règlement : celui du ticket d'origine, à défaut celui du compte connecté
            $employeeid = $ticket->employeeid ?: $user->employeeid;

            $reste = round(floatval($ticket->netapayer), 3);
            $totalAdded = 0;
            foreach ($reglements as $reg) {
                $montant = round(floatval($reg['montant'] ?? 0), 3);
                $modereglementid = intval($reg['modereglementid'] ?? 1);

                if ($montant > $reste) {
                    $montant = $reste; // Plafonner au reste à payer
                }
                if ($montant <= 0) continue;

                // Règlement de complément, au format du logiciel de bureau (creglements + creglementdets)
                $this->reglementsService->enregistrerPourTicket((int) $ticket->cticketid, [
                    'clientid' => $ticket->clientid,
                    'siteid' => $siteid,
                    'date' => $now,
                    'montant' => $montant,
                    'modereglementid' => $modereglementid,
                    'employeeid' => $employeeid,
                    'userid' => $caissierid,
                    'agencebid' => $agencebid,
                    'journalcaisseid' => $journalcaisseid,
                    'iscomplement' => true,
                ] + $this->detailsReglement($reg, $ticket->cticketnumero, $now));

                $totalAdded += $montant;
                $reste -= $montant;
            }

            if ($totalAdded <= 0) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Veuillez saisir un montant valide.']);
            }

            // Pas de mise à jour manuelle de ctickets.acompte / netapayer :
            // le trigger creglement_calc les recalcule à chaque insertion dans creglements.

            // Mettre à jour le journal caisse
            DB::table('journalcaisses')->where('journalcaisseid', $journalcaisseid)->increment('complementacompte', $totalAdded);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Complément acompte enregistré avec succès.',
                'nouveau_reste' => floatval(DB::table('ctickets')->where('cticketid', $cticketid)->value('netapayer'))
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur Complement Acompte: ' . $e->getMessage() . ' - Ligne: ' . $e->getLine());
            return response()->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }
}
