<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class PosVenteTest extends TestCase
{
    // Utiliser les transactions pour ne pas polluer la base de données de production/dev
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Simuler la connexion d'un caissier
        $userRecord = DB::table('users')->orderBy('userid')->first();
        if ($userRecord) {
            $user = User::find($userRecord->userid) ?? new User((array) $userRecord);
            $user->userid = $userRecord->userid;
            $user->siteid = $userRecord->siteid ?? 102;
            $user->employeeid = $userRecord->employeeid ?? 1;
            $user->agencebid = $userRecord->agencebid ?? 1;
            $this->actingAs($user);

            // Trouver une caisse
            $caisse = DB::table('caisses')->first();
            $caisseid = $caisse ? $caisse->caisseid : 1;

            // S'assurer qu'un journal de caisse ouvert existe pour ce caissier
            $journalExists = DB::table('journalcaisses')
                ->where('userid', $user->userid)
                ->where('isclosed', 0)
                ->exists();

            if (!$journalExists) {
                $nextJournalId = DB::table('journalcaisses')->max('journalcaisseid') + 1;
                DB::table('journalcaisses')->insert([
                    'journalcaisseid' => $nextJournalId,
                    'caisseid' => $caisseid,
                    'fondcaisse' => 100,
                    'dateouverture' => now(),
                    'userid' => $user->userid,
                    'employeeid' => $user->employeeid ?? 1,
                    'caissierclotureid' => 0,
                    'siteid' => $user->siteid,
                    'isclosed' => 0, // 0 = ouvert
                    'montantcloture' => 0,
                    'montanttheorique' => 0,
                    'envoyee' => false,
                    'agencebid' => $user->agencebid ?? 1,
                ]);
            }
        }

        // Resync sequence for creglements to avoid PK errors due to DB dump imports
        DB::statement("SELECT setval('creglements_creglementid_seq', coalesce(max(creglementid), 1), max(creglementid) IS NOT null) FROM creglements;");
    }

    public function test_pos_validation_ticket_standard()
    {
        // 1. Préparation des données réelles
        // Chercher un produit existant avec sa variante
        $produit2 = DB::table('produit2s')->first();
        if (!$produit2) {
            $this->markTestSkipped('Aucun produit trouvé dans la BDD pour effectuer le test.');
        }

        // 2. Simuler le payload envoyé par l'interface JavaScript de la caisse
        $payload = [
            'clientid' => 1, // Passager
            'vendeurid' => 1,
            'en_attente' => false,
            'acompte' => 0,
            'netapayer' => 0,
            'lignes' => [
                [
                    'produit2id' => $produit2->produit2id,
                    'qte' => 2,
                    'prix' => 50,
                    'prixNet' => 50,
                    'remise' => 0,
                    'total' => 100 // 2 * 50
                ]
            ],
            'reglements' => [
                [
                    'modereglementid' => 1, // Espèce
                    'montant' => 100
                ]
            ],
            'rendu' => 0
        ];

        // 3. Exécuter l'action sur le contrôleur (Requête POST)
        $response = $this->postJson(route('vente.caisse.store'), $payload);
        if ($response->status() !== 200) {
            $response->dump();
        }

        // 4. Assertions (Vérification)
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $ticketId = $response->json('cticketid');

        // Vérifier que le ticket a bien été inséré en base avec les bons calculs
        $ticket = DB::table('ctickets')->where('cticketid', $ticketId)->first();
        
        $this->assertNotNull($ticket);
        $this->assertEquals(100, $ticket->totalttc, 'Le total TTC doit être égal à 100');
        $this->assertEquals(2, $ticket->totalqte, 'La quantité totale doit être de 2');
        
        // La TVA utilisée est celle de la famille de taxe de l'article (HT = TTC / (1 + TVA))
        $tva = (float) DB::table('produits')
            ->join('taxefamilles', 'produits.taxefamilleid', '=', 'taxefamilles.taxefamilleid')
            ->where('produits.produitid', $produit2->produitid)
            ->value('taxefamilles.tvavente');
        $expectedHt = 100 / (1 + $tva / 100);
        $this->assertEqualsWithDelta($expectedHt, $ticket->totalnetht, 0.01, 'Le calcul du HT est incorrect');

        // Vérifier que le règlement a été créé
        $reglementCount = DB::table('creglementdets')->where('documentid', $ticketId)->where('typeid', 1)->count();
        $this->assertEquals(1, $reglementCount, 'Le règlement n\'a pas été enregistré');
    }

    public function test_pos_fidelite_bon_achat_generation()
    {
        // Client fidélité de test avec 3 ventes déjà validées dans le cycle : la vente suivante est la 4ème
        $clientId = DB::table('clients')->insertGetId([
            'nom' => 'TEST FIDELITE POS', 'prenom' => 'Vente', 'clientcode' => 'TSTF' . uniqid(), 'code' => 0,
            'fidelite' => true, 'credit' => true, 'bloque' => false, 'tel' => '98 000 000',
            'clientfamilleid' => DB::table('clientfamilles')->value('clientfamilleid'),
            'echeance' => 0, 'impaye' => 0, 'plafonecheance' => 0, 'plafonsolde' => 0,
            'solde' => 0, 'soldeinitial' => 0, 'remise' => 0, 'soldefidelite' => 0, 'cumulfidelite' => 0, 'pointfidelite' => 0,
            'fidelite_debut' => now()->subDays(30),
        ], 'clientid');
        $journal = DB::table('journalcaisses')->orderByDesc('journalcaisseid')->first();
        foreach ([20, 15, 10] as $j) {
            DB::table('ctickets')->insert([
                'clientid' => $clientId, 'siteid' => $journal->siteid, 'cticketnumero' => 9800000000 + random_int(1, 99999999),
                'modepieceid' => 1, 'etatblid' => 1, 'transfere' => 0, 'datecreation' => now()->subDays($j), 'cticketdate' => now()->subDays($j),
                'totalbrutht' => 100, 'remise' => 0, 'vremise' => 0, 'totalnetht' => 100, 'totaltva' => 0, 'totalttc' => 100,
                'acompte' => 100, 'netapayer' => 0, 'totalqte' => 1, 'caissierid' => $journal->userid, 'employeeid' => $journal->employeeid,
                'journalcaisseid' => $journal->journalcaisseid, 'caisseid' => $journal->caisseid, 'retour' => 0, 'totalavremise' => 100,
                'userid' => $journal->userid, 'vierge' => 0, 'totalfidelite' => 0, 'totalhtachat' => 0, 'totalttcachat' => 0,
                'margeht' => 0, 'margettc' => 0, 'pmargeht' => 0, 'pmargettc' => 0, 'enattente' => false, 'modestock' => 1, 'totalqtettans' => 1,
            ]);
        }

        $produit2 = DB::table('produit2s')->first();
        if (!$produit2) {
            $this->markTestSkipped('Aucun produit trouvé dans la BDD pour effectuer le test.');
        }
        
        // Simuler qu'il appartient à une famille éligible à la fidélité
        $produit = DB::table('produits')->where('produitid', $produit2->produitid)->first();
        if ($produit && $produit->sousfamilleid) {
            DB::table('sousfamilles')
                ->where('sousfamilleid', $produit->sousfamilleid)
                ->update(['is_loyalty_enabled' => 1]);
        }

        $payload = [
            'clientid' => $clientId,
            'vendeurid' => 1,
            'en_attente' => false,
            'lignes' => [
                [
                    'produit2id' => $produit2->produit2id,
                    'qte' => 3, // 3 paires éligibles
                    'prix' => 50,
                    'total' => 150
                ]
            ],
            'reglements' => [['modereglementid' => 1, 'montant' => 150]]
        ];

        $response = $this->postJson(route('vente.caisse.store'), $payload);
        $response->assertStatus(200)->assertJsonPath('success', true);

        // 4ème vente : un bon de 3 paires x 10 DT = 30 DT est généré et renvoyé à la caisse
        $bon = DB::table('bons_achat')->where('clientid', $clientId)->first();
        $this->assertNotNull($bon, 'Le bon d\'achat de fidélité n\'a pas été généré');
        $this->assertEquals(30, (float) $bon->montant, 'Le montant du bon d\'achat doit être de 30');
        $this->assertEquals(now()->addMonths(3)->toDateString(), (string) $bon->date_expiration);
        $response->assertJsonPath('bon_achat.montant', 30)
            ->assertJsonPath('client_tel', '98 000 000')
            ->assertJsonPath('is_first_sale', false);
        $this->assertStringContainsString("bon d'achat de 30.000 DT", $response->json('sms_message'));

        // Le solde fidélité affiché en caisse suit les bons disponibles, et le palier reste à 30 %
        $client = DB::table('clients')->where('clientid', $clientId)->first();
        $this->assertEquals(30, (float) $client->soldefidelite);
        $this->assertSame(30, app(\App\Services\FideliteService::class)->palier($client));

        // Le bon est accepté comme règlement d'une vente suivante, puis refusé une seconde fois
        $numero = app(\App\Services\FideliteService::class)->numeroBon((int) $bon->id);
        $payload['reglements'] = [['modereglementid' => 10, 'montant' => 30, 'numero' => $numero], ['modereglementid' => 1, 'montant' => 120]];
        $this->postJson(route('vente.caisse.store'), $payload)->assertStatus(200)->assertJsonPath('success', true);
        $this->assertTrue((bool) DB::table('bons_achat')->where('id', $bon->id)->value('utilise'));
        $this->postJson(route('vente.caisse.store'), $payload)->assertStatus(200)->assertJsonPath('success', false);
    }
}
