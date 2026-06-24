<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class TicketEtCommissionTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $dbUser = DB::table('users')->first();
        if ($dbUser) {
            $this->user = new User();
            $this->user->userid = $dbUser->userid;
            $this->user->login = $dbUser->login;
            $this->user->siteid = $dbUser->siteid ?? 101;
            $this->user->exists = true;
            $this->actingAs($this->user);
        }
    }

    /**
     * Test de l'index des tickets et des filtres de date et AJAX.
     */
    public function test_tickets_index_et_filtres()
    {
        // 1. Accès standard
        $response = $this->get('/vente/tickets');
        $response->assertStatus(200);
        $response->assertViewIs('vente.tickets.index');
        $response->assertViewHas('tickets');
        $response->assertViewHas('clients');
        $response->assertViewHas('totals');
        $response->assertViewHas('kpis');

        // 2. Requête AJAX de filtrage par date
        $response = $this->getJson('/vente/tickets?date_du=2026-01-01&date_au=2026-12-31', [
            'HTTP_X-Requested-With' => 'XMLHttpRequest'
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure(['html', 'pagination', 'totals', 'kpis']);

        // 3. Filtrage global par mot-clé (AJAX)
        $response = $this->getJson('/vente/tickets?q=Admin', [
            'HTTP_X-Requested-With' => 'XMLHttpRequest'
        ]);
        $response->assertStatus(200);
    }

    /**
     * Test d'affichage du détail d'un ticket (HTML et AJAX).
     */
    public function test_tickets_show()
    {
        // Récupérer un ticket de caisse existant pour le test
        $ticket = DB::table('ctickets')->first();
        if (!$ticket) {
            $this->markTestSkipped('Pas de ticket en base pour tester show.');
        }

        // 1. Consultation via AJAX
        $response = $this->get('/vente/tickets/' . $ticket->cticketid, [
            'HTTP_X-Requested-With' => 'XMLHttpRequest'
        ]);
        $response->assertStatus(200);
        $response->assertViewIs('vente.tickets.show');

        // 2. Consultation directe (impression directe)
        $response = $this->get('/vente/tickets/' . $ticket->cticketid);
        $response->assertStatus(200);
        $response->assertSee('Ticket N°');
    }

    /**
     * Test de l'index des commissions et de son filtrage.
     */
    public function test_commissions_index_et_calcul()
    {
        // 1. Accès standard
        $response = $this->get('/vente/commissions');
        $response->assertStatus(200);
        $response->assertViewIs('vente.commissions.index');
        $response->assertViewHas('commissions');
        $response->assertViewHas('vendeurs');

        // 2. Filtrage AJAX
        $response = $this->getJson('/vente/commissions?date_du=2026-01-01', [
            'HTTP_X-Requested-With' => 'XMLHttpRequest'
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure(['html', 'pagination', 'totals', 'kpis']);

        // 3. Calcul des commissions
        $response = $this->postJson('/vente/commissions/calculate');
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Calcul effectué.']);
    }

    /**
     * Test de sécurité : non authentifié redirige vers login.
     */
    public function test_non_authentifie_redirige_login()
    {
        auth()->logout();

        $response = $this->get('/vente/tickets');
        $response->assertRedirect('/login');

        $response = $this->get('/vente/commissions');
        $response->assertRedirect('/login');
    }
}
