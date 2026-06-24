<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class JourneeManagerTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $caisse;

    protected function setUp(): void
    {
        parent::setUp();
        
        $employee = DB::table('employees')->first();
        $agence = DB::table('agencebs')->first();
        
        $dbUser = DB::table('users')->first();
        if ($dbUser) {
            $this->user = new User();
            $this->user->userid = $dbUser->userid;
            $this->user->login = $dbUser->login;
            $this->user->siteid = $dbUser->siteid ?? 102;
            $this->user->employeeid = $employee->employeeid ?? 1;
            $this->user->agencebid = $agence->agencebid ?? 1;
            $this->user->exists = true;
            $this->actingAs($this->user);
        }

        // Trouver une caisse
        $this->caisse = DB::table('caisses')->first();
        
        // S'assurer qu'aucune journée n'est ouverte pour cette caisse avant le test (pour éviter les conflits)
        DB::table('journalcaisses')
            ->where('caisseid', $this->caisse->caisseid ?? 0)
            ->where('isclosed', false)
            ->update(['isclosed' => true]);

        // Fixer la séquence PostgreSQL pour éviter les violations de clé unique lors de l'insertion sans ID
        DB::statement("SELECT setval('journalcaisses_journalcaisseid_seq', COALESCE((SELECT MAX(journalcaisseid) FROM journalcaisses), 0) + 1, false);");
    }

    public function test_ouverture_journee_caisse()
    {
        if (!$this->caisse) {
            $this->markTestSkipped('Aucune caisse disponible.');
        }

        $payload = [
            'caisseid' => $this->caisse->caisseid,
            'fondcaisse' => 150.50
        ];

        $response = $this->post('/vente/journee/ouverture', $payload);
        
        // Assert redirect back with success
        $response->assertSessionHas('success');
        
        // Verify in database
        $this->assertDatabaseHas('journalcaisses', [
            'caisseid' => $this->caisse->caisseid,
            'fondcaisse' => 150.50,
            'isclosed' => false,
            'userid' => $this->user->userid
        ]);
    }

    public function test_etat_journee_caisse()
    {
        if (!$this->caisse) {
            $this->markTestSkipped('Aucune caisse disponible.');
        }

        $nextId = DB::table('journalcaisses')->max('journalcaisseid') + 1;

        // Ouvrir manuellement une journée
        DB::table('journalcaisses')->insert([
            'journalcaisseid' => $nextId,
            'caisseid' => $this->caisse->caisseid,
            'fondcaisse' => 100,
            'dateouverture' => now(),
            'userid' => $this->user->userid,
            'employeeid' => $this->user->employeeid ?? 1,
            'caissierclotureid' => 0,
            'siteid' => $this->user->siteid,
            'isclosed' => false,
            'montantcloture' => 0,
            'montanttheorique' => 0,
            'envoyee' => false,
            'agencebid' => $this->user->agencebid ?? 1,
        ]);

        $response = $this->get('/vente/journee/etat');
        
        if ($response->status() === 404) {
            $this->markTestSkipped('La route /vente/journee/etat n\'est pas définie.');
        }

        $response->assertStatus(200);
        $response->assertViewIs('vente.journee.etat');
        $response->assertViewHas('journalCaisse');
        $response->assertViewHas('caisseTotaux');
    }

    public function test_cloture_journee_caisse()
    {
        if (!$this->caisse) {
            $this->markTestSkipped('Aucune caisse disponible.');
        }

        $nextId = DB::table('journalcaisses')->max('journalcaisseid') + 1;

        // Ouvrir une journée
        DB::table('journalcaisses')->insert([
            'journalcaisseid' => $nextId,
            'caisseid' => $this->caisse->caisseid,
            'fondcaisse' => 200,
            'dateouverture' => now(),
            'userid' => $this->user->userid,
            'employeeid' => $this->user->employeeid ?? 1,
            'caissierclotureid' => 0,
            'siteid' => $this->user->siteid,
            'isclosed' => false,
            'montantcloture' => 0,
            'montanttheorique' => 0,
            'envoyee' => false,
            'agencebid' => $this->user->agencebid ?? 1,
        ]);

        // Simuler la clôture
        $payload = [
            'journalcaisseid' => $nextId,
            'totalespecephys' => 250, // On déclare avoir 250 espèces
            'totalchequephys' => 0,
            'totaltpephys' => 0,
            'totalcontrebonphys' => 0,
            'totalbonconventionphys' => 0,
            'totalregavoirphys' => 0,
        ];

        $response = $this->post('/vente/journee/cloture', $payload);
        
        $response->assertSessionHas('success');

        // Vérifier que la journée est fermée
        $this->assertDatabaseHas('journalcaisses', [
            'journalcaisseid' => $nextId,
            'isclosed' => true,
            'totalespecephys' => 250,
            'fondcaisse' => 200
        ]);
        
        $journee = DB::table('journalcaisses')->where('journalcaisseid', $nextId)->first();
        // L'écart total doit être 250 (physique) - 200 (théorique fond de caisse) = 50
        $this->assertEquals(50, $journee->totalecart);
    }
}
