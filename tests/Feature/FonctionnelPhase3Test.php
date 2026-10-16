<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Droits;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Phase 3 : choix du site à la connexion selon le droit, stocks négatifs, contrôle des données,
 * changement de mot de passe. Écritures annulées en fin de test (DatabaseTransactions).
 */
class FonctionnelPhase3Test extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::find(DB::table('users')->where('userdroitid', 1)->orderBy('userid')->value('userid'));
        Droits::viderCache();
    }

    /** Compte de test avec un mot de passe connu, sur le rôle donné */
    private function compte(int $userdroitid, string $mdp = 'motdepasse1'): User
    {
        $id = (int) DB::table('users')->max('userid') + 1;
        DB::table('users')->insert([
            'userid' => $id, 'login' => 'test_phase3_' . $id, 'password' => $mdp, 'userdroitid' => $userdroitid,
            'siteid' => $this->admin->siteid, 'clientid' => $this->admin->clientid, 'societeid' => $this->admin->societeid,
            'employeeid' => $this->admin->employeeid,
        ]);

        return User::find($id);
    }

    private function bloquer(int $userdroitid, string $abreviation): void
    {
        DB::table('userdroitdets')->insert([
            'userdroitdetid' => (int) DB::table('userdroitdets')->max('userdroitdetid') + 1,
            'userdroitid' => $userdroitid,
            'typedroitid' => DB::table('typedroits')->where('abreviation', $abreviation)->value('typedroitid'),
            'bloque' => true, 'badge' => false,
        ]);
        Droits::viderCache();
    }

    public function test_choix_du_site_reserve_aux_roles_autorises(): void
    {
        $roleLibre = (int) DB::table('userdroits')->max('userdroitid') + 1;
        DB::table('userdroits')->insert(['userdroitid' => $roleLibre, 'libelle' => 'Test site libre']);
        $roleBloque = $roleLibre + 1;
        DB::table('userdroits')->insert(['userdroitid' => $roleBloque, 'libelle' => 'Test site bloqué']);
        $this->bloquer($roleBloque, 'updatesitevente');

        $libre = $this->compte($roleLibre);
        $bloque = $this->compte($roleBloque);
        $autreSite = (int) DB::table('sites')->where('clientid', $libre->clientid)->where('siteid', '!=', $libre->siteid)->where('bloque', false)->value('siteid');

        // Liste des sites : proposée seulement au rôle autorisé, avec le site habituel présélectionné
        $this->getJson('/login/sites?login=' . $libre->login)->assertOk()->assertJsonFragment(['siteid' => $libre->siteid, 'actuel' => true]);
        $this->getJson('/login/sites?login=' . $bloque->login)->assertOk()->assertExactJson([]);

        // Un rôle bloqué ne peut pas forcer un autre site en envoyant le formulaire à la main
        $this->post('/login', ['login' => $bloque->login, 'password' => 'motdepasse1', 'siteid' => $autreSite])
            ->assertSessionHasErrors('siteid');
        $this->assertGuest();
        $this->assertEquals($this->admin->siteid, DB::table('users')->where('userid', $bloque->userid)->value('siteid'));

        // Garder son propre site reste possible
        $this->post('/login', ['login' => $bloque->login, 'password' => 'motdepasse1', 'siteid' => $bloque->siteid])
            ->assertRedirect(route('dashboard'));
        $this->post('/logout');

        // Le rôle autorisé change de site
        $this->post('/login', ['login' => $libre->login, 'password' => 'motdepasse1', 'siteid' => $autreSite])
            ->assertRedirect(route('dashboard'));
        $this->assertEquals($autreSite, DB::table('users')->where('userid', $libre->userid)->value('siteid'));
    }

    public function test_ecran_stocks_negatifs(): void
    {
        $this->actingAs($this->admin);
        $attendu = DB::table('stock2s')->where('siteid', $this->admin->siteid)->where('qtestock', '<', 0)->count();

        $page = $this->get(route('stock.negatifs.index'))->assertOk()->assertSee('Stocks négatifs');
        $this->assertEquals($attendu, $page->viewData('totaux')->lignes);
        $this->get(route('stock.negatifs.index', ['q' => 'ZZZ-introuvable']))->assertOk()->assertSee('Aucun article en stock négatif');
    }

    public function test_ecran_controle_des_donnees(): void
    {
        $this->actingAs($this->admin);
        $page = $this->get(route('parametre.controle.index'))->assertOk()->assertSee('Contrôle des données');
        $controles = collect($page->viewData('controles'))->keyBy('titre');

        $ecarts = count(DB::select("SELECT c.cticketid FROM ctickets c JOIN detctickets d ON d.cticketid = c.cticketid
            WHERE c.siteid = ? AND c.enattente = false GROUP BY c.cticketid HAVING ABS(c.totalttc - SUM(d.totalttcnet)) > 0.01", [$this->admin->siteid]));
        $this->assertEquals($ecarts, $controles['Tickets dont le total ne correspond pas aux lignes']['total']);
    }

    public function test_controle_des_donnees_reserve_au_droit_config(): void
    {
        $role = (int) DB::table('userdroits')->max('userdroitid') + 1;
        DB::table('userdroits')->insert(['userdroitid' => $role, 'libelle' => 'Test sans config']);
        $this->bloquer($role, 'config');
        $this->actingAs($this->compte($role));
        $this->get(route('parametre.controle.index'))->assertStatus(403);
    }

    public function test_changer_son_mot_de_passe(): void
    {
        $user = $this->compte(1, 'ancien123');
        $this->actingAs($user);

        $this->get(route('compte.mot_de_passe'))->assertOk();

        $this->post(route('compte.mot_de_passe.update'), ['actuel' => 'faux', 'nouveau' => 'nouveau123', 'nouveau_confirmation' => 'nouveau123'])
            ->assertSessionHasErrors('actuel');
        $this->post(route('compte.mot_de_passe.update'), ['actuel' => 'ancien123', 'nouveau' => 'abc', 'nouveau_confirmation' => 'abc'])
            ->assertSessionHasErrors('nouveau');
        $this->post(route('compte.mot_de_passe.update'), ['actuel' => 'ancien123', 'nouveau' => 'nouveau123', 'nouveau_confirmation' => 'autre123'])
            ->assertSessionHasErrors('nouveau');

        $this->post(route('compte.mot_de_passe.update'), ['actuel' => 'ancien123', 'nouveau' => 'nouveau123', 'nouveau_confirmation' => 'nouveau123'])
            ->assertSessionHas('success');
        // Stocké en clair pour le logiciel de bureau
        $this->assertSame('nouveau123', DB::table('users')->where('userid', $user->userid)->value('password'));
    }

    public function test_mot_de_passe_trop_court_refuse_en_gestion_des_utilisateurs(): void
    {
        $this->actingAs($this->admin);
        $this->post(route('parametre.utilisateur.store'), ['login' => 'test_court_mdp', 'password' => '123', 'userdroitid' => 1])
            ->assertSessionHasErrors('password');
        $this->assertFalse(DB::table('users')->where('login', 'test_court_mdp')->exists());
    }
}
