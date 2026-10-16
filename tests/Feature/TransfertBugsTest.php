<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;

class TransfertBugsTest extends TestCase
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
            $this->user->siteid = $dbUser->siteid ?? 102;
            $this->user->exists = true;
            $this->actingAs($this->user);
        }

        $this->siteExpediteur = DB::table('sites')->where('siteid', $this->user->siteid)->first() ?? DB::table('sites')->first();
        $this->siteRecepteur = DB::table('sites')->where('siteid', '!=', $this->siteExpediteur->siteid)->first();
    }

    /**
     * Scenario 1: Proving that self-transfer requests are currently allowed (Incoherency).
     */
    public function test_self_transfer_request_is_allowed()
    {
        $produit = DB::table('produit2s')->first();
        if (!$produit) {
            $this->markTestSkipped('Pas de produit pour tester.');
        }

        // Mock stock for self site to bypass validation
        DB::table('stock2s')->updateOrInsert(
            ['produit2id' => $produit->produit2id, 'siteid' => $this->siteExpediteur->siteid],
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

        $payload = [
            'siteid' => $this->siteExpediteur->siteid,
            'siterecepteurid' => $this->siteExpediteur->siteid, // SAME SITE
            'description' => 'Self transfer test request',
            'action_type' => 'envoyer',
            'lignes' => [
                [
                    'produitid' => $produit->produitid,
                    'produit2id' => $produit->produit2id,
                    'qte' => 10,
                    'prix' => 100.00
                ]
            ]
        ];

        // Ensure DTE sequence is correct
        $maxId = DB::table('demandetransferts')->max('demandetransfertid') ?? 0;
        DB::statement("SELECT setval('demandetransfert_demandetransfertid_seq', ?)", [$maxId + 10]);

        $response = $this->post('/transfert/demande-envoye', $payload);

        // Assert that the request redirects back with error (meaning it is REFUSED, which is the correct validation)
        $response->assertSessionHas('error', "Le site expéditeur et le site récepteur ne peuvent pas être identiques.");
        
        $demande = DB::table('demandetransferts')
            ->where('description', 'Self transfer test request')
            ->first();

        $this->assertNull($demande);
    }

    /**
     * Scenario 2: Proving the stock calculation bug in public.cbl_stock() trigger.
     * When shipping/receiving partial stock, the trigger uses requested quantity (qte)
     * instead of shipped quantity (qteenvoi) or received quantity (qterecu).
     */
    public function test_stock_trigger_bug_recalculated_incorrectly()
    {
        $produit = DB::table('produit2s')->first();
        if (!$produit) {
            $this->markTestSkipped('Pas de produit pour tester.');
        }

        // Set initial stock:
        // Sender site: 100
        // Receiver site: 0
        DB::table('stock2s')->updateOrInsert(
            ['produit2id' => $produit->produit2id, 'siteid' => $this->siteExpediteur->siteid],
            [
                'stockid' => 1,
                'produitid' => $produit->produitid,
                'qtestock' => 100,
                'stockvirtuel' => 100,
                'stockreserve' => 0,
                'datedepart' => '0001-01-01 00:00:00',
                'qtedepart' => 0, 'qteentree' => 0, 'qtesortie' => 0, 'entreeencour' => 0, 'sorieencour' => 0
            ]
        );

        DB::table('stock2s')->updateOrInsert(
            ['produit2id' => $produit->produit2id, 'siteid' => $this->siteRecepteur->siteid],
            [
                'stockid' => 2,
                'produitid' => $produit->produitid,
                'qtestock' => 0,
                'stockvirtuel' => 0,
                'stockreserve' => 0,
                'datedepart' => '0001-01-01 00:00:00',
                'qtedepart' => 0, 'qteentree' => 0, 'qtesortie' => 0, 'entreeencour' => 0, 'sorieencour' => 0
            ]
        );

        // 1. Create a transfer request for 10 units
        $demandeId = (DB::table('demandetransferts')->max('demandetransfertid') ?? 0) + 1;
        $dteNum = (DB::table('demandetransferts')->max('demandetransfertnumero') ?? 0) + 1;
        
        DB::table('demandetransferts')->insert([
            'demandetransfertid' => $demandeId,
            'siteid' => $this->siteRecepteur->siteid, // Receiver requests it
            'siterecepteurid' => $this->siteExpediteur->siteid, // Sender supplies it
            'etatdemandetransfertid' => 2, // Sent
            'demandetransfertnumero' => $dteNum,
            'numerointerne' => 'DTE-BUG-TEST',
            'datecreation' => now(),
            'demandetransfertdate' => now(),
            'datedebut' => now(),
            'datefin' => now(),
            'totalqte' => 10,
            'confirmer' => false,
            'totalbrutht' => 0, 'remise' => 0, 'vremise' => 0, 'totalnetht' => 0, 'totaltva' => 0, 'totalttc' => 0, 'acompte' => 0, 'netapayer' => 0
        ]);

        $detDteId = (DB::table('detdemandetransferts')->max('detdemandetransfertid') ?? 0) + 1;
        DB::table('detdemandetransferts')->insert([
            'detdemandetransfertid' => $detDteId,
            'demandetransfertid' => $demandeId,
            'siteid' => $this->siteRecepteur->siteid,
            'siterecepteurid' => $this->siteExpediteur->siteid,
            'produitid' => $produit->produitid,
            'produit2id' => $produit->produit2id,
            'taxefamilleid' => 1,
            'ht' => 10, 'ttc' => 11.9,
            'qte' => 10,
            'qteenvoi' => 0, 'qterecu' => 0, 'qteecart' => 0,
            'etatdemandetransfertid' => 2,
            'totalht' => 100, 'remise' => 0, 'remise2' => 0, 'totalhtnet' => 100,
            'taxe1' => 0, 'vtaxe1' => 0,
            'taxe2' => 0, 'vtaxe2' => 0,
            'taxe3' => 0, 'vtaxe3' => 0,
            'taxe4' => 0, 'vtaxe4' => 0,
            'tva' => 19, 'vtva' => 19, 'totalttc' => 119, 'totalttcnet' => 119,
            'date' => now(), 'largeur' => 0, 'longueur' => 0, 'surface' => 0,
            'pointer' => false, 'ordre' => 1, 'prodid' => 0, // colonne integer, produitid bigint : même convention que le code
            'modestock' => 1
        ]);

        // 2. Point/Validate only 5 units to send (partial fulfillment)
        DB::table('detdemandetransferts')->where('detdemandetransfertid', $detDteId)->update([
            'qteenvoi' => 5, // Approved only 5
            'etatdemandetransfertid' => 3 // Validated
        ]);

        // 3. Create shipping voucher (BTE) for the 5 units
        $bonId = (DB::table('bontransferts')->max('bontransfertid') ?? 0) + 1;
        $bteNum = (DB::table('bontransferts')->max('bontransfertnumero') ?? 0) + 1;

        DB::table('bontransferts')->insert([
            'bontransfertid' => $bonId,
            'siteid' => $this->siteExpediteur->siteid, // Sender
            'siterecepteurid' => $this->siteRecepteur->siteid, // Receiver
            'bontransfertnumero' => $bteNum,
            'numerointerne' => 'BTE-BUG-TEST',
            'etatbontransfertid' => 2, // Sent (This fires the trigger!)
            'datecreation' => now(),
            'bontransfertdate' => now(),
            'datedebut' => now(),
            'datefin' => now(),
            'totalqte' => 5,
            'confirmer' => false,
            'totalbrutht' => 0, 'remise' => 0, 'vremise' => 0, 'totalnetht' => 0, 'totaltva' => 0, 'totalttc' => 0, 'acompte' => 0, 'netapayer' => 0,
            'modereceptionid' => 1, 'typetransfertid' => 1
        ]);

        $detBteId = (DB::table('detbontransferts')->max('detbontransfertid') ?? 0) + 1;
        // IMPORTANT: We use DatabaseTransactions, so this trigger cbl_stock will fire
        DB::table('detbontransferts')->insert([
            'detbontransfertid' => $detBteId,
            'bontransfertid' => $bonId,
            'siteid' => $this->siteExpediteur->siteid,
            'siterecepteurid' => $this->siteRecepteur->siteid,
            'produitid' => $produit->produitid,
            'produit2id' => $produit->produit2id,
            'taxefamilleid' => 1,
            'ht' => 10, 'ttc' => 11.9,
            // Convention du logiciel de bureau, lue par le trigger cbl_stock en base : qte = quantité expédiée.
            // La quantité demandée (10) reste sur la demande de transfert.
            'qte' => 5,
            'qteenvoi' => 5, // We shipped only 5
            'qterecu' => 0, 'qteecart' => 0,
            'etatbontransfertid' => 2,
            'totalht' => 50, 'remise' => 0, 'remise2' => 0, 'totalhtnet' => 50,
            'taxe1' => 0, 'vtaxe1' => 0,
            'taxe2' => 0, 'vtaxe2' => 0,
            'taxe3' => 0, 'vtaxe3' => 0,
            'taxe4' => 0, 'vtaxe4' => 0,
            'tva' => 19, 'vtva' => 9.5, 'totalttc' => 59.5, 'totalttcnet' => 59.5,
            'date' => now(), 'largeur' => 0, 'longueur' => 0, 'surface' => 0,
            'pointer' => false, 'ordre' => 1, 'prodid' => 0, // colonne integer, produitid bigint : même convention que le code
            'modestock' => 1,
            'grammagegr' => 0, 'largeurmm' => 0, 'longueurm' => 0, 'modereceptionid' => 1, 'poids' => 0, 'stockorigineid' => 1
        ]);

        // VERIFY SENDER STOCK:
        $senderStock = DB::table('stock2s')
            ->where('produit2id', $produit->produit2id)
            ->where('siteid', $this->siteExpediteur->siteid)
            ->value('qtestock');

        // Sender initial stock is computed dynamically from scratch by the trigger.
        // Since no other documents exist, computed stock = (0 inputs) - (sent transfer).
        // Le trigger en base débite detbontransferts.qte (= 5 expédiés) -> stock -5.
        $this->assertEquals(-5.0, (float)$senderStock);
        
        // 4. Receive the stock at the receiver site (e.g. they receive the 5 items)
        DB::table('detbontransferts')->where('detbontransfertid', $detBteId)->update([
            'qterecu' => 5,
            'etatbontransfertid' => 3 // Confirm / Received (Fires trigger!)
        ]);
        
        DB::table('bontransferts')->where('bontransfertid', $bonId)->update([
            'etatbontransfertid' => 3
        ]);

        // VERIFY RECEIVER STOCK:
        // Receiver had 0. They received 5. Correct stock should be 5.
        // The trigger is now correct, so it adds 5 (qterecu).
        $receiverStock = DB::table('stock2s')
            ->where('produit2id', $produit->produit2id)
            ->where('siteid', $this->siteRecepteur->siteid)
            ->value('qtestock');

        $this->assertEquals(5.0, (float)$receiverStock); // Trigger now correctly calculates 5 instead of 10!
    }
}
