<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Droits;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Vérifie que les droits (userdroits / userdroitdets) sont appliqués côté serveur.
 */
class DroitsTest extends TestCase
{
    use DatabaseTransactions;

    private function utilisateurAvecRole(int $userdroitid): User
    {
        $row = DB::table('users')->orderBy('userid')->first();
        $user = new User();
        $user->userid = $row->userid;
        $user->login = $row->login;
        $user->siteid = $row->siteid;
        $user->employeeid = $row->employeeid;
        $user->userdroitid = $userdroitid;
        $user->exists = true;
        Droits::viderCache();
        return $user;
    }

    /** Crée un rôle temporaire avec les droits (abréviations) bloqués passés en paramètre */
    private function roleAvecBlocages(array $abreviations, array $indicateurs = []): int
    {
        $roleId = DB::table('userdroits')->max('userdroitid') + 1;
        DB::table('userdroits')->insert(array_merge(['userdroitid' => $roleId, 'libelle' => 'Test droits'], $indicateurs));

        $detId = DB::table('userdroitdets')->max('userdroitdetid') + 1;
        foreach (DB::table('typedroits')->whereIn('abreviation', $abreviations)->pluck('typedroitid') as $typeId) {
            DB::table('userdroitdets')->insert([
                'userdroitdetid' => $detId++, 'userdroitid' => $roleId, 'typedroitid' => $typeId, 'bloque' => true, 'badge' => false,
            ]);
        }
        return $roleId;
    }

    public function test_role_sans_caisse_ni_backoffice_est_refuse()
    {
        $role = $this->roleAvecBlocages([], ['notcaisse' => true, 'notbackoffice' => true]);
        $this->actingAs($this->utilisateurAvecRole($role));

        $this->get('/vente/caisse/pos')->assertStatus(403);
        $this->get('/stock/articles')->assertStatus(403);
        $this->get('/parametre/utilisateur')->assertStatus(403);
        $this->getJson('/vente/caisse/journal-data')->assertStatus(403)->assertJson(['success' => false]);
    }

    public function test_droits_bloques_appliques_sur_les_routes()
    {
        $role = $this->roleAvecBlocages(['cons.etatjourneecaisse', 'gestion.users', 'supp.client']);
        $this->actingAs($this->utilisateurAvecRole($role));

        $this->get('/vente/journee/etat')->assertStatus(403);
        $this->get('/parametre/utilisateur')->assertStatus(403);
        $this->deleteJson('/vente/clients/1')->assertStatus(403);

        // Les autres pages restent accessibles
        $this->get('/stock/articles')->assertStatus(200);
        $this->get('/parametre/vendeur')->assertStatus(200);
    }

    public function test_ticket_en_attente_refuse_si_droit_bloque()
    {
        $role = $this->roleAvecBlocages(['tick.attente']);
        $this->actingAs($this->utilisateurAvecRole($role));

        $produit2 = DB::table('produit2s')->first();
        $this->postJson(route('vente.caisse.store'), [
            'en_attente' => true,
            'lignes' => [['produit2id' => $produit2->produit2id, 'qte' => 1, 'prix' => 10, 'total' => 10]],
        ])->assertJson(['success' => false])
          ->assertJsonFragment(['message' => Droits::message('tick.attente')]);
    }

    public function test_admin_a_acces_partout()
    {
        $this->actingAs($this->utilisateurAvecRole(1));

        $this->get('/stock/articles')->assertStatus(200);
        $this->get('/parametre/utilisateur')->assertStatus(200);
        $this->get('/transfert/envoye')->assertStatus(200);
        $this->assertTrue(Droits::autorise('caisse'));
        $this->assertTrue(Droits::autorise('supp.client'));
    }

    public function test_menu_masque_les_entrees_non_autorisees()
    {
        $role = $this->roleAvecBlocages(['gestion.droits']);
        $this->actingAs($this->utilisateurAvecRole($role));

        $html = $this->get('/stock/articles')->assertStatus(200)->getContent();
        $this->assertStringNotContainsString(route('parametre.droit.index'), $html);
        $this->assertStringContainsString(route('parametre.utilisateur.index'), $html);
    }
}
