<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class StockManagerTest extends TestCase
{
    // Important pour ne pas fausser le stock de la production après les tests !
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Simuler un login d'utilisateur
        $user = DB::table('users')->first();
        if ($user) {
            $userModel = new \App\Models\User();
            $userModel->userid = $user->userid;
            $userModel->login = $user->login;
            $userModel->exists = true;
            $this->actingAs($userModel);
        }
    }

    public function test_vente_pos_decremente_le_stock_automatiquement()
    {
        $stockData = DB::table('stock2s')->where('qtestock', '>', 0)->first();
        if (!$stockData) {
            $this->markTestSkipped('Aucun stock disponible dans la base.');
        }

        $site = DB::table('sites')->where('siteid', $stockData->siteid)->first();
        DB::table('sites')->where('siteid', $site->siteid)->update(['isstock' => 1]);

        $caisse = DB::table('caisses')->first();
        DB::table('caisses')->where('caisseid', $caisse->caisseid)->update(['siteid' => $site->siteid]);

        $produit2id = $stockData->produit2id;
        $produitid = clone DB::table('produit2s')->where('produit2id', $produit2id)->first();
        $produitInfo = DB::table('produits')->where('produitid', $produitid->produitid ?? $stockData->produitid)->first();
        
        $stockInitial = (float) $stockData->qtestock;
        $qteVendue = 2;

        // 2. Préparer le payload de la caisse POS (comme dans l'interface React/Blade)
        $ticketPayload = [
            'caisse_id' => $caisse->caisseid,
            'client_id' => null, // Client passager
            'vendeur_id' => DB::table('employees')->value('employeeid'),
            'type_ticket' => 'vente', // Ticket normal
            'remise_globale' => 0,
            'lignes' => [
                [
                    'produit2id' => $produit2id,
                    'produitid' => $produitid->produitid ?? $stockData->produitid,
                    'qte' => $qteVendue,
                    'prix' => $produitInfo->ttc_vente ?? 100,
                    'prixNet' => $produitInfo->ttc_vente ?? 100,
                    'remise' => 0,
                    'total' => ($produitInfo->ttc_vente ?? 100) * $qteVendue
                ]
            ],
            'reglements' => [
                [
                    'modereglementid' => 1, // Espèces
                    'montant' => ($produitInfo->ttc_vente ?? 100) * $qteVendue,
                ]
            ]
        ];

        // 3. Envoyer la requête
        $response = $this->post('/vente/caisse', $ticketPayload);

        // Si le contrôleur retourne 500, le test va l'afficher
        if ($response->status() !== 200) {
            $response->dump();
        }
        
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // 4. Vérifier que le stock a bien diminué via le trigger PostgreSQL `calcul_stock`
        $stockFinalData = DB::table('stock2s')
            ->where('siteid', $site->siteid)
            ->where('produit2id', $produit2id)
            ->first();

        $stockFinal = (float) $stockFinalData->qtestock;

        $this->assertEquals(
            $stockInitial - $qteVendue, 
            $stockFinal, 
            "Le stock n'a pas été décrémenté correctement par le trigger."
        );
    }
}
