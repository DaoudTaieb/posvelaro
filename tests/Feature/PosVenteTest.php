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
        $userRecord = DB::table('users')->first();
        if ($userRecord) {
            $user = User::find($userRecord->userid) ?? new User((array) $userRecord);
            $user->userid = $userRecord->userid;
            $this->actingAs($user);
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
        
        // La TVA est configurée à 19% en dur dans le contrôleur (HT = TTC / 1.19)
        $expectedHt = 100 / 1.19;
        $this->assertEqualsWithDelta($expectedHt, $ticket->totalnetht, 0.01, 'Le calcul du HT est incorrect');

        // Vérifier que le règlement a été créé
        $reglementCount = DB::table('creglements')->where('documentid', $ticketId)->count();
        $this->assertEquals(1, $reglementCount, 'Le règlement n\'a pas été enregistré');
    }

    public function test_pos_fidelite_bon_achat_generation()
    {
        // Utiliser un client existant pour éviter les erreurs de contraintes DB
        $clientRecord = DB::table('clients')->first();
        if (!$clientRecord) {
            $this->markTestSkipped('Aucun client trouvé dans la BDD.');
        }
        $clientId = $clientRecord->clientid;

        // Le rendre fidèle
        DB::table('clients')->where('clientid', $clientId)->update([
            'fidelite' => 1,
            'total_ventes_fidelite' => 3
        ]);

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
        $response->assertStatus(200);

        // Vérifier l'augmentation du compteur de ventes
        $client = DB::table('clients')->where('clientid', $clientId)->first();
        $this->assertEquals(4, $client->total_ventes_fidelite, 'Le compteur de vente n\'a pas été incrémenté à 4');

        // Vérifier la génération du bon d'achat (3 paires * 10 = 30€)
        $bon = DB::table('bons_achat')->where('clientid', $clientId)->first();
        
        $this->assertNotNull($bon, 'Le bon d\'achat de fidélité n\'a pas été généré');
        $this->assertEquals(30, $bon->montant, 'Le montant du bon d\'achat doit être de 30');
    }
}
