<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class DashboardTest extends TestCase
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
     * Test 1 : Le dashboard se charge avec tous les KPIs.
     */
    public function test_dashboard_charge_avec_kpis()
    {
        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard');

        // KPIs présents dans la vue
        $response->assertViewHas('caToday');
        $response->assertViewHas('ticketsToday');
        $response->assertViewHas('panierMoyenToday');
        $response->assertViewHas('articlesToday');
        $response->assertViewHas('caTrend');
        $response->assertViewHas('ticketsTrend');
    }

    /**
     * Test 2 : Les données de chart (7 derniers jours) sont présentes.
     */
    public function test_dashboard_chart_data()
    {
        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('chartLabels');
        $response->assertViewHas('chartData');

        // 7 jours dans le chart
        $chartLabels = $response->viewData('chartLabels');
        $chartData = $response->viewData('chartData');

        $this->assertCount(7, $chartLabels, 'Le chart doit avoir 7 labels (1 par jour).');
        $this->assertCount(7, $chartData, 'Le chart doit avoir 7 valeurs (1 par jour).');
    }

    /**
     * Test 3 : Les sections analytiques sont présentes.
     */
    public function test_dashboard_sections_analytiques()
    {
        $response = $this->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewHas('topProducts');
        $response->assertViewHas('flops');
        $response->assertViewHas('categorySales');
        $response->assertViewHas('peakHours');
        $response->assertViewHas('topVendeurs');
        $response->assertViewHas('topClients');
        $response->assertViewHas('recentTickets');
    }

    /**
     * Test 4 : Le calcul du panier moyen est correct.
     */
    public function test_dashboard_calcul_panier_moyen()
    {
        $response = $this->get('/dashboard');
        $response->assertStatus(200);

        $caToday = $response->viewData('caToday');
        $ticketsToday = $response->viewData('ticketsToday');
        $panierMoyenToday = $response->viewData('panierMoyenToday');

        if ($ticketsToday > 0) {
            $expected = round($caToday / $ticketsToday, 2);
            $this->assertEqualsWithDelta($expected, $panierMoyenToday, 0.01,
                'Le panier moyen doit être CA / Nb Tickets.');
        } else {
            $this->assertEquals(0, $panierMoyenToday,
                'Panier moyen doit être 0 s\'il n\'y a pas de tickets.');
        }
    }

    /**
     * Test 5 : Non authentifié → redirection vers login.
     */
    public function test_dashboard_non_authentifie_redirige()
    {
        // Déconnexion
        auth()->logout();

        $response = $this->get('/dashboard');

        // Doit rediriger vers /login
        $response->assertRedirect('/login');
    }
}
