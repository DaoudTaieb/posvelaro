<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class TransferUiTest extends DuskTestCase
{
    protected $testUser;
    protected $siteExpediteur;
    protected $siteRecepteur;

    protected function setUp(): void
    {
        parent::setUp();

        // Retrieve a real user and sites from the DB
        $dbUser = DB::table('users')->orderBy('userid')->first();
        if ($dbUser) {
            $this->testUser = User::find($dbUser->userid);
        }

        $siteId = $this->testUser ? $this->testUser->siteid : 101;
        $this->siteExpediteur = DB::table('sites')->where('siteid', $siteId)->first() ?? DB::table('sites')->first();
        $this->siteRecepteur = DB::table('sites')->where('siteid', '!=', $this->siteExpediteur->siteid)->first();
    }

    /**
     * Test 1: Verify the Sent and Received transfer lists load successfully in the UI.
     */
    public function test_transfer_index_pages_load()
    {
        if (!$this->testUser) {
            $this->markTestSkipped('No user found to run Dusk UI test.');
        }

        $this->browse(function (Browser $browser) {
            // 1. Visit Sent Transfers Index
            $browser->loginAs($this->testUser)
                    ->visit('/transfert/envoye')
                    ->assertSee('Consultation Bons de Transferts')
                    ->assertSee('ENVOYÉS')
                    ->assertPresent('.data-table');

            // 2. Visit Received Transfers Index
            $browser->visit('/transfert/recu')
                    ->assertSee('Bons de Transferts Reçus')
                    ->assertPresent('.data-table');
        });
    }

    /**
     * Test 2: Verify the creation page for a transfer request (Demande) and its UI elements.
     */
    public function test_create_demande_transfert_ui()
    {
        if (!$this->testUser || !$this->siteRecepteur) {
            $this->markTestSkipped('Insufficient user/site data for Dusk UI test.');
        }

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->testUser)
                    ->visit('/transfert/demande-envoye/create')
                    ->assertSee('Saisie Demande de Transfert')
                    ->assertPresent('select[name="siterecepteurid"]')
                    ->type('description', 'Test Dusk Transfer Request description')
                    // Check search product button triggers modal
                    ->assertPresent('button[onclick="openProductModal()"]')
                    ->click('button[onclick="openProductModal()"]')
                    ->pause(1000) // Wait for modal animation
                    ->assertSee('Sélection Produit')
                    // Close the modal
                    ->click('button[onclick="document.getElementById(\'productModal\').style.display=\'none\'"]');
        });
    }

    /**
     * Test 3: Verify the creation page for a sent transfer voucher (Bon) and its UI elements.
     */
    public function test_create_bon_transfert_ui()
    {
        if (!$this->testUser) {
            $this->markTestSkipped('No user found to run Dusk UI test.');
        }

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->testUser)
                    ->visit('/transfert/envoye/create')
                    ->assertSee('Saisie Bon de Transfert')
                    ->assertPresent('select[name="siterecepteurid"]')
                    ->type('description', 'Test Dusk Bon Transfer description')
                    ->assertPresent('button[onclick="openProductModal()"]');
        });
    }

    /**
     * Test 4: Verify the edit forms for both demands and transfers load their data in the inputs.
     */
    public function test_edit_transfer_forms_ui()
    {
        if (!$this->testUser || !$this->siteRecepteur) {
            $this->markTestSkipped('Insufficient user/site data for Dusk UI test.');
        }

        // Insert a draft demand manually to verify editing
        $demandeId = DB::selectOne("SELECT nextval('demandetransfert_demandetransfertid_seq') as id")->id;
        $numero = (DB::table('demandetransferts')->max('demandetransfertnumero') ?? 0) + 1;

        DB::table('demandetransferts')->insert([
            'demandetransfertid' => $demandeId,
            'siteid' => $this->siteExpediteur->siteid,
            'siterecepteurid' => $this->siteRecepteur->siteid,
            'etatdemandetransfertid' => 1, // Draft
            'demandetransfertnumero' => $numero,
            'numerointerne' => 'DTE-DUSK-EDIT',
            'datecreation' => now(),
            'demandetransfertdate' => now(),
            'datedebut' => now(),
            'datefin' => now(),
            'userid' => $this->testUser->userid,
            'description' => 'Original Dusk Description',
            'totalqte' => 0,
            'confirmer' => false,
            'totalbrutht' => 0, 'remise' => 0, 'vremise' => 0, 'totalnetht' => 0, 'totaltva' => 0, 'totalttc' => 0, 'acompte' => 0, 'netapayer' => 0
        ]);

        $this->browse(function (Browser $browser) use ($demandeId) {
            $browser->loginAs($this->testUser)
                    ->visit('/transfert/demande-envoye/' . $demandeId . '/edit')
                    ->assertSee('Saisie Demande de Transfert')
                    ->assertInputValue('description', 'Original Dusk Description');
        });
    }

    /**
     * Test 5: Demande transfer by Manar to Central Achat with product qty more than in stock.
     */
    public function test_demande_transfert_by_manar_greater_than_stock()
    {
        $manar = User::where('login', 'Manar')->first();
        $centralAchat = DB::table('sites')->where('libelle', 'Central Achat')->first();

        if (!$manar || !$centralAchat) {
            $this->markTestSkipped('Manar user or Central Achat site not found.');
        }

        // Clean up any stale records from previous runs
        DB::table('demandetransferts')
            ->where('description', 'Test excess stock request by Manar')
            ->delete();

        $targetProduit2Id = 5000000000063;

        // Retrieve target product for searching in the UI
        $produit = DB::table('vproduit2stocks')
            ->where('siteid', $manar->siteid)
            ->where('produit2id', $targetProduit2Id)
            ->first();

        if (!$produit) {
            $this->markTestSkipped('Target product not found on Manar\'s site.');
        }

        // Store original stock on Central Achat (site 101) to restore it later
        $originalStock = DB::table('stock2s')
            ->where('siteid', $centralAchat->siteid)
            ->where('produit2id', $targetProduit2Id)
            ->value('qtestock');

        // Temporarily set stock of target product on Central Achat to 5
        DB::table('stock2s')
            ->where('siteid', $centralAchat->siteid)
            ->where('produit2id', $targetProduit2Id)
            ->update(['qtestock' => 5]);

        try {
            $excessQty = 15; // 15 is greater than stock on Central Achat (5)

            $this->browse(function (Browser $browser) use ($manar, $centralAchat, $produit, $excessQty) {
                $browser->loginAs($manar)
                        ->visit('/transfert/demande-envoye/create')
                        ->assertSee('Saisie Demande de Transfert')
                        // Select Central Achat (siteid 101) as receiver
                        ->select('siterecepteurid', (string)$centralAchat->siteid)
                        ->type('description', 'Test excess stock request by Manar')
                        // Open the product search modal
                        ->click('button[onclick="openProductModal()"]')
                        ->pause(1000)
                        ->assertSee('Sélection Produit')
                        // Filter by product reference
                        ->type('#filter-search', $produit->reference)
                        ->click('button[onclick="searchProducts()"]')
                        ->pause(1000)
                        // Click on the first search result row in the modal
                        ->click('#modal-tbody tr')
                        ->pause(500)
                        // Set quantity higher than available stock (15 > 5)
                        ->type('#input-qte', (string)$excessQty)
                        // Add the line
                        ->click('button.action-btn-small.add')
                        ->pause(500)
                        // Assert the row is present in the table with correct quantity
                        ->assertSeeIn('.data-table tbody', (string)$excessQty)
                        // Click the Send (Envoyer) button in the header actions
                        ->click('button[onclick="submitForm(\'envoyer\')"]')
                        ->pause(2000)
                        // Assert redirect back with validation error alert
                        ->assertPathIs('/transfert/demande-envoye/create')
                        ->assertSee('Stock insuffisant pour le produit')
                        ->assertSee('Demandé: 15')
                        ->assertSee('Disponible: 5');
            });

            // Verify the record is NOT created in the database
            $demande = DB::table('demandetransferts')
                ->where('siteid', $manar->siteid)
                ->where('siterecepteurid', $centralAchat->siteid)
                ->where('description', 'Test excess stock request by Manar')
                ->first();

            $this->assertNull($demande);

        } finally {
            // Restore original stock
            DB::table('stock2s')
                ->where('siteid', $centralAchat->siteid)
                ->where('produit2id', $targetProduit2Id)
                ->update(['qtestock' => $originalStock]);
        }
    }
}
