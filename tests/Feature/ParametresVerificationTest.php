<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Droits;
use App\Support\Identifiants;
use App\Support\RetailConfig;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Vérification complète du module Paramètres (8 écrans) sur la base velaro :
 * Général, Contrôle des données, Caisse, Imprimantes et douchettes, Gestion des utilisateurs,
 * Gestion des droits, Gestion des vendeurs, Libération de caisse.
 * Toutes les écritures sont annulées en fin de test (DatabaseTransactions).
 */
class ParametresVerificationTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private int $siteid;

    protected function setUp(): void
    {
        parent::setUp();
        // Administrateur réel du magasin Velaro (rôle 1, site avec caisse et journée)
        $this->admin = User::find(DB::table('users')->where('userdroitid', 1)->where('siteid', 102)->value('userid')
            ?? DB::table('users')->where('userdroitid', 1)->value('userid'));
        $this->siteid = (int) $this->admin->siteid;
        $this->actingAs($this->admin);
        Droits::viderCache();
        RetailConfig::vider();
    }

    private function sequence(string $table, string $colonne): int
    {
        $s = DB::selectOne('SELECT pg_get_serial_sequence(?, ?) AS s', [$table, $colonne])->s;
        $e = DB::selectOne("SELECT last_value, is_called FROM {$s}");

        return $e->is_called ? (int) $e->last_value : (int) $e->last_value - 1;
    }

    // ---------------------------------------------------------------- Les 8 écrans s'ouvrent

    public function test_les_huit_ecrans_du_menu_parametres_s_affichent(): void
    {
        foreach ([
            'parametre.configuration.general' => 'Configuration Générale',
            'parametre.controle.index' => 'Contrôle des données',
            'parametre.caisse.index' => 'Caisse',
            'parametre.impression.index' => 'Imprimantes et douchettes',
            'parametre.utilisateur.index' => 'Vendeur associé',
            'parametre.droit.index' => 'Permissions',
            'parametre.vendeur.index' => 'Vendeur',
            'parametre.caisse.liberation' => 'Libération',
        ] as $route => $texte) {
            $this->get(route($route))->assertOk()->assertSee($texte, false);
        }
        // Le menu affiche bien les 8 entrées
        $menu = $this->get(route('parametre.configuration.general'))->getContent();
        foreach (['General', 'Contrôle des données', 'Imprimantes et douchettes', 'Gestion des utilisateur', 'Gestion des Droits', 'Gestion des Vendeurs', 'Libération de caisse'] as $entree) {
            $this->assertStringContainsString($entree, $menu);
        }
    }

    // ---------------------------------------------------------------- Identifiants et séquences

    public function test_identifiants_surs_pour_le_logiciel_de_bureau(): void
    {
        foreach ([['users', 'userid'], ['employees', 'employeeid'], ['userdroits', 'userdroitid'], ['caisses', 'caisseid'], ['retailconfigs', 'retailconfigid'], ['caisseconfigs', 'caisseconfigid']] as [$t, $c]) {
            $this->assertGreaterThanOrEqual((int) DB::table($t)->max($c), $this->sequence($t, $c), "séquence de {$t} en retard sur max({$c})");
        }

        // Une création web avance la séquence : la prochaine insertion du bureau (nextval) ne peut pas collisionner
        $this->post(route('parametre.vendeur.store'), ['nom' => 'VENDEUR TEST SEQ'])->assertSessionHas('success');
        $id = (int) DB::table('employees')->where('nom', 'VENDEUR TEST SEQ')->value('employeeid');
        $this->assertSame($id, $this->sequence('employees', 'employeeid'));
        $suivant = (int) DB::selectOne("SELECT nextval(pg_get_serial_sequence('employees', 'employeeid')) AS v")->v;
        $this->assertGreaterThan($id, $suivant, 'le bureau obtiendrait un identifiant déjà utilisé');

        // Plage réservée en bloc (droits d'un rôle)
        $premier = Identifiants::plage('userdroitdets', 'userdroitdetid', 5);
        $this->assertSame($premier + 4, $this->sequence('userdroitdets', 'userdroitdetid'));
    }

    // ---------------------------------------------------------------- Général

    public function test_general_valeurs_typees_booleens_et_sous_familles(): void
    {
        $param = fn (string $libelle) => DB::table('retailconfigs')->where('libelle', $libelle)->first();
        $entier = $param('perioderetour');          // type 1
        $decimal = $param('tauxticketresto');       // type 3 : 10.000000
        $booleen = $param('isbarcode2');            // type 2 : True
        $minuscule = DB::table('retailconfigs')->where('pagelibelle', 'Général')->whereIn('value', ['true', 'false'])->first();
        $chiffre = $param('tauxacompte');           // type 2 mais valeur 99

        // Texte dans un paramètre numérique : refusé, rien n'est écrit
        $this->post(route('parametre.configuration.general.update'), ['config' => [$entier->retailconfigid => 'abc', $decimal->retailconfigid => 'x']])
            ->assertSessionHasErrors(['config.' . $entier->retailconfigid, 'config.' . $decimal->retailconfigid]);
        $this->assertSame($entier->value, $param('perioderetour')->value);
        $this->post(route('parametre.configuration.general.update'), ['config' => [$chiffre->retailconfigid => 'oui']])
            ->assertSessionHasErrors('config.' . $chiffre->retailconfigid);

        // Valeurs correctes ; décimal à virgule normalisé, décimal inchangé gardé au format du bureau
        $this->post(route('parametre.configuration.general.update'), ['config' => [
            $entier->retailconfigid => '120',
            $decimal->retailconfigid => '12,5',
            $booleen->retailconfigid => 'False',
            $chiffre->retailconfigid => '80',
        ]])->assertSessionHas('success');
        $this->assertSame('120', $param('perioderetour')->value);
        $this->assertSame('12.5', $param('tauxticketresto')->value);
        $this->assertSame('False', $param('isbarcode2')->value, 'casse « False » du bureau conservée');
        $this->assertSame('80', $param('tauxacompte')->value);

        $this->post(route('parametre.configuration.general.update'), ['config' => [$decimal->retailconfigid => '12.5']])->assertSessionHas('success');
        DB::table('retailconfigs')->where('retailconfigid', $decimal->retailconfigid)->update(['value' => '10.000000']);
        $this->post(route('parametre.configuration.general.update'), ['config' => [$decimal->retailconfigid => '10']])->assertSessionHas('success');
        $this->assertSame('10.000000', $param('tauxticketresto')->value);

        if ($minuscule) {
            $this->post(route('parametre.configuration.general.update'), ['config' => [$minuscule->retailconfigid => 'True']])->assertSessionHas('success');
            $this->assertSame('true', DB::table('retailconfigs')->where('retailconfigid', $minuscule->retailconfigid)->value('value'), 'casse « true » du bureau conservée');
        }

        // Paramètre d'une autre page : jamais modifié par cet écran
        $autre = DB::table('retailconfigs')->where('pagelibelle', '!=', 'Général')->first();
        $this->post(route('parametre.configuration.general.update'), ['config' => [$autre->retailconfigid => 'PIRATE']]);
        $this->assertNotSame('PIRATE', DB::table('retailconfigs')->where('retailconfigid', $autre->retailconfigid)->value('value'));

        // Sous-familles fidélité
        $ids = DB::table('sousfamilles')->orderBy('sousfamilleid')->limit(2)->pluck('sousfamilleid')->all();
        $this->post(route('parametre.configuration.general.update'), ['fidelite_section' => 1, 'sousfamilles_fidelite' => $ids])->assertSessionHas('success');
        $this->assertEquals($ids, DB::table('sousfamilles')->where('is_loyalty_enabled', true)->orderBy('sousfamilleid')->pluck('sousfamilleid')->all());
    }

    // ---------------------------------------------------------------- Caisse et libération

    public function test_caisse_creation_validation_poste_unique_suppression_et_liberation(): void
    {
        $stationLibre = DB::table('stations')->whereNotIn('stationid', DB::table('caisses')->whereNotNull('machineid')->pluck('machineid'))->value('stationid');
        $stationPrise = DB::table('caisses')->whereNotNull('machineid')->value('machineid');
        $agence = DB::table('agencebs')->where('siteid', $this->siteid)->value('agencebid');
        $base = ['libelle' => 'CAISSE TEST', 'compteur' => 0, 'numero' => 9, 'siteid' => $this->siteid, 'agencebid' => $agence];

        // Autre site, poste déjà pris, agence inexistante : refusés
        $autreSite = DB::table('sites')->where('siteid', '!=', $this->siteid)->value('siteid');
        $this->post(route('parametre.caisse.store'), ['siteid' => $autreSite] + $base)->assertSessionHasErrors('siteid');
        if ($stationPrise) {
            $this->post(route('parametre.caisse.store'), ['machineid' => $stationPrise] + $base)->assertSessionHasErrors('machineid');
        }
        $this->post(route('parametre.caisse.store'), ['agencebid' => 999999] + $base)->assertSessionHasErrors('agencebid');

        // Création valide
        $this->post(route('parametre.caisse.store'), ['machineid' => $stationLibre, 'bloque' => 'on'] + $base)->assertSessionHas('success');
        $caisse = DB::table('caisses')->where('libelle', 'CAISSE TEST')->first();
        $this->assertTrue((bool) $caisse->bloque);
        $this->assertSame((int) $caisse->caisseid, $this->sequence('caisses', 'caisseid'));

        // Modification en gardant son propre poste (pas de conflit avec elle-même)
        $this->put(route('parametre.caisse.update', $caisse->caisseid), ['machineid' => $stationLibre, 'libelle' => 'CAISSE TEST 2'] + $base)->assertSessionHas('success');
        $this->assertSame('CAISSE TEST 2', DB::table('caisses')->where('caisseid', $caisse->caisseid)->value('libelle'));

        // Libération : la caisse apparaît dans la liste puis perd son poste
        $this->get(route('parametre.caisse.liberation'))->assertSee('CAISSE TEST 2');
        $this->post(route('parametre.caisse.liberer', $caisse->caisseid))->assertSessionHas('success');
        $this->assertNull(DB::table('caisses')->where('caisseid', $caisse->caisseid)->value('machineid'));

        // Une caisse d'un autre site ne peut être ni modifiée ni libérée
        $caisseAutre = DB::table('caisses')->where('siteid', '!=', $this->siteid)->first();
        if ($caisseAutre) {
            $this->post(route('parametre.caisse.liberer', $caisseAutre->caisseid))->assertSessionHas('error');
            $this->assertSame($caisseAutre->machineid, DB::table('caisses')->where('caisseid', $caisseAutre->caisseid)->value('machineid'));
        }

        // Suppression : refusée pour une caisse utilisée (tickets, journées), acceptée pour la caisse de test
        $caisseUtilisee = DB::table('caisses')->where('siteid', $this->siteid)->whereIn('caisseid', DB::table('journalcaisses')->pluck('caisseid'))->value('caisseid');
        if ($caisseUtilisee) {
            $this->delete(route('parametre.caisse.destroy', $caisseUtilisee))->assertSessionHas('error');
            $this->assertTrue(DB::table('caisses')->where('caisseid', $caisseUtilisee)->exists());
        }
        $this->delete(route('parametre.caisse.destroy', $caisse->caisseid))->assertSessionHas('success');
        $this->assertFalse(DB::table('caisses')->where('caisseid', $caisse->caisseid)->exists());
    }

    // ---------------------------------------------------------------- Utilisateurs

    public function test_utilisateur_cree_avec_vendeur_peut_vendre_et_garde_fous(): void
    {
        $vendeur = DB::table('employees')->where('siteid', $this->siteid)->first();
        $vendeurAutreSite = DB::table('employees')->where('siteid', '!=', $this->siteid)->value('employeeid');

        $this->post(route('parametre.utilisateur.store'), ['login' => 'test_param_user', 'password' => '123', 'userdroitid' => 1])->assertSessionHasErrors('password');
        $this->post(route('parametre.utilisateur.store'), ['login' => $this->admin->login, 'password' => 'secret12', 'userdroitid' => 1])->assertSessionHasErrors('login');
        $this->post(route('parametre.utilisateur.store'), ['login' => 'test_param_user', 'password' => 'secret12', 'userdroitid' => 1, 'employeeid' => $vendeurAutreSite])->assertSessionHasErrors('employeeid');

        $this->post(route('parametre.utilisateur.store'), ['login' => 'test_param_user', 'password' => 'secret12', 'userdroitid' => 1, 'employeeid' => $vendeur->employeeid])->assertSessionHas('success');
        $u = DB::table('users')->where('login', 'test_param_user')->first();
        $this->assertSame((int) $this->siteid, (int) $u->siteid);
        $this->assertSame((int) $vendeur->employeeid, (int) $u->employeeid);
        $this->assertSame((int) $this->admin->clientid, (int) $u->clientid, 'rattachement client nécessaire au choix du site');
        $this->assertNotNull($u->agencebid);
        $this->assertSame('secret12', $u->password, 'mot de passe en clair pour le logiciel de bureau');
        $this->assertSame((int) $u->userid, $this->sequence('users', 'userid'));
        $this->get(route('parametre.utilisateur.index'))->assertSee('test_param_user')->assertSee($vendeur->nom);

        // Le nouveau compte peut se connecter et ouvrir l'écran de caisse
        $this->post('/logout');
        $this->post('/login', ['login' => 'test_param_user', 'password' => 'secret12'])->assertRedirect(route('dashboard'));
        $this->assertNotSame('/vente/journee/ouverture', parse_url($this->get(route('vente.caisse.pos'))->headers->get('Location') ?? '', PHP_URL_PATH) ?: '', 'caisse accessible');
        $this->post('/logout');
        $this->actingAs($this->admin);

        // Modification sans mot de passe : l'ancien est conservé
        $this->put(route('parametre.utilisateur.update', $u->userid), ['login' => 'test_param_user2', 'userdroitid' => 1, 'employeeid' => ''])->assertSessionHas('success');
        $u2 = DB::table('users')->where('userid', $u->userid)->first();
        $this->assertSame('secret12', $u2->password);
        $this->assertNull($u2->employeeid);

        // Garde-fous : pas de suppression de soi-même, pas de rôle sans gestion des utilisateurs pour soi-même
        $this->delete(route('parametre.utilisateur.destroy', $this->admin->userid))->assertSessionHas('error');
        $roleSansGestion = (int) DB::table('userdroits')->max('userdroitid') + 1;
        DB::table('userdroits')->insert(['userdroitid' => $roleSansGestion, 'libelle' => 'TEST sans gestion users']);
        DB::table('userdroitdets')->insert(['userdroitdetid' => Identifiants::prochain('userdroitdets', 'userdroitdetid'), 'userdroitid' => $roleSansGestion,
            'typedroitid' => DB::table('typedroits')->where('abreviation', 'gestion.users')->value('typedroitid'), 'bloque' => true, 'badge' => false]);
        $this->put(route('parametre.utilisateur.update', $this->admin->userid), ['login' => $this->admin->login, 'userdroitid' => $roleSansGestion])->assertSessionHas('error');
        $this->assertSame(1, (int) DB::table('users')->where('userid', $this->admin->userid)->value('userdroitid'));

        // Utilisateur avec historique de ventes : non supprimable
        $avecHistorique = DB::table('ctickets')->where('siteid', $this->siteid)->whereNotNull('userid')->value('userid');
        if ($avecHistorique && $avecHistorique != $this->admin->userid) {
            $this->delete(route('parametre.utilisateur.destroy', $avecHistorique))->assertSessionHas('error');
        }

        $this->delete(route('parametre.utilisateur.destroy', $u->userid))->assertSessionHas('success');
        $this->assertFalse(DB::table('users')->where('userid', $u->userid)->exists());
    }

    // ---------------------------------------------------------------- Droits

    public function test_droits_acces_caisse_backoffice_permissions_et_anti_blocage(): void
    {
        $this->post(route('parametre.droit.role.store'), ['libelle' => 'TEST ROLE PARAM'])->assertSessionHas('success');
        $role = DB::table('userdroits')->where('libelle', 'TEST ROLE PARAM')->first();
        $this->assertFalse((bool) $role->notcaisse);
        $this->get(route('parametre.droit.index', ['id' => $role->userdroitid]))->assertOk()->assertSee('Accès à la caisse')->assertSee('Accès au back-office');

        // Caisse seule, sans back-office, et droit « supp.client » bloqué
        $typeSupp = DB::table('typedroits')->where('abreviation', 'supp.client')->value('typedroitid');
        $this->post(route('parametre.droit.permissions.update', $role->userdroitid), [
            'indicateurs_section' => 1, 'acces_caisse' => 1,
            'permissions' => [$typeSupp => ['bloque' => 1]],
        ])->assertSessionHas('success');
        $role = DB::table('userdroits')->where('userdroitid', $role->userdroitid)->first();
        $this->assertFalse((bool) $role->notcaisse);
        $this->assertTrue((bool) $role->notbackoffice);
        $this->assertTrue(DB::table('userdroitdets')->where('userdroitid', $role->userdroitid)->where('typedroitid', $typeSupp)->value('bloque'));

        // Effet réel sur un compte de ce rôle : caisse ouverte, back-office refusé
        $compte = new User();
        foreach (['userid', 'login', 'siteid', 'employeeid', 'clientid'] as $c) {
            $compte->{$c} = $this->admin->{$c};
        }
        $compte->userdroitid = $role->userdroitid;
        $compte->exists = true;
        Droits::viderCache();
        $this->actingAs($compte);
        $this->assertTrue(Droits::autorise('caisse'));
        $this->assertFalse(Droits::autorise('backoffice'));
        $this->get(route('stock.articles.index'))->assertStatus(403);
        $this->actingAs($this->admin);
        Droits::viderCache();

        // Anti-blocage : l'administrateur ne peut pas retirer le back-office ni la gestion des droits à son propre rôle
        $typeGestion = DB::table('typedroits')->where('abreviation', 'gestion.droits')->value('typedroitid');
        $this->post(route('parametre.droit.permissions.update', 1), ['indicateurs_section' => 1, 'acces_caisse' => 1])->assertSessionHas('error');
        $this->post(route('parametre.droit.permissions.update', 1), ['indicateurs_section' => 1, 'acces_caisse' => 1, 'acces_backoffice' => 1, 'permissions' => [$typeGestion => ['bloque' => 1]]])->assertSessionHas('error');
        $this->assertFalse((bool) DB::table('userdroits')->where('userdroitid', 1)->value('notbackoffice'));

        // Rôle attribué : non supprimable ; rôle libre : supprimé avec ses droits
        $this->delete(route('parametre.droit.role.destroy', 1))->assertSessionHas('error');
        $this->delete(route('parametre.droit.role.destroy', $role->userdroitid))->assertSessionHas('success');
        $this->assertFalse(DB::table('userdroitdets')->where('userdroitid', $role->userdroitid)->exists());
    }

    // ---------------------------------------------------------------- Vendeurs

    public function test_vendeurs_crees_proposes_en_caisse_et_regles(): void
    {
        $this->post(route('parametre.vendeur.store'), ['nom' => 'VENDEUR TEST CAISSE', 'tauxcommission' => 150])->assertSessionHasErrors('tauxcommission');
        $this->post(route('parametre.vendeur.store'), ['nom' => 'VENDEUR TEST CAISSE', 'tauxcommission' => 5])->assertSessionHas('success');
        $v = DB::table('employees')->where('nom', 'VENDEUR TEST CAISSE')->first();
        $this->assertSame($this->siteid, (int) $v->siteid);
        $this->assertTrue((bool) $v->isvendeur);
        $this->assertSame(str_pad((string) $v->employeeid, 3, '0', STR_PAD_LEFT), $v->code);

        // Le vendeur créé est proposé dans la caisse ; bloqué, il disparaît
        $this->getJson(route('vente.caisse.pos.search_vendeurs'))->assertOk()->assertJsonFragment(['nom' => 'VENDEUR TEST CAISSE']);
        $this->put(route('parametre.vendeur.update', $v->employeeid), ['nom' => 'VENDEUR TEST CAISSE', 'tauxcommission' => 5, 'bloque' => 'on'])->assertSessionHas('success');
        $this->getJson(route('vente.caisse.pos.search_vendeurs'))->assertJsonMissing(['nom' => 'VENDEUR TEST CAISSE']);

        // Un vendeur bloqué ou d'un autre site est refusé à la validation du ticket
        $journal = DB::table('journalcaisses')->where('isclosed', false)->where('siteid', $this->siteid)->first();
        if ($journal) {
            $produit2 = DB::table('produit2s')->first();
            $ticket = fn ($vendeurid) => $this->postJson(route('vente.caisse.store'), [
                'clientid' => 1, 'vendeurid' => $vendeurid, 'en_attente' => true,
                'lignes' => [['produit2id' => $produit2->produit2id, 'qte' => 1, 'prix' => 10, 'prixNet' => 10, 'remise' => 0, 'total' => 10]],
            ]);
            $ticket($v->employeeid)->assertJson(['success' => false]);
            $autre = DB::table('employees')->where('siteid', '!=', $this->siteid)->value('employeeid');
            $ticket($autre)->assertJson(['success' => false]);
            $this->put(route('parametre.vendeur.update', $v->employeeid), ['nom' => 'VENDEUR TEST CAISSE', 'tauxcommission' => 5]);
            $ticket($v->employeeid)->assertJson(['success' => true]);
        }

        // Rôle avec « choix.vendeur » bloqué : seul le vendeur du compte est proposé
        $role = (int) DB::table('userdroits')->max('userdroitid') + 1;
        DB::table('userdroits')->insert(['userdroitid' => $role, 'libelle' => 'TEST choix vendeur']);
        DB::table('userdroitdets')->insert(['userdroitdetid' => Identifiants::prochain('userdroitdets', 'userdroitdetid'), 'userdroitid' => $role,
            'typedroitid' => DB::table('typedroits')->where('abreviation', 'choix.vendeur')->value('typedroitid'), 'bloque' => true, 'badge' => false]);
        $compte = User::find($this->admin->userid);
        $compte->userdroitid = $role;
        Droits::viderCache();
        $this->actingAs($compte);
        $noms = collect($this->getJson(route('vente.caisse.pos.search_vendeurs'))->json())->pluck('employeeid')->all();
        $this->assertEquals([(int) $this->admin->employeeid], array_map('intval', $noms));
        $this->actingAs($this->admin);
        Droits::viderCache();

        // Suppression : vendeur sans historique supprimé, avec historique seulement bloqué, le sien refusé
        $this->delete(route('parametre.vendeur.destroy', $this->admin->employeeid))->assertSessionHas('error');
        $avecHistorique = DB::table('employees')->where('siteid', $this->siteid)->whereIn('employeeid', DB::table('ctickets')->pluck('employeeid'))->where('employeeid', '!=', $this->admin->employeeid)->value('employeeid');
        if ($avecHistorique) {
            $this->delete(route('parametre.vendeur.destroy', $avecHistorique))->assertSessionHas('success');
            $this->assertTrue((bool) DB::table('employees')->where('employeeid', $avecHistorique)->value('bloque'));
        }
        if (!DB::table('ctickets')->where('employeeid', $v->employeeid)->exists()) {
            $this->delete(route('parametre.vendeur.destroy', $v->employeeid))->assertSessionHas('success');
            $this->assertFalse(DB::table('employees')->where('employeeid', $v->employeeid)->exists());
        }
    }

    // ---------------------------------------------------------------- Contrôle des données et impression

    public function test_controle_des_donnees_et_impression_coherents_avec_la_base(): void
    {
        $page = $this->get(route('parametre.controle.index'))->assertOk();
        $controles = collect($page->viewData('controles'))->keyBy('titre');
        $restes = DB::table('ctickets')->where('siteid', $this->siteid)->where('enattente', false)->where('netapayer', '>', 0.001)->count();
        $this->assertEquals($restes, $controles['Tickets avec un reste à payer']['total']);
        $this->assertEquals(DB::table('stock2s')->where('siteid', $this->siteid)->where('qtestock', '<', 0)->count(), $page->viewData('stockNegatif'));

        // Impression : chaque caisse du site affichée avec l'imprimante du bureau
        $page = $this->get(route('parametre.impression.index'))->assertOk();
        foreach (DB::table('caisses')->where('siteid', $this->siteid)->get() as $caisse) {
            $page->assertSee($caisse->libelle);
            $imprimante = DB::table('caisseconfigs')->where('caisseid', $caisse->caisseid)->where('libelle', 'ticketprinter')->value('value');
            if ($imprimante) {
                $page->assertSee($imprimante);
            }
        }
    }
}
