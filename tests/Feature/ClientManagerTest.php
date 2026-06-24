<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class ClientManagerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        
        $userRecord = DB::table('users')->first();
        if ($userRecord) {
            $user = User::find($userRecord->userid) ?? new User((array) $userRecord);
            $user->userid = $userRecord->userid;
            $this->actingAs($user);
        }

        // Resync sequence for clients as well
        DB::statement("SELECT setval('clients_clientid_seq', coalesce(max(clientid), 1), max(clientid) IS NOT null) FROM clients;");
    }

    public function test_create_valid_client_via_pos_ajax()
    {
        $payload = [
            'raison' => 'Nouveau Client Test',
            'prenom' => 'Jean',
            'telephone' => '0612345678',
            'email' => 'jean@test.com',
            'ville' => 'Paris',
            'g_fidelite' => 'on' // Simulation de la case cochée
        ];

        $response = $this->postJson(route('vente.caisse.pos.store_client'), $payload);
        if ($response->status() !== 200) {
            $response->dump();
        }

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'client' => ['clientid', 'nom', 'tel']]);
        
        $clientId = $response->json('client.clientid');

        // Vérifier dans la base de données
        $client = DB::table('clients')->where('clientid', $clientId)->first();
        $this->assertNotNull($client);
        $this->assertEquals('Nouveau Client Test', $client->nom);
        $this->assertEquals(1, $client->fidelite, 'Le statut de fidélité doit être activé');
        
        // Vérifier la génération du code client (ex: 411000X)
        $this->assertStringStartsWith('411', $client->clientcode);
    }

    public function test_create_client_validation_fails_when_missing_required_fields()
    {
        // On envoie une requête sans le nom ('raison') ni 'telephone' qui sont requis
        $payload = [
            'email' => 'jean@test.com',
            'ville' => 'Paris',
        ];

        $response = $this->postJson(route('vente.caisse.pos.store_client'), $payload);

        // Validation error = 422 Unprocessable Entity
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['raison', 'telephone']);
    }
}
