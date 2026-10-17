<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\ImpressionTicketService;
use App\Support\EscPos;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;

/**
 * Impression thermique ESC/POS : réglages par caisse (caisseconfigs), envoi réseau (port 9100),
 * envoi Windows (spouleur, simulé), impression automatique à la validation et réimpression.
 */
class ImpressionTicketTest extends TestCase
{
    use DatabaseTransactions;

    private ImpressionTicketService $impression;
    private object $journal;
    private User $user;

    /** @var resource|null serveur TCP local jouant le rôle d'imprimante réseau */
    private $serveur = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->impression = app(ImpressionTicketService::class);
        $this->journal = DB::table('journalcaisses')->where('isclosed', false)->orderByDesc('journalcaisseid')->first();
        if (!$this->journal) {
            $this->markTestSkipped('Aucune journée de caisse ouverte.');
        }
        $this->user = User::find($this->journal->userid);
        $this->actingAs($this->user);
    }

    protected function tearDown(): void
    {
        if ($this->serveur) {
            fclose($this->serveur);
        }
        parent::tearDown();
    }

    /** Démarre une « imprimante réseau » locale et règle la caisse de la journée dessus. */
    private function imprimanteReseau(): void
    {
        $this->serveur = stream_socket_server('tcp://127.0.0.1:0', $code, $erreur);
        $this->assertNotFalse($this->serveur, $erreur);
        $adresse = stream_socket_get_name($this->serveur, false);
        $this->impression->enregistrerConfiguration((int) $this->journal->caisseid, [
            'mode' => 'reseau', 'reseau' => $adresse, 'colonnes' => 48, 'accents' => false, 'tiroir' => true,
        ]);
    }

    /** Lit ce que l'« imprimante » a reçu. */
    private function recu(): string
    {
        $client = stream_socket_accept($this->serveur, 2);
        $this->assertNotFalse($client, 'Aucune connexion reçue par l\'imprimante réseau.');
        stream_set_timeout($client, 2);
        $donnees = stream_get_contents($client);
        fclose($client);

        return (string) $donnees;
    }

    private function ticketPayload(float $total, array $reglements): array
    {
        $produit2 = DB::table('produit2s')->first();

        return [
            'clientid' => 1, 'vendeurid' => $this->user->employeeid, 'en_attente' => false,
            'lignes' => [['produit2id' => $produit2->produit2id, 'qte' => 1, 'prix' => $total, 'prixNet' => $total, 'remise' => 0, 'total' => $total]],
            'reglements' => $reglements,
        ];
    }

    public function test_generateur_escpos(): void
    {
        $p = (new EscPos(32, false))->alignement(1)->gras()->ligne('Été à Sfax : 10 €')->codeBarres('BA000123')->tiroir()->couper();
        $flux = $p->flux();

        $this->assertStringStartsWith("\x1B@", $flux, 'initialisation ESC @');
        $this->assertStringContainsString('Ete a Sfax : 10 EUR', $flux, 'translittération sans accents');
        $this->assertStringContainsString("\x1Dk" . chr(73) . chr(10) . '{BBA000123', $flux, 'code-barres CODE128');
        $this->assertStringContainsString("\x1Bp\x00", $flux, 'ouverture du tiroir');
        $this->assertStringEndsWith("\x1DV" . chr(66) . chr(3), $flux, 'coupe du papier');

        $accents = (new EscPos(48, true))->ligne('é')->flux();
        $this->assertStringContainsString("\x1Bt" . chr(19), $accents, 'page de code PC858');
        $this->assertStringContainsString(chr(0x82), $accents, 'é en PC858');

        $ligne = (new EscPos(20))->gaucheDroite('Article très long qui déborde', '12.500')->flux();
        $this->assertStringContainsString("Article tres  12.500\n", $ligne, 'alignement gauche/droite sur 20 colonnes');
        $total = (new EscPos(48))->gaucheDroite('TOTAL', '25.000', 2)->flux();
        $this->assertStringContainsString('TOTAL' . str_repeat(' ', 13) . "25.000\n", $total, 'double largeur : 24 colonnes');
    }

    public function test_reglages_par_caisse_dans_caisseconfigs(): void
    {
        $caisseid = (int) $this->journal->caisseid;
        $imprimanteBureau = DB::table('caisseconfigs')->where('caisseid', $caisseid)->where('libelle', 'ticketprinter')->value('value');

        // Par défaut (aucun réglage Golden Pos) : impression navigateur, imprimante du bureau reprise
        DB::table('caisseconfigs')->where('caisseid', $caisseid)->where('libelle', 'like', 'goldenpos_%')->delete();
        $c = $this->impression->configuration($caisseid);
        $this->assertSame('navigateur', $c['mode']);
        $this->assertSame((string) $imprimanteBureau, $c['imprimante']);

        // Un nom d'imprimante vide ne doit jamais effacer le réglage du logiciel de bureau
        $this->impression->enregistrerConfiguration($caisseid, ['mode' => 'windows', 'imprimante' => '', 'colonnes' => 32, 'accents' => true]);
        $c = $this->impression->configuration($caisseid);
        $this->assertSame('windows', $c['mode']);
        $this->assertSame(32, $c['colonnes']);
        $this->assertTrue($c['accents']);
        $this->assertSame((string) $imprimanteBureau, DB::table('caisseconfigs')->where('caisseid', $caisseid)->where('libelle', 'ticketprinter')->value('value'));
        $this->assertEquals(5, DB::table('caisseconfigs')->where('caisseid', $caisseid)->where('libelle', 'like', 'goldenpos_%')->count());
    }

    public function test_vente_imprimee_directement_sur_imprimante_reseau(): void
    {
        $this->imprimanteReseau();

        $res = $this->postJson(route('vente.caisse.store'), $this->ticketPayload(25, [['modereglementid' => 1, 'montant' => 25]]))
            ->assertJson(['success' => true, 'impression' => ['ok' => true, 'mode' => 'reseau']]);

        $flux = $this->recu();
        $numero = (string) $res->json('cticketnumero');
        $this->assertStringContainsString('TICKET No ' . $numero, $flux);
        $this->assertStringContainsString('25.000', $flux);
        $this->assertStringContainsString("\x1Bp\x00", $flux, 'paiement en espèces : tiroir ouvert');
        $this->assertStringEndsWith("\x1DV" . chr(66) . chr(3), $flux);
    }

    public function test_imprimante_injoignable_la_vente_reste_enregistree(): void
    {
        $this->impression->enregistrerConfiguration((int) $this->journal->caisseid, ['mode' => 'reseau', 'reseau' => '127.0.0.1:1', 'colonnes' => 48]);

        $res = $this->postJson(route('vente.caisse.store'), $this->ticketPayload(15, [['modereglementid' => 1, 'montant' => 15]]))
            ->assertJson(['success' => true, 'impression' => ['ok' => false, 'mode' => 'reseau']]);
        $this->assertStringContainsString('injoignable', $res->json('impression.message'));
        $this->assertNotNull($res->json('print_url'), 'repli sur le reçu navigateur');
        $this->assertTrue(DB::table('ctickets')->where('cticketid', $res->json('cticketid'))->exists());
    }

    public function test_mode_windows_passe_par_le_spouleur(): void
    {
        Process::fake(['*' => Process::result(output: '', exitCode: 0)]);
        $this->impression->enregistrerConfiguration((int) $this->journal->caisseid, ['mode' => 'windows', 'imprimante' => 'EPSON TM-T20II Receipt', 'colonnes' => 48]);

        $res = $this->postJson(route('vente.caisse.store'), $this->ticketPayload(10, [['modereglementid' => 4, 'montant' => 10]]))
            ->assertJson(['success' => true, 'impression' => ['ok' => true, 'mode' => 'windows']]);

        Process::assertRan(fn ($process) => in_array('EPSON TM-T20II Receipt', (array) $process->command, true)
            && in_array('-Imprimante', (array) $process->command, true));
        $this->assertNotNull($res->json('cticketid'));

        // Erreur du spouleur : message renvoyé à la caisse
        Process::fake(['*' => Process::result(errorOutput: 'Imprimante introuvable : EPSON', exitCode: 1)]);
        $r = $this->impression->imprimerTicket((int) $res->json('cticketid'));
        $this->assertFalse($r['ok']);
        $this->assertStringContainsString('Imprimante introuvable', $r['message']);
    }

    public function test_reimpression_et_ecran_de_parametrage(): void
    {
        $this->imprimanteReseau();
        $ticket = DB::table('ctickets')->where('siteid', $this->journal->siteid)->where('enattente', false)->orderByDesc('cticketid')->first();

        $this->postJson(route('vente.caisse.imprimer', $ticket->cticketid))->assertJson(['ok' => true]);
        $this->assertStringContainsString('TICKET No ' . $ticket->cticketnumero, $this->recu());

        // Écran Paramètres > Imprimantes et douchettes, enregistrement et ticket de test
        $this->get(route('parametre.impression.index'))->assertOk()->assertSee('Imprimantes et douchettes');
        $adresse = stream_socket_get_name($this->serveur, false);
        $this->put(route('parametre.impression.update', $this->journal->caisseid), ['mode' => 'reseau', 'reseau' => $adresse, 'colonnes' => 42])
            ->assertRedirect(route('parametre.impression.index'));
        $this->assertSame(42, $this->impression->configuration((int) $this->journal->caisseid)['colonnes']);
        $this->post(route('parametre.impression.test', $this->journal->caisseid))->assertSessionHas('success');
        $this->assertStringContainsString('Ticket de test', $this->recu());

        $this->put(route('parametre.impression.update', $this->journal->caisseid), ['mode' => 'windows', 'imprimante' => '', 'colonnes' => 48])
            ->assertSessionHasErrors('imprimante');
    }
}
