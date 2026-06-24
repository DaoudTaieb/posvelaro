<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class DemandeTransfertTest extends TestCase
{
    use DatabaseTransactions;

    protected $userExpediteur;
    protected $siteExpediteur;
    protected $siteRecepteur;

    protected function setUp(): void
    {
        parent::setUp();

        $dbUser = DB::table('users')->first();
        if ($dbUser) {
            $this->userExpediteur = new User();
            $this->userExpediteur->userid = $dbUser->userid;
            $this->userExpediteur->login = $dbUser->login;
            $this->userExpediteur->siteid = $dbUser->siteid ?? 101;
            $this->userExpediteur->exists = true;
            $this->actingAs($this->userExpediteur);
        }

        $this->siteExpediteur = DB::table('sites')->where('siteid', $this->userExpediteur->siteid)->first() ?? DB::table('sites')->first();
        $this->siteRecepteur = DB::table('sites')->where('siteid', '!=', $this->siteExpediteur->siteid)->first();

        // Réinitialiser la séquence de demande de transfert pour éviter les conflits d'IDs en test
        $maxId = DB::table('demandetransferts')->max('demandetransfertid') ?? 0;
        DB::statement("SELECT setval('demandetransfert_demandetransfertid_seq', ?)", [$maxId + 10]);
    }

    /**
     * Test 1 : La page index des demandes de transfert envoyées se charge correctement.
     */
    public function test_index_demandes_envoyees()
    {
        $response = $this->get('/transfert/demande-envoye');
        
        $response->assertStatus(200);
        $response->assertViewIs('transfert.demande_envoye.index');
        $response->assertViewHas('demandes');
        $response->assertViewHas('sites');
        $response->assertViewHas('etats');
    }

    /**
     * Test 2 : La page de création de demande de transfert se charge correctement.
     */
    public function test_create_demande_page()
    {
        $response = $this->get('/transfert/demande-envoye/create');
        
        $response->assertStatus(200);
        $response->assertViewIs('transfert.demande_envoye.create');
        $response->assertViewHas('sites');
        $response->assertViewHas('familles');
    }

    /**
     * Test 3 : Envoi d'une demande de transfert brouillon (sans lignes).
     */
    public function test_demande_brouillon_sans_lignes()
    {
        if (!$this->siteRecepteur) {
            $this->markTestSkipped('Pas de site récepteur pour tester.');
        }

        $payload = [
            'siteid' => $this->siteExpediteur->siteid,
            'siterecepteurid' => $this->siteRecepteur->siteid,
            'description' => 'Demande brouillon header test',
            'action_type' => 'save' // 1 = Brouillon
        ];

        $response = $this->post('/transfert/demande-envoye', $payload);
        
        $response->assertRedirect('/transfert/demande-envoye');
        $response->assertSessionHas('success');

        $demande = DB::table('demandetransferts')
            ->where('description', 'Demande brouillon header test')
            ->first();

        $this->assertNotNull($demande);
        $this->assertEquals(1, $demande->etatdemandetransfertid);
        $this->assertEquals($this->siteRecepteur->siteid, $demande->siterecepteurid);
    }

    /**
     * Test 4 : Création d'une demande de transfert envoyée avec des lignes.
     */
    public function test_demande_envoyee_avec_lignes()
    {
        if (!$this->siteRecepteur) {
            $this->markTestSkipped('Pas de site récepteur pour tester.');
        }

        $produit = DB::table('produit2s')->first();
        if (!$produit) {
            $this->markTestSkipped('Pas de produit pour tester.');
        }

        $payload = [
            'siteid' => $this->siteExpediteur->siteid,
            'siterecepteurid' => $this->siteRecepteur->siteid,
            'description' => 'Demande envoyée avec lignes test',
            'action_type' => 'envoyer', // 2 = Envoyé
            'lignes' => [
                [
                    'produitid' => $produit->produitid,
                    'produit2id' => $produit->produit2id,
                    'qte' => 3,
                    'prix' => 150.00
                ]
            ]
        ];

        $response = $this->post('/transfert/demande-envoye', $payload);

        $response->assertRedirect('/transfert/demande-envoye');
        $response->assertSessionHas('success');

        // Vérifier l'insertion de l'en-tête
        $demande = DB::table('demandetransferts')
            ->where('description', 'Demande envoyée avec lignes test')
            ->first();

        $this->assertNotNull($demande);
        $this->assertEquals(2, $demande->etatdemandetransfertid); // 2 = Envoyé
        $this->assertEquals(3, $demande->totalqte);

        // Vérifier la ligne de détail
        $ligne = DB::table('detdemandetransferts')
            ->where('demandetransfertid', $demande->demandetransfertid)
            ->first();

        $this->assertNotNull($ligne);
        $this->assertEquals($produit->produit2id, $ligne->produit2id);
        $this->assertEquals(3, $ligne->qte);
    }

    /**
     * Test 5 : Pointage/Validation d'une demande reçue.
     */
    public function test_demande_recu_pointer()
    {
        if (!$this->siteRecepteur) {
            $this->markTestSkipped('Pas de site récepteur pour tester.');
        }

        $produit = DB::table('produit2s')->first();
        if (!$produit) {
            $this->markTestSkipped('Pas de produit pour tester.');
        }

        // Créer manuellement une demande de transfert à l'état 2 (Envoyée) envoyée par siteRecepteur à siteExpediteur
        $demandeId = (DB::table('demandetransferts')->max('demandetransfertid') ?? 0) + 1;
        $numero = (DB::table('demandetransferts')->max('demandetransfertnumero') ?? 0) + 1;

        DB::table('demandetransferts')->insert([
            'demandetransfertid' => $demandeId,
            'siteid' => $this->siteRecepteur->siteid,
            'siterecepteurid' => $this->siteExpediteur->siteid, // Reçu par nous (l'utilisateur connecté)
            'etatdemandetransfertid' => 2, // Envoyé
            'demandetransfertnumero' => $numero,
            'numerointerne' => 'DTE-TEST-REC',
            'datecreation' => now(),
            'demandetransfertdate' => now(),
            'datedebut' => now(),
            'datefin' => now(),
            'userid' => $this->userExpediteur->userid,
            'description' => 'Demande reçue à pointer',
            'confirmer' => false,
            'totalqte' => 10,
            'totalbrutht' => 0,
            'remise' => 0,
            'vremise' => 0,
            'totalnetht' => 0,
            'totaltva' => 0,
            'totalttc' => 0,
            'acompte' => 0,
            'netapayer' => 0
        ]);

        $detId = (DB::table('detdemandetransferts')->max('detdemandetransfertid') ?? 0) + 1;

        DB::table('detdemandetransferts')->insert([
            'detdemandetransfertid' => $detId,
            'demandetransfertid' => $demandeId,
            'siteid' => $this->siteRecepteur->siteid,
            'siterecepteurid' => $this->siteExpediteur->siteid,
            'produitid' => $produit->produitid,
            'produit2id' => $produit->produit2id,
            'taxefamilleid' => 1,
            'ht' => 100,
            'ttc' => 119,
            'qte' => 10,
            'qteenvoi' => 0,
            'qterecu' => 0,
            'qteecart' => 0,
            'etatdemandetransfertid' => 2,
            'totalht' => 1000,
            'remise' => 0,
            'remise2' => 0,
            'totalhtnet' => 1000,
            'taxe1' => 0, 'vtaxe1' => 0,
            'taxe2' => 0, 'vtaxe2' => 0,
            'taxe3' => 0, 'vtaxe3' => 0,
            'taxe4' => 0, 'vtaxe4' => 0,
            'tva' => 19,
            'vtva' => 0,
            'totalttc' => 1190,
            'totalttcnet' => 1190,
            'date' => now(),
            'largeur' => 0, 'longueur' => 0, 'surface' => 0,
            'pointer' => false,
            'ordre' => 1,
            'prodid' => 0,
            'modestock' => 1,
            'etatdemandetransfertid' => 2
        ]);

        // 1. Accéder à l'index des demandes reçues
        $response = $this->get('/transfert/demande-recu');
        $response->assertStatus(200);
        $response->assertViewIs('transfert.demande_recu.index');

        // 2. Pointer/Valider la demande
        $payload = [
            'pointage' => [
                $detId => [
                    'qte_validee' => 8,
                    'cause' => 'Manque de stock'
                ]
            ]
        ];

        $response = $this->post('/transfert/demande-recu/pointer', $payload);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Vérifier que la ligne a été mise à jour
        $this->assertDatabaseHas('detdemandetransferts', [
            'detdemandetransfertid' => $detId,
            'qteenvoi' => 8,
            'description' => 'Manque de stock',
            'pointer' => true,
            'etatdemandetransfertid' => 3
        ]);

        // Vérifier que l'en-tête global est passé à l'état 3
        $this->assertDatabaseHas('demandetransferts', [
            'demandetransfertid' => $demandeId,
            'etatdemandetransfertid' => 3
        ]);
    }

    /**
     * Test 6 : Suppression d'une demande.
     */
    public function test_supprimer_demande()
    {
        $demandeId = (DB::table('demandetransferts')->max('demandetransfertid') ?? 0) + 1;

        DB::table('demandetransferts')->insert([
            'demandetransfertid' => $demandeId,
            'siteid' => $this->siteExpediteur->siteid,
            'siterecepteurid' => $this->siteRecepteur->siteid ?? 102,
            'etatdemandetransfertid' => 1,
            'demandetransfertnumero' => 9999,
            'numerointerne' => 'DTE-TEMP',
            'datecreation' => now(),
            'demandetransfertdate' => now(),
            'datedebut' => now(),
            'datefin' => now(),
            'userid' => $this->userExpediteur->userid,
            'description' => 'Demande à supprimer',
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

        $response = $this->delete('/transfert/demande-envoye/' . $demandeId);
        $response->assertRedirect('/transfert/demande-envoye');
        $response->assertSessionHas('success');

        $this->assertNull(DB::table('demandetransferts')->where('demandetransfertid', $demandeId)->first());
    }

    /**
     * Test de sécurité : non authentifié redirige vers login.
     */
    public function test_non_authentifie_redirige_login()
    {
        auth()->logout();

        $response = $this->get('/transfert/demande-envoye');
        $response->assertRedirect('/login');

        $response = $this->get('/transfert/demande-recu');
        $response->assertRedirect('/login');
    }
}
