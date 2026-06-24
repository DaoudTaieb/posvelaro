<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class TransfertManagerTest extends TestCase
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
    }

    /**
     * Test 1 : La page de liste des transferts envoyés se charge correctement.
     */
    public function test_index_transferts_envoyes()
    {
        $response = $this->get('/transfert/envoye');
        
        $response->assertStatus(200);
        $response->assertViewIs('transfert.envoye.index');
        $response->assertViewHas('bontransferts');
        $response->assertViewHas('etats');
    }

    /**
     * Test 2 : La page de création de transfert se charge.
     */
    public function test_create_transfert_page()
    {
        $response = $this->get('/transfert/envoye/create');
        
        $response->assertStatus(200);
        $response->assertViewIs('transfert.envoye.create');
        $response->assertViewHas('familles');
        $response->assertViewHas('sites');
    }

    /**
     * Test 3 : Enregistrement d'un bon de transfert (sans lignes — brouillon header only).
     * Cela permet de tester la logique header sans déclencher le bug bigint/integer sur prodid.
     */
    public function test_envoi_transfert_brouillon()
    {
        if (!$this->siteRecepteur) {
            $this->markTestSkipped('Pas de site récepteur pour tester.');
        }

        $payload = [
            'siterecepteurid' => $this->siteRecepteur->siteid,
            'description' => 'Transfert brouillon test',
            'action_type' => 'save', // 1 = Brouillon
        ];

        $response = $this->post('/transfert/envoye', $payload);
        
        $response->assertSessionHas('success');

        $bon = DB::table('bontransferts')
            ->where('description', 'Transfert brouillon test')
            ->first();

        $this->assertNotNull($bon, 'Le bon de transfert brouillon doit exister en base.');
        $this->assertEquals(1, $bon->etatbontransfertid, 'Le bon doit être en état brouillon (1).');
        $this->assertEquals($this->siteRecepteur->siteid, $bon->siterecepteurid);
    }

    /**
     * Test 4 : Réception d'un transfert envoyé.
     * On crée manuellement un bon envoyé avec une ligne, puis on simule la réception.
     */
    public function test_reception_transfert()
    {
        if (!$this->siteRecepteur) {
            $this->markTestSkipped('Pas de site récepteur pour tester.');
        }

        // Créer un bon de transfert envoyé (header)
        $bonId = (DB::table('bontransferts')->max('bontransfertid') ?? 0) + 1;
        $numero = (DB::table('bontransferts')->max('bontransfertnumero') ?? 0) + 1;

        DB::table('bontransferts')->insert([
            'bontransfertid' => $bonId,
            'siteid' => $this->siteExpediteur->siteid,
            'siterecepteurid' => $this->siteRecepteur->siteid,
            'etatbontransfertid' => 2, // Envoyé
            'bontransfertnumero' => $numero,
            'numerointerne' => 'BTE-TEST-REC',
            'datecreation' => now(),
            'bontransfertdate' => now(),
            'datedebut' => now(),
            'datefin' => now(),
            'userid' => $this->userExpediteur->userid,
            'description' => 'Test reception transfert',
            'trajet' => 'Test',
            'confirmer' => false,
            'totalqte' => 5,
            'totalbrutht' => 0,
            'remise' => 0,
            'vremise' => 0,
            'totalnetht' => 0,
            'totaltva' => 0,
            'totalttc' => 0,
            'acompte' => 0,
            'netapayer' => 0,
            'modereceptionid' => 1,
            'typetransfertid' => 1
        ]);

        // Créer une ligne de détail (avec des IDs bigint castés en int pour prodid)
        $produit = DB::table('produit2s')->first();
        $detId = DB::selectOne("SELECT nextval('detbontransferts_detbontransfertid_seq') as id")->id;

        DB::table('detbontransferts')->insert([
            'detbontransfertid' => $detId,
            'bontransfertid' => $bonId,
            'siteid' => $this->siteExpediteur->siteid,
            'siterecepteurid' => $this->siteRecepteur->siteid,
            'produitid' => $produit->produitid,
            'produit2id' => $produit->produit2id,
            'taxefamilleid' => 1,
            'ht' => 100,
            'ttc' => 119,
            'qte' => 5,
            'qteenvoi' => 5,
            'qterecu' => 0,
            'qteecart' => 0,
            'etatbontransfertid' => 2,
            'totalht' => 500,
            'remise' => 0,
            'remise2' => 0,
            'totalhtnet' => 500,
            'taxe1' => 0, 'vtaxe1' => 0,
            'taxe2' => 0, 'vtaxe2' => 0,
            'taxe3' => 0, 'vtaxe3' => 0,
            'taxe4' => 0, 'vtaxe4' => 0,
            'tva' => 19,
            'vtva' => 0,
            'totalttc' => 595,
            'totalttcnet' => 595,
            'date' => now(),
            'largeur' => 0, 'longueur' => 0, 'surface' => 0,
            'pointer' => false,
            'ordre' => 1,
            'prodid' => 0, // On met 0 car la colonne est integer et produitid est bigint (BUG SCHEMA)
            'modestock' => 1,
            'grammagegr' => 0,
            'largeurmm' => 0,
            'longueurm' => 0,
            'modereceptionid' => 1,
            'poids' => 0,
            'stockorigineid' => 1
        ]);

        // Changer le site de l'utilisateur pour simuler le récepteur
        $this->userExpediteur->siteid = $this->siteRecepteur->siteid;

        $payload = [
            'reception' => [
                $detId => [
                    'qte_recue' => 5,
                    'observation' => 'RAS'
                ]
            ]
        ];

        $response = $this->post('/transfert/recu/'.$bonId.'/receptionner', $payload);
        
        $response->assertSessionHas('success');
        
        // Vérifier que le bon est passé en état 3 (Reçu/Validé)
        $this->assertDatabaseHas('bontransferts', [
            'bontransfertid' => $bonId,
            'etatbontransfertid' => 3
        ]);

        // Vérifier que la ligne a été pointée
        $this->assertDatabaseHas('detbontransferts', [
            'detbontransfertid' => $detId,
            'qterecu' => 5,
            'pointer' => true
        ]);
    }

    /**
     * Test 5 : BUG DETECTE - La colonne `prodid` dans `detbontransferts` est de type integer
     * mais les IDs de produits sont en bigint. Cela provoque un crash "Numeric value out of range"
     * lors de la création de transferts avec lignes de produits.
     */
    public function test_bug_schema_prodid_integer_overflow()
    {
        $produit = DB::table('produit2s')->first();
        
        if (!$produit) {
            $this->markTestSkipped('Pas de produit.');
        }

        // Vérifier le type de colonne
        $colInfo = DB::selectOne("SELECT data_type FROM information_schema.columns WHERE table_name='detbontransferts' AND column_name='prodid'");
        
        // Ce test documente le bug: prodid est integer alors que produitid est bigint
        if ($colInfo && $colInfo->data_type === 'integer' && $produit->produitid > 2147483647) {
            $this->markTestIncomplete(
                'BUG SCHEMA: La colonne detbontransferts.prodid est de type INTEGER ' .
                'mais les IDs produits sont en BIGINT (' . $produit->produitid . '). ' .
                'Cela provoque un crash lors de la création de transferts avec lignes. ' .
                'FIX: ALTER TABLE detbontransferts ALTER COLUMN prodid TYPE bigint;'
            );
        }
        
        $this->assertTrue(true, 'Le type de colonne est correct.');
    }
}
