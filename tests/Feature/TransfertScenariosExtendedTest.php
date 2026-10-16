<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;

class TransfertScenariosExtendedTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $siteExpediteur;
    protected $siteRecepteur;

    protected function setUp(): void
    {
        parent::setUp();

        $dbUser = DB::table('users')->orderBy('userid')->first();
        if ($dbUser) {
            $this->user = new User();
            $this->user->userid = $dbUser->userid;
            $this->user->login = $dbUser->login;
            $this->user->siteid = $dbUser->siteid ?? 101;
            $this->user->exists = true;
            $this->actingAs($this->user);
        }

        $this->siteExpediteur = DB::table('sites')->where('siteid', $this->user->siteid)->first() ?? DB::table('sites')->first();
        $this->siteRecepteur = DB::table('sites')->where('siteid', '!=', $this->siteExpediteur->siteid)->first();
        
        // Ensure sequence exists and is updated to avoid insert conflicts
        $maxId = DB::table('demandetransferts')->max('demandetransfertid') ?? 0;
        DB::statement("SELECT setval('demandetransfert_demandetransfertid_seq', ?)", [$maxId + 10]);
    }

    /**
     * Scenario 1: Create a full transfer request (demande) with multiple lines and verify calculations.
     */
    public function test_create_demande_transfert_complete()
    {
        if (!$this->siteRecepteur) {
            $this->markTestSkipped('Pas de site récepteur pour tester.');
        }

        // Find products with stock on sender site
        $produits = DB::table('vproduit2stocks')
            ->where('siteid', $this->siteExpediteur->siteid)
            ->limit(2)
            ->get();

        if ($produits->count() < 2) {
            $this->markTestSkipped('Pas assez de produits avec stock pour tester.');
        }

        // Set up sufficient stock on the receiver site to satisfy the request
        foreach ($produits as $p) {
            DB::table('stock2s')->updateOrInsert(
                ['produit2id' => $p->produit2id, 'siteid' => $this->siteRecepteur->siteid],
                [
                    'stockid' => 1,
                    'produitid' => $p->produitid,
                    'qtestock' => 100,
                    'stockvirtuel' => 100,
                    'stockreserve' => 0,
                    'datedepart' => '0001-01-01 00:00:00',
                    'qtedepart' => 0,
                    'qteentree' => 0,
                    'qtesortie' => 0,
                    'entreeencour' => 0,
                    'sorieencour' => 0
                ]
            );
        }

        $payload = [
            'siteid' => $this->siteExpediteur->siteid,
            'siterecepteurid' => $this->siteRecepteur->siteid,
            'description' => 'Test demande complete',
            'action_type' => 'envoyer', // Status 2 (Envoyé)
            'lignes' => [
                [
                    'produitid' => $produits[0]->produitid,
                    'produit2id' => $produits[0]->produit2id,
                    'qte' => 5,
                    'prix' => 100.00
                ],
                [
                    'produitid' => $produits[1]->produitid,
                    'produit2id' => $produits[1]->produit2id,
                    'qte' => 2,
                    'prix' => 150.00
                ]
            ]
        ];

        $response = $this->post('/transfert/demande-envoye', $payload);
        $response->assertRedirect('/transfert/demande-envoye');

        // Check header table
        $demande = DB::table('demandetransferts')
            ->where('description', 'Test demande complete')
            ->first();

        $this->assertNotNull($demande);
        $this->assertEquals(2, $demande->etatdemandetransfertid); // Envoyé
        $this->assertEquals(7, $demande->totalqte); // 5 + 2

        // Check lines details
        $lignesDb = DB::table('detdemandetransferts')
            ->where('demandetransfertid', $demande->demandetransfertid)
            ->get();

        $this->assertCount(2, $lignesDb);
    }

    /**
     * Scenario 2: Edit an existing draft transfer request.
     */
    public function test_edit_draft_demande_transfert()
    {
        if (!$this->siteRecepteur) {
            $this->markTestSkipped('Pas de site récepteur pour tester.');
        }

        // Find product with stock on sender site
        $produit = DB::table('vproduit2stocks')
            ->where('siteid', $this->siteExpediteur->siteid)
            ->first();

        if (!$produit) {
            $this->markTestSkipped('Pas de produit avec stock pour tester.');
        }

        // Insert a draft demande manually
        $demandeId = DB::selectOne("SELECT nextval('demandetransfert_demandetransfertid_seq') as id")->id;
        $numero = (DB::table('demandetransferts')->max('demandetransfertnumero') ?? 0) + 1;

        DB::table('demandetransferts')->insert([
            'demandetransfertid' => $demandeId,
            'siteid' => $this->siteExpediteur->siteid,
            'siterecepteurid' => $this->siteRecepteur->siteid,
            'etatdemandetransfertid' => 1, // Brouillon
            'demandetransfertnumero' => $numero,
            'numerointerne' => 'DTE-DRAFT',
            'datecreation' => now(),
            'demandetransfertdate' => now(),
            'datedebut' => now(),
            'datefin' => now(),
            'userid' => $this->user->userid,
            'description' => 'Draft Original Description',
            'totalqte' => 1,
            'confirmer' => false,
            'totalbrutht' => 0, 'remise' => 0, 'vremise' => 0, 'totalnetht' => 0, 'totaltva' => 0, 'totalttc' => 0, 'acompte' => 0, 'netapayer' => 0
        ]);

        $detId = (DB::table('detdemandetransferts')->max('detdemandetransfertid') ?? 0) + 1;
        DB::table('detdemandetransferts')->insert([
            'detdemandetransfertid' => $detId,
            'demandetransfertid' => $demandeId,
            'siteid' => $this->siteExpediteur->siteid,
            'siterecepteurid' => $this->siteRecepteur->siteid,
            'produitid' => $produit->produitid,
            'produit2id' => $produit->produit2id,
            'taxefamilleid' => 1,
            'ht' => 50,
            'ttc' => 59.5,
            'qte' => 1,
            'qteenvoi' => 0, 'qterecu' => 0, 'qteecart' => 0,
            'etatdemandetransfertid' => 1,
            'totalht' => 50, 'remise' => 0, 'remise2' => 0, 'totalhtnet' => 50,
            'taxe1' => 0, 'vtaxe1' => 0,
            'taxe2' => 0, 'vtaxe2' => 0,
            'taxe3' => 0, 'vtaxe3' => 0,
            'taxe4' => 0, 'vtaxe4' => 0,
            'tva' => 19, 'vtva' => 0, 'totalttc' => 59.5, 'totalttcnet' => 59.5,
            'date' => now(), 'largeur' => 0, 'longueur' => 0, 'surface' => 0,
            'pointer' => false, 'ordre' => 1, 'prodid' => 0, // colonne integer, produitid bigint : même convention que le code
            'modestock' => 1
        ]);

        // Set up sufficient stock on the receiver site to satisfy the request
        DB::table('stock2s')->updateOrInsert(
            ['produit2id' => $produit->produit2id, 'siteid' => $this->siteRecepteur->siteid],
            [
                'stockid' => 1,
                'produitid' => $produit->produitid,
                'qtestock' => 100,
                'stockvirtuel' => 100,
                'stockreserve' => 0,
                'datedepart' => '0001-01-01 00:00:00',
                'qtedepart' => 0,
                'qteentree' => 0,
                'qtesortie' => 0,
                'entreeencour' => 0,
                'sorieencour' => 0
            ]
        );

        // Put request payload to edit/update
        $payload = [
            'demandetransfertid' => $demandeId,
            'siteid' => $this->siteExpediteur->siteid,
            'siterecepteurid' => $this->siteRecepteur->siteid,
            'description' => 'Draft Modified Description',
            'action_type' => 'envoyer', // Upgrade draft to Sent
            'lignes' => [
                [
                    'produitid' => $produit->produitid,
                    'produit2id' => $produit->produit2id,
                    'qte' => 10, // Modify quantity
                    'prix' => 80.00
                ]
            ]
        ];

        $response = $this->post('/transfert/demande-envoye', $payload);
        $response->assertRedirect('/transfert/demande-envoye');

        // Check if header is updated
        $updatedDemande = DB::table('demandetransferts')->where('demandetransfertid', $demandeId)->first();
        $this->assertEquals('Draft Modified Description', $updatedDemande->description);
        $this->assertEquals(2, $updatedDemande->etatdemandetransfertid); // State changed to Envoyé
        $this->assertEquals(10, $updatedDemande->totalqte); // Quantity changed to 10

        // Check if old lines were replaced
        $updatedLignes = DB::table('detdemandetransferts')->where('demandetransfertid', $demandeId)->get();
        $this->assertCount(1, $updatedLignes);
        $this->assertEquals(10, $updatedLignes[0]->qte);
    }

    /**
     * Scenario 3: Create a full Transfer Sending (Bon Transfert) with lines and verify.
     */
    public function test_create_bon_transfert_complete()
    {
        if (!$this->siteRecepteur) {
            $this->markTestSkipped('Pas de site récepteur pour tester.');
        }

        // Find product with stock on sender site
        $produit = DB::table('vproduit2stocks')
            ->where('siteid', $this->siteExpediteur->siteid)
            ->first();

        if (!$produit) {
            $this->markTestSkipped('Pas de produit avec stock pour tester.');
        }

        // L'envoi vérifie désormais le stock disponible du site expéditeur
        DB::table('stock2s')
            ->where('produit2id', $produit->produit2id)
            ->where('siteid', $this->siteExpediteur->siteid)
            ->update(['qtestock' => 100]);

        $payload = [
            'siterecepteurid' => $this->siteRecepteur->siteid,
            'description' => 'Test envoi bon complet',
            'action_type' => 'envoyer', // Status 2 (Envoyé)
            'lignes' => [
                [
                    'produitid' => $produit->produitid,
                    'produit2id' => $produit->produit2id,
                    'qte' => 4,
                    'prix' => 120.00
                ]
            ]
        ];

        $response = $this->post('/transfert/envoye', $payload);
        $response->assertRedirect('/transfert/envoye');

        // Verify bon is inserted
        $bon = DB::table('bontransferts')
            ->where('description', 'Test envoi bon complet')
            ->first();

        $this->assertNotNull($bon);
        $this->assertEquals(2, $bon->etatbontransfertid); // Envoyé
        $this->assertEquals(4, $bon->totalqte);

        // Verify detbontransferts
        $ligne = DB::table('detbontransferts')
            ->where('bontransfertid', $bon->bontransfertid)
            ->first();

        $this->assertNotNull($ligne);
        $this->assertEquals(4, $ligne->qteenvoi);
    }

    /**
     * Scenario 4: Update a Bon Transfert Brouillon.
     */
    public function test_update_bon_transfert_brouillon()
    {
        if (!$this->siteRecepteur) {
            $this->markTestSkipped('Pas de site récepteur pour tester.');
        }

        // Find product with stock on sender site
        $produit = DB::table('vproduit2stocks')
            ->where('siteid', $this->siteExpediteur->siteid)
            ->first();

        if (!$produit) {
            $this->markTestSkipped('Pas de produit avec stock pour tester.');
        }

        // Insert a draft bon manually
        $bonId = (DB::table('bontransferts')->max('bontransfertid') ?? 0) + 1;
        $numero = (DB::table('bontransferts')->max('bontransfertnumero') ?? 0) + 1;

        DB::table('bontransferts')->insert([
            'bontransfertid' => $bonId,
            'siteid' => $this->siteExpediteur->siteid,
            'siterecepteurid' => $this->siteRecepteur->siteid,
            'etatbontransfertid' => 1, // Brouillon
            'bontransfertnumero' => $numero,
            'numerointerne' => 'BTE-DRAFT',
            'datecreation' => now(),
            'bontransfertdate' => now(),
            'datedebut' => now(),
            'datefin' => now(),
            'userid' => $this->user->userid,
            'description' => 'Original Bon Description',
            'totalqte' => 2,
            'confirmer' => false,
            'totalbrutht' => 0, 'remise' => 0, 'vremise' => 0, 'totalnetht' => 0, 'totaltva' => 0, 'totalttc' => 0, 'acompte' => 0, 'netapayer' => 0,
            'modereceptionid' => 1, 'typetransfertid' => 1
        ]);

        $detId = DB::selectOne("SELECT nextval('detbontransferts_detbontransfertid_seq') as id")->id;
        DB::table('detbontransferts')->insert([
            'detbontransfertid' => $detId,
            'bontransfertid' => $bonId,
            'siteid' => $this->siteExpediteur->siteid,
            'siterecepteurid' => $this->siteRecepteur->siteid,
            'produitid' => $produit->produitid,
            'produit2id' => $produit->produit2id,
            'taxefamilleid' => 1,
            'ht' => 100, 'ttc' => 119, 'qte' => 2, 'qteenvoi' => 2, 'qterecu' => 0, 'qteecart' => 0,
            'etatbontransfertid' => 1,
            'totalht' => 200, 'remise' => 0, 'remise2' => 0, 'totalhtnet' => 200,
            'taxe1' => 0, 'vtaxe1' => 0,
            'taxe2' => 0, 'vtaxe2' => 0,
            'taxe3' => 0, 'vtaxe3' => 0,
            'taxe4' => 0, 'vtaxe4' => 0,
            'tva' => 19, 'vtva' => 0, 'totalttc' => 238, 'totalttcnet' => 238,
            'date' => now(), 'largeur' => 0, 'longueur' => 0, 'surface' => 0,
            'pointer' => false, 'ordre' => 1, 'prodid' => 0, // colonne integer, produitid bigint : même convention que le code
            'modestock' => 1, 'modereceptionid' => 1, 'stockorigineid' => 1
        ]);

        // Put request payload to edit/update
        $payload = [
            'description' => 'Updated Bon Description',
            'trajet' => 'Sousse-Tunis',
            'lignes' => [
                [
                    'produitid' => $produit->produitid,
                    'produit2id' => $produit->produit2id,
                    'qte' => 8, // Update qty
                    'prix' => 130.00
                ]
            ]
        ];

        $response = $this->put('/transfert/envoye/' . $bonId, $payload);
        $response->assertRedirect('/transfert/envoye');

        // Check if updated in DB
        $updatedBon = DB::table('bontransferts')->where('bontransfertid', $bonId)->first();
        $this->assertEquals('Updated Bon Description', $updatedBon->description);
        $this->assertEquals('Sousse-Tunis', $updatedBon->trajet);
        $this->assertEquals(8, $updatedBon->totalqte);

        // Check if details are replaced
        $updatedLignes = DB::table('detbontransferts')->where('bontransfertid', $bonId)->get();
        $this->assertCount(1, $updatedLignes);
        $this->assertEquals(8, $updatedLignes[0]->qteenvoi);
    }

    /**
     * Scenario 5: Delete a draft Bon Transfert.
     */
    public function test_delete_bon_transfert_brouillon()
    {
        if (!$this->siteRecepteur) {
            $this->markTestSkipped('Pas de site récepteur pour tester.');
        }

        // Find product with stock on sender site
        $produit = DB::table('vproduit2stocks')
            ->where('siteid', $this->siteExpediteur->siteid)
            ->first();

        if (!$produit) {
            $this->markTestSkipped('Pas de produit avec stock pour tester.');
        }

        $bonId = (DB::table('bontransferts')->max('bontransfertid') ?? 0) + 1;
        $numero = (DB::table('bontransferts')->max('bontransfertnumero') ?? 0) + 1;

        DB::table('bontransferts')->insert([
            'bontransfertid' => $bonId,
            'siteid' => $this->siteExpediteur->siteid,
            'siterecepteurid' => $this->siteRecepteur->siteid,
            'etatbontransfertid' => 1, // Brouillon
            'bontransfertnumero' => $numero,
            'numerointerne' => 'BTE-DRAFT-DEL',
            'datecreation' => now(),
            'bontransfertdate' => now(),
            'datedebut' => now(),
            'datefin' => now(),
            'userid' => $this->user->userid,
            'description' => 'Bon to delete',
            'totalqte' => 0,
            'confirmer' => false,
            'totalbrutht' => 0, 'remise' => 0, 'vremise' => 0, 'totalnetht' => 0, 'totaltva' => 0, 'totalttc' => 0, 'acompte' => 0, 'netapayer' => 0,
            'modereceptionid' => 1, 'typetransfertid' => 1
        ]);

        $detId = DB::selectOne("SELECT nextval('detbontransferts_detbontransfertid_seq') as id")->id;
        DB::table('detbontransferts')->insert([
            'detbontransfertid' => $detId,
            'bontransfertid' => $bonId,
            'siteid' => $this->siteExpediteur->siteid,
            'siterecepteurid' => $this->siteRecepteur->siteid,
            'produitid' => $produit->produitid,
            'produit2id' => $produit->produit2id,
            'taxefamilleid' => 1,
            'ht' => 10, 'ttc' => 11.9, 'qte' => 1, 'qteenvoi' => 1, 'qterecu' => 0, 'qteecart' => 0,
            'etatbontransfertid' => 1,
            'totalht' => 10, 'remise' => 0, 'remise2' => 0, 'totalhtnet' => 10,
            'taxe1' => 0, 'vtaxe1' => 0,
            'taxe2' => 0, 'vtaxe2' => 0,
            'taxe3' => 0, 'vtaxe3' => 0,
            'taxe4' => 0, 'vtaxe4' => 0,
            'tva' => 19, 'vtva' => 0, 'totalttc' => 11.9, 'totalttcnet' => 11.9,
            'date' => now(), 'largeur' => 0, 'longueur' => 0, 'surface' => 0,
            'pointer' => false, 'ordre' => 1, 'prodid' => 0, // colonne integer, produitid bigint : même convention que le code
            'modestock' => 1, 'modereceptionid' => 1, 'stockorigineid' => 1
        ]);

        $response = $this->delete('/transfert/envoye/' . $bonId);
        $response->assertRedirect('/transfert/envoye');

        // Check deletion
        $this->assertNull(DB::table('bontransferts')->where('bontransfertid', $bonId)->first());
        $this->assertCount(0, DB::table('detbontransferts')->where('bontransfertid', $bonId)->get());
    }

    /**
     * Scenario 6: API get lines of a Demande Transfert.
     */
    public function test_api_demande_lignes()
    {
        if (!$this->siteRecepteur) {
            $this->markTestSkipped('Pas de site récepteur pour tester.');
        }

        // Find product with stock on sender site
        $produit = DB::table('vproduit2stocks')
            ->where('siteid', $this->siteExpediteur->siteid)
            ->first();

        if (!$produit) {
            $this->markTestSkipped('Pas de produit avec stock pour tester.');
        }

        $demandeId = DB::selectOne("SELECT nextval('demandetransfert_demandetransfertid_seq') as id")->id;
        $numero = (DB::table('demandetransferts')->max('demandetransfertnumero') ?? 0) + 1;

        DB::table('demandetransferts')->insert([
            'demandetransfertid' => $demandeId,
            'siteid' => $this->siteRecepteur->siteid, // demande émise par l'autre site
            'siterecepteurid' => $this->siteExpediteur->siteid, // adressée à notre site (fournisseur)
            'etatdemandetransfertid' => 2, // Envoyé
            'demandetransfertnumero' => $numero,
            'numerointerne' => 'DTE-API',
            'datecreation' => now(),
            'demandetransfertdate' => now(),
            'datedebut' => now(),
            'datefin' => now(),
            'userid' => $this->user->userid,
            'description' => 'Demande for API test',
            'totalqte' => 5,
            'confirmer' => false,
            'totalbrutht' => 0, 'remise' => 0, 'vremise' => 0, 'totalnetht' => 0, 'totaltva' => 0, 'totalttc' => 0, 'acompte' => 0, 'netapayer' => 0
        ]);

        $detId = (DB::table('detdemandetransferts')->max('detdemandetransfertid') ?? 0) + 1;
        DB::table('detdemandetransferts')->insert([
            'detdemandetransfertid' => $detId,
            'demandetransfertid' => $demandeId,
            'siteid' => $this->siteRecepteur->siteid, // demande émise par l'autre site
            'siterecepteurid' => $this->siteExpediteur->siteid, // adressée à notre site (fournisseur)
            'produitid' => $produit->produitid,
            'produit2id' => $produit->produit2id,
            'taxefamilleid' => 1,
            'ht' => 50,
            'ttc' => 59.5,
            'qte' => 5,
            'qteenvoi' => 5, 'qterecu' => 0, 'qteecart' => 0, // Ligne pointée : 5 validées
            'etatdemandetransfertid' => 3,
            'totalht' => 250, 'remise' => 0, 'remise2' => 0, 'totalhtnet' => 250,
            'taxe1' => 0, 'vtaxe1' => 0,
            'taxe2' => 0, 'vtaxe2' => 0,
            'taxe3' => 0, 'vtaxe3' => 0,
            'taxe4' => 0, 'vtaxe4' => 0,
            'tva' => 19, 'vtva' => 0, 'totalttc' => 297.5, 'totalttcnet' => 297.5,
            'date' => now(), 'largeur' => 0, 'longueur' => 0, 'surface' => 0,
            'pointer' => false, 'ordre' => 1, 'prodid' => 0, // colonne integer, produitid bigint : même convention que le code
            'modestock' => 1
        ]);

        // Get request to API
        $response = $this->get('/transfert/envoye/api/demande-lignes/' . $demandeId);
        $response->assertStatus(200);

        // Verify JSON content has the correct fields
        $json = $response->json();
        $this->assertCount(1, $json);
        $this->assertEquals($produit->produit2id, $json[0]['produit2id']);
        $this->assertEquals(5, $json[0]['qte_demandee']);
        $this->assertEquals(5, $json[0]['qte_validee']);
        $this->assertEquals($detId, $json[0]['detdemandetransfertid']);
    }

    /**
     * Scenario 7: View list of received transfer vouchers (bontransferts).
     */
    public function test_index_received_transfers()
    {
        $response = $this->get('/transfert/recu');
        $response->assertStatus(200);
        $response->assertViewIs('transfert.recu.index');
        $response->assertViewHas('bontransferts');
    }

    /**
     * Scenario 8: View the reception form page for a sent transfer voucher.
     */
    public function test_view_receptionner_page()
    {
        if (!$this->siteRecepteur) {
            $this->markTestSkipped('Pas de site récepteur pour tester.');
        }

        $bonId = (DB::table('bontransferts')->max('bontransfertid') ?? 0) + 1;
        $numero = (DB::table('bontransferts')->max('bontransfertnumero') ?? 0) + 1;

        DB::table('bontransferts')->insert([
            'bontransfertid' => $bonId,
            'siteid' => $this->siteRecepteur->siteid, // Sent from recepteur site
            'siterecepteurid' => $this->siteExpediteur->siteid, // Received by our user's site
            'etatbontransfertid' => 2, // Envoyé
            'bontransfertnumero' => $numero,
            'numerointerne' => 'BTE-REC-VIEW',
            'datecreation' => now(),
            'bontransfertdate' => now(),
            'datedebut' => now(),
            'datefin' => now(),
            'userid' => $this->user->userid,
            'description' => 'Test receptionner page',
            'totalqte' => 0,
            'confirmer' => false,
            'totalbrutht' => 0, 'remise' => 0, 'vremise' => 0, 'totalnetht' => 0, 'totaltva' => 0, 'totalttc' => 0, 'acompte' => 0, 'netapayer' => 0,
            'modereceptionid' => 1, 'typetransfertid' => 1
        ]);

        $response = $this->get('/transfert/recu/' . $bonId . '/receptionner');
        $response->assertStatus(200);
        $response->assertViewIs('transfert.recu.receptionner');
        $response->assertViewHas('bon');
        $response->assertViewHas('lignes');
    }

    /**
     * Scenario 9: Autocomplete product search endpoint for transfer creation.
     */
    public function test_demande_search_products_endpoint()
    {
        // 1. Search with no query parameter (returns default limits)
        $response = $this->getJson('/transfert/demande-envoye/search-products');
        $response->assertStatus(200);

        // 2. Search with explicit query matching first product
        $produit = DB::table('vproduit2stocks')
            ->where('siteid', $this->siteExpediteur->siteid)
            ->first();

        if ($produit) {
            $response = $this->getJson('/transfert/demande-envoye/search-products?search=' . urlencode($produit->produitlibelle));
            $response->assertStatus(200);
            $json = $response->json();
            $this->assertNotEmpty($json);
        }
    }

    /**
     * Scenario 10: View the edit form page of a Demande Transfert.
     */
    public function test_view_edit_demande_page()
    {
        if (!$this->siteRecepteur) {
            $this->markTestSkipped('Pas de site récepteur pour tester.');
        }

        $demandeId = DB::selectOne("SELECT nextval('demandetransfert_demandetransfertid_seq') as id")->id;
        $numero = (DB::table('demandetransferts')->max('demandetransfertnumero') ?? 0) + 1;

        DB::table('demandetransferts')->insert([
            'demandetransfertid' => $demandeId,
            'siteid' => $this->siteExpediteur->siteid,
            'siterecepteurid' => $this->siteRecepteur->siteid,
            'etatdemandetransfertid' => 1, // Brouillon
            'demandetransfertnumero' => $numero,
            'numerointerne' => 'DTE-EDIT-VIEW',
            'datecreation' => now(),
            'demandetransfertdate' => now(),
            'datedebut' => now(),
            'datefin' => now(),
            'userid' => $this->user->userid,
            'description' => 'Test edit page',
            'totalqte' => 0,
            'confirmer' => false,
            'totalbrutht' => 0, 'remise' => 0, 'vremise' => 0, 'totalnetht' => 0, 'totaltva' => 0, 'totalttc' => 0, 'acompte' => 0, 'netapayer' => 0
        ]);

        $response = $this->get('/transfert/demande-envoye/' . $demandeId . '/edit');
        $response->assertStatus(200);
        $response->assertViewIs('transfert.demande_envoye.create');
        $response->assertViewHas('demande');
        $response->assertViewHas('lignes');
    }

    /**
     * Scenario 11: View the multiple printing vouchers page.
     */
    public function test_view_impression_multiple_page()
    {
        $response = $this->get('/transfert/envoye/impression-multiple');
        $response->assertStatus(200);
        $response->assertViewIs('transfert.envoye.impression_multiple');
        $response->assertViewHas('bons');
    }

    /**
     * Scenario 12: View the edit form page of a Bon Transfert.
     */
    public function test_view_edit_bon_page()
    {
        if (!$this->siteRecepteur) {
            $this->markTestSkipped('Pas de site récepteur pour tester.');
        }

        $bonId = (DB::table('bontransferts')->max('bontransfertid') ?? 0) + 1;
        $numero = (DB::table('bontransferts')->max('bontransfertnumero') ?? 0) + 1;

        DB::table('bontransferts')->insert([
            'bontransfertid' => $bonId,
            'siteid' => $this->siteExpediteur->siteid,
            'siterecepteurid' => $this->siteRecepteur->siteid,
            'etatbontransfertid' => 1, // Brouillon
            'bontransfertnumero' => $numero,
            'numerointerne' => 'BTE-EDIT-VIEW',
            'datecreation' => now(),
            'bontransfertdate' => now(),
            'datedebut' => now(),
            'datefin' => now(),
            'userid' => $this->user->userid,
            'description' => 'Test edit bon page',
            'totalqte' => 0,
            'confirmer' => false,
            'totalbrutht' => 0, 'remise' => 0, 'vremise' => 0, 'totalnetht' => 0, 'totaltva' => 0, 'totalttc' => 0, 'acompte' => 0, 'netapayer' => 0,
            'modereceptionid' => 1, 'typetransfertid' => 1
        ]);

        $response = $this->get('/transfert/envoye/' . $bonId . '/edit');
        $response->assertStatus(200);
        $response->assertViewIs('transfert.envoye.edit');
        $response->assertViewHas('bon');
        $response->assertViewHas('lignes');
    }
}
