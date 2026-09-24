<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use App\Models\User;

/**
 * Tests de non-régression des corrections du module Vente (caisse).
 */
class VenteCorrectionsTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $produit2;

    protected function setUp(): void
    {
        parent::setUp();

        $userRecord = DB::table('users')->orderBy('userid')->first();
        $this->produit2 = DB::table('produit2s')->first();
        if (!$userRecord || !$this->produit2) {
            $this->markTestSkipped('Données insuffisantes (users / produit2s).');
        }

        $this->user = User::find($userRecord->userid);
        $this->actingAs($this->user);

        $journalExists = DB::table('journalcaisses')
            ->where('userid', $this->user->userid)
            ->where('isclosed', 0)
            ->exists();
        if (!$journalExists) {
            DB::table('journalcaisses')->insert([
                'journalcaisseid' => DB::table('journalcaisses')->max('journalcaisseid') + 1,
                'caisseid' => DB::table('caisses')->value('caisseid') ?? 1,
                'fondcaisse' => 0,
                'dateouverture' => now(),
                'userid' => $this->user->userid,
                'employeeid' => $this->user->employeeid ?? 1,
                'caissierclotureid' => 0,
                'siteid' => $this->user->siteid,
                'isclosed' => 0,
                'montantcloture' => 0,
                'montanttheorique' => 0,
                'envoyee' => false,
                'agencebid' => 1,
            ]);
        }

        DB::statement("SELECT setval('creglements_creglementid_seq', coalesce(max(creglementid), 1), max(creglementid) IS NOT null) FROM creglements;");
    }

    private function ticketPayload(float $total, array $reglements, bool $enAttente = false): array
    {
        return [
            'clientid' => 1,
            'vendeurid' => $this->user->employeeid ?? 1,
            'en_attente' => $enAttente,
            'lignes' => [[
                'produit2id' => $this->produit2->produit2id,
                'qte' => 1,
                'prix' => $total,
                'prixNet' => $total,
                'remise' => 0,
                'total' => $total,
            ]],
            'reglements' => $reglements,
        ];
    }

    public function test_complement_acompte_n_est_pas_compte_deux_fois()
    {
        // Ticket de 100 avec un acompte de 30
        $res = $this->postJson(route('vente.caisse.store'), $this->ticketPayload(100, [
            ['modereglementid' => 1, 'montant' => 30],
        ]))->assertJson(['success' => true]);
        $ticketId = $res->json('cticketid');

        $this->assertEqualsWithDelta(70, DB::table('ctickets')->where('cticketid', $ticketId)->value('netapayer'), 0.001);

        // Complément de 70 : le ticket doit être soldé (acompte 100, reste 0)
        $this->postJson(route('vente.caisse.store_complement_acompte'), [
            'cticketid' => $ticketId,
            'reglements' => [['modereglementid' => 1, 'montant' => 70]],
        ])->assertJson(['success' => true, 'nouveau_reste' => 0]);

        $ticket = DB::table('ctickets')->where('cticketid', $ticketId)->first();
        $this->assertEqualsWithDelta(100, $ticket->acompte, 0.001);
        $this->assertEqualsWithDelta(0, $ticket->netapayer, 0.001);

        // Un nouveau complément ne doit rien enregistrer (pas de règlement négatif)
        $this->postJson(route('vente.caisse.store_complement_acompte'), [
            'cticketid' => $ticketId,
            'reglements' => [['modereglementid' => 1, 'montant' => 50]],
        ])->assertJson(['success' => false]);
        $this->assertEquals(0, DB::table('creglements')->where('documentid', $ticketId)->where('typeid', 1)->where('montant', '<=', 0)->count());
    }

    public function test_reprise_refuse_un_ticket_paye_et_les_requetes_get()
    {
        $res = $this->postJson(route('vente.caisse.store'), $this->ticketPayload(50, [
            ['modereglementid' => 1, 'montant' => 50],
        ]))->assertJson(['success' => true]);
        $ticketId = $res->json('cticketid');

        // GET n'est plus autorisé (évite la suppression via un simple lien)
        $this->get('/vente/caisse/reprise/' . $ticketId)->assertStatus(405);

        // Un ticket payé ne peut pas être repris (donc pas supprimé)
        $this->postJson('/vente/caisse/reprise/' . $ticketId)->assertJson(['success' => false]);
        $this->assertTrue(DB::table('ctickets')->where('cticketid', $ticketId)->exists());
    }

    public function test_reprise_ticket_en_attente_conserve_la_remise()
    {
        $payload = $this->ticketPayload(0, [], true);
        $payload['lignes'][0] = array_merge($payload['lignes'][0], [
            'qte' => 2, 'prix' => 100, 'remise' => 30, 'prixNet' => 70, 'total' => 140,
        ]);
        $res = $this->postJson(route('vente.caisse.store'), $payload)->assertJson(['success' => true]);

        $ligne = $this->postJson('/vente/caisse/reprise/' . $res->json('cticketid'))
            ->assertJson(['success' => true])
            ->json('lignes.0');

        $this->assertEqualsWithDelta(100, $ligne['prix'], 0.001);
        $this->assertEqualsWithDelta(70, $ligne['prixNet'], 0.001);
        $this->assertEqualsWithDelta(140, $ligne['total'], 0.001);
        $this->assertFalse(DB::table('ctickets')->where('cticketid', $res->json('cticketid'))->exists());
    }

    public function test_ticket_en_attente_ne_modifie_pas_le_stock()
    {
        $stock = fn () => (float) DB::table('stock2s')
            ->where('produit2id', $this->produit2->produit2id)
            ->where('siteid', $this->user->siteid)
            ->value('qtestock');
        $avant = $stock();

        $res = $this->postJson(route('vente.caisse.store'), $this->ticketPayload(25, [], true))->assertJson(['success' => true]);
        $ticketId = $res->json('cticketid');

        $this->assertEquals($avant, $stock(), 'Un ticket en attente ne doit pas décrémenter le stock');
        $this->assertEquals(0, DB::table('detctickets')->where('cticketid', $ticketId)->count());
        $this->assertEqualsWithDelta(25, DB::table('ctickets')->where('cticketid', $ticketId)->value('totalttc'), 0.001);

        // La reprise restitue bien la ligne
        $ligne = $this->postJson('/vente/caisse/reprise/' . $ticketId)->assertJson(['success' => true])->json('lignes.0');
        $this->assertEquals($this->produit2->produit2id, $ligne['produit2id']);
        $this->assertEqualsWithDelta(25, $ligne['total'], 0.001);
    }

    public function test_reglement_superieur_au_total_refuse()
    {
        $this->postJson(route('vente.caisse.store'), $this->ticketPayload(40, [
            ['modereglementid' => 1, 'montant' => 60],
        ]))->assertJson(['success' => false]);
    }

    public function test_numeros_de_ticket_uniques()
    {
        $a = $this->postJson(route('vente.caisse.store'), $this->ticketPayload(10, [['modereglementid' => 1, 'montant' => 10]]))->json('cticketnumero');
        $b = $this->postJson(route('vente.caisse.store'), $this->ticketPayload(10, [['modereglementid' => 1, 'montant' => 10]]))->json('cticketnumero');

        $this->assertNotEquals($a, $b);
        $this->assertEquals(1, DB::table('ctickets')->where('cticketnumero', $b)->count());
    }

    public function test_bon_achat_utilisable_une_seule_fois()
    {
        $bonId = DB::table('bons_achat')->insertGetId([
            'clientid' => 1,
            'montant' => 20,
            'date_emission' => now(),
            'date_expiration' => now()->addMonth(),
            'utilise' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $reglements = [['modereglementid' => 10, 'montant' => 20, 'numero' => (string) $bonId]];
        $this->postJson(route('vente.caisse.store'), $this->ticketPayload(20, $reglements))->assertJson(['success' => true]);
        $this->assertTrue((bool) DB::table('bons_achat')->where('id', $bonId)->value('utilise'));

        $this->postJson(route('vente.caisse.store'), $this->ticketPayload(20, $reglements))->assertJson(['success' => false]);
    }
}
