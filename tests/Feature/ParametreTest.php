<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class ParametreTest extends TestCase
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
     * Test de la gestion des caisses.
     */
    public function test_gestion_caisses()
    {
        // 1. Consultation de l'index configuration caisses
        $response = $this->get('/parametre/caisse/configuration');
        $response->assertStatus(200);
        $response->assertViewIs('parametre.caisse.index');

        // Récupérer un site, une agence, un client et une station existants pour l'insertion
        $site = DB::table('sites')->first();
        $this->assertNotNull($site, 'Il doit y avoir au moins un site.');

        $agence = DB::table('agencebs')->first();
        $client = DB::table('clients')->first();
        $station = DB::table('stations')->first();

        $siteId = $site->siteid;
        $agenceId = $agence ? $agence->agencebid : null;
        $clientId = $client ? $client->clientid : null;
        $stationId = $station ? $station->stationid : null;

        // 2. Création d'une caisse (POST)
        $payload = [
            'libelle' => 'Caisse de Test QA',
            'compteur' => 10,
            'numero' => 99,
            'siteid' => $siteId,
            'agencebid' => $agenceId,
            'clientid' => $clientId,
            'machineid' => $stationId,
            'bloque' => 'on'
        ];

        $response = $this->post('/parametre/caisse/configuration', $payload);
        $response->assertRedirect('/parametre/caisse/configuration');
        $response->assertSessionHas('success');

        // Vérifier l'insertion
        $caisse = DB::table('caisses')->where('libelle', 'Caisse de Test QA')->first();
        $this->assertNotNull($caisse);
        $this->assertEquals(10, $caisse->compteur);
        $this->assertEquals(99, $caisse->numero);
        $this->assertTrue($caisse->bloque);

        // 3. Modification de la caisse (PUT)
        $updatePayload = [
            'libelle' => 'Caisse de Test QA Modifiée',
            'compteur' => 20,
            'numero' => 88,
            'siteid' => $siteId,
            'agencebid' => $agenceId,
            'clientid' => $clientId,
            'machineid' => $stationId,
            // non bloqué cette fois
        ];

        $response = $this->put('/parametre/caisse/configuration/' . $caisse->caisseid, $updatePayload);
        $response->assertRedirect('/parametre/caisse/configuration');
        $response->assertSessionHas('success');

        $caisseModifiee = DB::table('caisses')->where('caisseid', $caisse->caisseid)->first();
        $this->assertEquals('Caisse de Test QA Modifiée', $caisseModifiee->libelle);
        $this->assertEquals(20, $caisseModifiee->compteur);
        $this->assertFalse($caisseModifiee->bloque);

        // 4. Consultation de la libération des caisses
        $response = $this->get('/parametre/caisse/liberation');
        $response->assertStatus(200);
        $response->assertViewIs('parametre.caisse.liberation');

        // S'assurer que notre caisse modifiée a une machine (station) associée
        DB::table('caisses')->where('caisseid', $caisse->caisseid)->update([
            'machineid' => $stationId ?? 1,
            'siteid' => $this->user->siteid // Doit correspondre au site de l'utilisateur pour apparaître
        ]);

        // 5. Libération de la caisse (POST)
        $response = $this->post('/parametre/caisse/liberer/' . $caisse->caisseid);
        $response->assertRedirect('/parametre/caisse/liberation');
        $response->assertSessionHas('success');

        $caisseLiberee = DB::table('caisses')->where('caisseid', $caisse->caisseid)->first();
        $this->assertNull($caisseLiberee->machineid);

        // 6. Suppression de la caisse (DELETE)
        $response = $this->delete('/parametre/caisse/configuration/' . $caisse->caisseid);
        $response->assertRedirect('/parametre/caisse/configuration');
        $response->assertSessionHas('success');

        $this->assertNull(DB::table('caisses')->where('caisseid', $caisse->caisseid)->first());
    }

    /**
     * Test de la gestion des vendeurs.
     */
    public function test_gestion_vendeurs()
    {
        // 1. Consultation de l'index des vendeurs
        $response = $this->get('/parametre/vendeur');
        $response->assertStatus(200);
        $response->assertViewIs('parametre.vendeur.index');

        // 2. Création d'un vendeur (POST)
        $response = $this->post('/parametre/vendeur', [
            'nom' => 'Vendeur Test QA',
            'bloque' => 'on'
        ]);
        $response->assertRedirect('/parametre/vendeur');
        $response->assertSessionHas('success');

        $vendeur = DB::table('employees')->where('nom', 'Vendeur Test QA')->first();
        $this->assertNotNull($vendeur);
        $this->assertTrue($vendeur->bloque);
        $this->assertTrue($vendeur->isvendeur);

        // 3. Modification du vendeur (PUT)
        $response = $this->put('/parametre/vendeur/' . $vendeur->employeeid, [
            'nom' => 'Vendeur Test QA Modifié'
            // non bloqué
        ]);
        $response->assertRedirect('/parametre/vendeur');
        $response->assertSessionHas('success');

        $vendeurModifie = DB::table('employees')->where('employeeid', $vendeur->employeeid)->first();
        $this->assertEquals('Vendeur Test QA Modifié', $vendeurModifie->nom);
        $this->assertFalse($vendeurModifie->bloque);
    }

    /**
     * Test de la configuration générale.
     */
    public function test_configuration_general()
    {
        // 1. Consultation
        $response = $this->get('/parametre/configuration/general');
        $response->assertStatus(200);
        $response->assertViewIs('parametre.configuration.general');

        // S'assurer qu'au moins une config existe pour 'Général'
        $configId = DB::table('retailconfigs')->where('pagelibelle', 'Général')->value('retailconfigid');
        if (!$configId) {
            $configId = (DB::table('retailconfigs')->max('retailconfigid') ?? 0) + 1;
            DB::table('retailconfigs')->insert([
                'retailconfigid' => $configId,
                'pagelibelle' => 'Général',
                'libelle' => 'Test Config',
                'value' => 'Old Value',
                'ordreaffichage' => 1
            ]);
        }

        // 2. Mise à jour de la configuration
        $response = $this->post('/parametre/configuration/general', [
            'config' => [
                $configId => 'New Value Test QA'
            ]
        ]);
        $response->assertRedirect('/parametre/configuration/general');
        $response->assertSessionHas('success');

        $this->assertEquals('New Value Test QA', DB::table('retailconfigs')->where('retailconfigid', $configId)->value('value'));
    }

    /**
     * Test de la gestion des utilisateurs.
     */
    public function test_gestion_utilisateurs()
    {
        // 1. Consultation de l'index utilisateur
        $response = $this->get('/parametre/utilisateur');
        $response->assertStatus(200);
        $response->assertViewIs('parametre.utilisateur.index');

        // Obtenir un droit existant
        $droit = DB::table('userdroits')->first();
        if (!$droit) {
            $droitId = 1;
            DB::table('userdroits')->insert([
                'userdroitid' => 1,
                'libelle' => 'Role Test',
                'isadmin' => false,
                'cangrant' => false
            ]);
        } else {
            $droitId = $droit->userdroitid;
        }

        // 2. Création d'un utilisateur (POST)
        $response = $this->post('/parametre/utilisateur', [
            'login' => 'user_test_qa',
            'password' => 'secret123',
            'userdroitid' => $droitId
        ]);
        $response->assertRedirect('/parametre/utilisateur');
        $response->assertSessionHas('success');

        $user = DB::table('users')->where('login', 'user_test_qa')->first();
        $this->assertNotNull($user);
        $this->assertEquals($droitId, $user->userdroitid);

        // 3. Modification de l'utilisateur (PUT)
        $response = $this->put('/parametre/utilisateur/' . $user->userid, [
            'login' => 'user_test_qa_mod',
            'userdroitid' => $droitId,
            'password' => 'newsecret123'
        ]);
        $response->assertRedirect('/parametre/utilisateur');
        $response->assertSessionHas('success');

        $userModifie = DB::table('users')->where('userid', $user->userid)->first();
        $this->assertEquals('user_test_qa_mod', $userModifie->login);
        $this->assertEquals('newsecret123', $userModifie->password);

        // 4. Suppression de l'utilisateur (DELETE)
        $response = $this->delete('/parametre/utilisateur/' . $user->userid);
        $response->assertRedirect('/parametre/utilisateur');
        $response->assertSessionHas('success');

        $this->assertNull(DB::table('users')->where('userid', $user->userid)->first());
    }

    /**
     * Test de la gestion des droits/rôles.
     */
    public function test_gestion_droits_et_roles()
    {
        // 1. Consultation sans ID sélectionné
        $response = $this->get('/parametre/droit');
        $response->assertStatus(200);
        $response->assertViewIs('parametre.droit.index');

        // 2. Création d'un rôle
        $response = $this->post('/parametre/droit/role', [
            'libelle' => 'Rôle Test QA'
        ]);
        // Redirige vers la page d'index avec le paramètre ?id=
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $role = DB::table('userdroits')->where('libelle', 'Rôle Test QA')->first();
        $this->assertNotNull($role);

        // 3. Consultation avec ID sélectionné
        $response = $this->get('/parametre/droit?id=' . $role->userdroitid);
        $response->assertStatus(200);
        $response->assertViewHas('permissions');

        // 4. Modification du libellé du rôle
        $response = $this->put('/parametre/droit/role/' . $role->userdroitid, [
            'libelle' => 'Rôle Test QA Modifié'
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $roleModifie = DB::table('userdroits')->where('userdroitid', $role->userdroitid)->first();
        $this->assertEquals('Rôle Test QA Modifié', $roleModifie->libelle);

        // S'assurer qu'il existe au moins un type de droit
        $typeDroit = DB::table('typedroits')->first();
        if (!$typeDroit) {
            DB::table('typedroits')->insert([
                'typedroitid' => 1,
                'libelle' => 'Permission Test',
                'ordre' => 1
            ]);
            $typeDroitId = 1;
        } else {
            $typeDroitId = $typeDroit->typedroitid;
        }

        // 5. Mise à jour des permissions du rôle
        $response = $this->post('/parametre/droit/permissions/' . $role->userdroitid, [
            'permissions' => [
                $typeDroitId => [
                    'bloque' => 'on',
                    'badge' => 'on'
                ]
            ]
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $permissionLink = DB::table('userdroitdets')
            ->where('userdroitid', $role->userdroitid)
            ->where('typedroitid', $typeDroitId)
            ->first();
        $this->assertNotNull($permissionLink);
        $this->assertTrue($permissionLink->bloque);
        $this->assertTrue($permissionLink->badge);

        // 6. Suppression du rôle (et de ses permissions associées)
        $response = $this->delete('/parametre/droit/role/' . $role->userdroitid);
        $response->assertRedirect('/parametre/droit');
        $response->assertSessionHas('success');

        $this->assertNull(DB::table('userdroits')->where('userdroitid', $role->userdroitid)->first());
        $this->assertNull(DB::table('userdroitdets')->where('userdroitid', $role->userdroitid)->first());
    }

    /**
     * Test de sécurité : redirection vers login pour utilisateur non authentifié.
     */
    public function test_non_authentifie_redirige_login()
    {
        auth()->logout();

        $routes = [
            ['GET', '/parametre/caisse/configuration'],
            ['POST', '/parametre/caisse/configuration'],
            ['GET', '/parametre/caisse/liberation'],
            ['GET', '/parametre/vendeur'],
            ['POST', '/parametre/vendeur'],
            ['GET', '/parametre/configuration/general'],
            ['GET', '/parametre/utilisateur'],
            ['GET', '/parametre/droit']
        ];

        foreach ($routes as $route) {
            $method = $route[0];
            $url = $route[1];

            if ($method === 'GET') {
                $response = $this->get($url);
            } else {
                $response = $this->post($url);
            }

            $response->assertRedirect('/login');
        }
    }
}
