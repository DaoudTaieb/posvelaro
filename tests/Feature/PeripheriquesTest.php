<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\FideliteService;
use App\Services\ImpressionTicketService;
use App\Support\CodeBarre;
use App\Support\Droits;
use App\Support\RetailConfig;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;

/**
 * Périphériques de caisse : douchette (lecture des codes, clavier QWERTY sur poste AZERTY,
 * carte fidélité, bon d'achat), imprimantes Windows du poste, tiroir-caisse et page de réglages.
 */
class PeripheriquesTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;
    private int $siteid;

    /** @var resource|null serveur TCP local jouant le rôle d'imprimante réseau */
    private $serveur = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::find(DB::table('users')->where('userdroitid', 1)->where('siteid', 102)->value('userid')
            ?? DB::table('users')->where('userdroitid', 1)->value('userid'));
        $this->siteid = (int) $this->admin->siteid;
        $this->actingAs($this->admin);
        Droits::viderCache();
        RetailConfig::vider();
    }

    protected function tearDown(): void
    {
        if ($this->serveur) {
            fclose($this->serveur);
        }
        parent::tearDown();
    }

    /** Ce qu'une douchette QWERTY tape sur un poste AZERTY pour un code donné. */
    private function enQwertySurAzerty(string $code): string
    {
        return strtr($code, [
            '1' => '&', '2' => 'é', '3' => '"', '4' => "'", '5' => '(', '6' => '-', '7' => 'è', '8' => '_', '9' => 'ç', '0' => 'à',
            'A' => 'Q', 'Q' => 'A', 'Z' => 'W', 'W' => 'Z',
        ]);
    }

    // ---------------------------------------------------------------- Lecture des codes

    public function test_codes_qwerty_sur_poste_azerty_relus_correctement(): void
    {
        $this->assertSame('123456', CodeBarre::nettoyer("123456\r\n"));
        $this->assertSame('ABC', CodeBarre::nettoyer("\tABC\t"));
        $this->assertSame([], CodeBarre::variantes("  \r\n"));

        // Code numérique (EAN) : la rangée des chiffres AZERTY est relue en chiffres
        $this->assertSame('-&ç&é"\'(-è_çà', $this->enQwertySurAzerty('6191234567890'));
        $this->assertContains('6191234567890', CodeBarre::variantes($this->enQwertySurAzerty('6191234567890')));
        $this->assertTrue(CodeBarre::semblePermute('&é"'));
        $this->assertFalse(CodeBarre::semblePermute('123456'));

        // Bon d'achat « BA000123 » tapé « BQàààé" » : chiffres puis lettres A/Q corrigés
        $this->assertContains('BA000123', CodeBarre::variantes($this->enQwertySurAzerty('BA000123')));

        // Le code reçu reste toujours le premier essayé (une vraie référence avec tiret n'est pas modifiée)
        $this->assertSame('REF-12', CodeBarre::variantes('REF-12')[0]);
    }

    public function test_douchette_en_caisse_article_carte_fidelite_bon_et_code_inconnu(): void
    {
        // Article : code tel quel, puis code tapé par une douchette QWERTY
        $produit = DB::table('produit2s')->join('produits', 'produits.produitid', '=', 'produit2s.produitid')
            ->whereRaw("produits.produitcode ~ '^[0-9]{4,}$'")
            ->select('produits.produitcode', 'produits.produitid')->first();
        if (!$produit) {
            $this->markTestSkipped('Aucun article avec un code numérique.');
        }
        $this->getJson(route('vente.caisse.pos.scanner', ['code' => $produit->produitcode]))
            ->assertOk()->assertJson(['type' => 'produit', 'code' => $produit->produitcode, 'corrige' => false])
            ->assertJsonPath('produit.produitid', $produit->produitid);
        $this->getJson(route('vente.caisse.pos.scanner', ['code' => $this->enQwertySurAzerty($produit->produitcode)]))
            ->assertOk()->assertJson(['type' => 'produit', 'code' => $produit->produitcode, 'corrige' => true]);

        // Carte fidélité : n° de carte = clients.barcode
        $client = DB::table('clients')->where('clientid', '>', 1)->orderBy('clientid')->first();
        $carte = 'TSTCARTE' . random_int(100000, 999999);
        DB::table('clients')->where('clientid', $client->clientid)->update(['barcode' => $carte]);
        $this->getJson(route('vente.caisse.pos.scanner', ['code' => $carte]))
            ->assertOk()->assertJson(['type' => 'client'])
            ->assertJsonPath('client.clientid', $client->clientid)
            ->assertJsonStructure(['client' => ['loyalty_tier', 'num_fidelite', 'bons_disponibles']]);

        // Bon d'achat Golden Pos, même lu en QWERTY
        $ticket = DB::table('ctickets')->orderByDesc('cticketid')->value('cticketid');
        $id = DB::table('bons_achat')->insertGetId([
            'clientid' => $client->clientid, 'montant' => 10, 'date_emission' => now()->toDateString(),
            'date_expiration' => now()->addMonths(3)->toDateString(), 'utilise' => false, 'annule' => false,
            'ticketid_source' => $ticket, 'created_at' => now(), 'updated_at' => now(),
        ], 'id');
        $numero = app(FideliteService::class)->numeroBon($id);
        $this->getJson(route('vente.caisse.pos.scanner', ['code' => $this->enQwertySurAzerty($numero)]))
            ->assertOk()->assertJson(['type' => 'bon', 'code' => $numero, 'bon' => ['numero' => $numero, 'utilisable' => true]]);

        // Un nombre seul n'est jamais pris pour un bon (il reste un article inconnu)
        $this->getJson(route('vente.caisse.pos.scanner', ['code' => (string) $id . '999999999']))
            ->assertOk()->assertJson(['type' => null]);
        $this->getJson(route('vente.caisse.pos.scanner', ['code' => 'ZZINCONNU' . random_int(1000, 9999)]))
            ->assertOk()->assertJson(['type' => null]);

        // L'ancien point d'entrée reste disponible (choix d'article, consultation)
        $this->getJson(route('vente.caisse.pos.scan_product', ['code' => $produit->produitcode]))
            ->assertOk()->assertJsonPath('produitid', $produit->produitid);
    }

    // ---------------------------------------------------------------- Imprimantes et tiroir

    public function test_liste_des_imprimantes_windows_du_poste(): void
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            $this->markTestSkipped('Liste des imprimantes Windows : poste Windows uniquement.');
        }
        Cache::flush();
        Process::fake(['*' => Process::result(json_encode([
            ['nom' => 'XP-80C', 'pilote' => 'XP-80', 'port' => 'USB001', 'etat' => 'pret', 'defaut' => true, 'reseau' => false],
            ['nom' => 'EPSON TM-T20II Receipt', 'pilote' => 'EPSON', 'port' => 'ESDPRT001', 'etat' => 'hors_ligne', 'defaut' => false, 'reseau' => false],
        ]))]);

        $this->getJson(route('parametre.impression.imprimantes'))
            ->assertOk()
            ->assertJson(['disponible' => true])
            ->assertJsonPath('imprimantes.0.nom', 'EPSON TM-T20II Receipt')
            ->assertJsonPath('imprimantes.0.etat', 'hors_ligne')
            ->assertJsonPath('imprimantes.1.nom', 'XP-80C');
        // Variables Windows transmises explicitement : dans une requête web, PowerShell échouait sans SystemRoot
        Process::assertRan(fn ($process) => str_contains(implode(' ', (array) $process->command), 'liste-imprimantes.ps1')
            && !empty($process->environment['SystemRoot']));

        // Une seule imprimante : PowerShell renvoie un objet, pas une liste
        Process::fake(['*' => Process::result(json_encode(['nom' => 'XP-80C', 'pilote' => 'XP-80', 'port' => 'USB001', 'etat' => 'pret', 'defaut' => true, 'reseau' => false]))]);
        $this->getJson(route('parametre.impression.imprimantes', ['actualiser' => 1]))
            ->assertOk()->assertJsonCount(1, 'imprimantes')->assertJsonPath('imprimantes.0.nom', 'XP-80C');

        // PowerShell en erreur : la page l'indique au lieu de planter
        Process::fake(['*' => Process::result('', 'Accès refusé', 1)]);
        $this->getJson(route('parametre.impression.imprimantes', ['actualiser' => 1]))
            ->assertOk()->assertJson(['disponible' => false, 'imprimantes' => []]);
    }

    public function test_tiroir_caisse_et_ticket_de_test(): void
    {
        $caisseid = (int) DB::table('caisses')->where('siteid', $this->siteid)->value('caisseid');
        if (!$caisseid) {
            $this->markTestSkipped('Aucune caisse sur le site.');
        }
        $impression = app(ImpressionTicketService::class);

        // Mode navigateur : le tiroir ne peut pas être commandé
        $impression->enregistrerConfiguration($caisseid, ['mode' => 'navigateur', 'colonnes' => 48]);
        $this->post(route('parametre.impression.tiroir', $caisseid))->assertSessionHas('error');

        // Imprimante réseau locale : la commande d'ouverture ESC p arrive à l'imprimante
        $this->serveur = stream_socket_server('tcp://127.0.0.1:0', $code, $erreur);
        $impression->enregistrerConfiguration($caisseid, ['mode' => 'reseau', 'reseau' => stream_socket_get_name($this->serveur, false), 'colonnes' => 48]);
        $this->post(route('parametre.impression.tiroir', $caisseid))->assertSessionHas('success');
        $client = stream_socket_accept($this->serveur, 2);
        $recu = stream_get_contents($client);
        fclose($client);
        $this->assertStringContainsString("\x1Bp\x00", $recu);

        // Mode Windows : envoi par le script du spouleur, message d'erreur lisible si l'imprimante manque
        $impression->enregistrerConfiguration($caisseid, ['mode' => 'windows', 'imprimante' => 'XP-80C', 'colonnes' => 48]);
        Process::fake(['*' => Process::result('', 'Imprimante introuvable : XP-80C (code 1801)', 1)]);
        $this->post(route('parametre.impression.test', $caisseid))->assertSessionHas('error', 'Impression impossible : Imprimante introuvable : XP-80C (code 1801)');
        Process::fake();
        $this->post(route('parametre.impression.test', $caisseid))->assertSessionHas('success');
        Process::assertRan(fn ($process) => str_contains(implode(' ', (array) $process->command), 'impression-brute.ps1')
            && in_array('XP-80C', (array) $process->command, true));
    }

    // ---------------------------------------------------------------- Page de réglages

    public function test_page_imprimantes_et_douchettes_et_test_de_douchette(): void
    {
        $this->get(route('parametre.impression.index'))
            ->assertOk()
            ->assertSee('Imprimantes et douchettes')
            ->assertSee('Imprimantes installées sur ce poste')
            ->assertSee('Test de la douchette')
            ->assertSee('Ouvrir le tiroir-caisse')
            ->assertSee('list="imprimantesWindows"', false);

        $produit = DB::table('produits')->whereRaw("produitcode ~ '^[0-9]{4,}$'")->first(['produitcode', 'produitlibelle']);
        if ($produit) {
            $this->getJson(route('parametre.impression.douchette', ['code' => $this->enQwertySurAzerty($produit->produitcode)]))
                ->assertOk()
                ->assertJson(['type' => 'produit', 'code' => $produit->produitcode, 'corrige' => true, 'qwerty' => true]);
        }
        $this->getJson(route('parametre.impression.douchette', ['code' => '']))->assertOk()->assertJson(['type' => null]);

        // Réglages réservés au droit « config.caisses » : la caisse seule n'y a pas accès
        $role = DB::table('userdroits')->insertGetId(['userdroitid' => \App\Support\Identifiants::prochain('userdroits', 'userdroitid'), 'libelle' => 'TEST CAISSIER PERIPH'], 'userdroitid');
        $type = DB::table('typedroits')->where('abreviation', 'config.caisses')->value('typedroitid');
        if ($type) {
            DB::table('userdroitdets')->insert(['userdroitdetid' => \App\Support\Identifiants::prochain('userdroitdets', 'userdroitdetid'), 'userdroitid' => $role, 'typedroitid' => $type, 'bloque' => true, 'badge' => false]);
            $this->admin->userdroitid = $role;
            $this->actingAs($this->admin);
            Droits::viderCache();
            $this->getJson(route('parametre.impression.douchette', ['code' => '123']))->assertForbidden();
        }
    }
}
