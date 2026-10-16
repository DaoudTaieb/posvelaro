<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\JourneeCaisseService;
use App\Support\TaxeProduit;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Phase 1 : fiabilité de la caisse.
 *  - règlements écrits au format du logiciel de bureau (creglements.documentid = 0 + creglementdets) ;
 *  - reprise d'un ticket en attente sans perte (suppression seulement à la validation) ;
 *  - journée ouverte unique pour la caisse, l'état et la clôture ;
 *  - site obligatoire, client et vendeur validés, TVA réelle sur les transferts.
 */
class FiabiliteCaisseTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;
    private object $produit2;
    private object $journal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->journal = DB::table('journalcaisses')
            ->where(fn ($q) => $q->where('isclosed', false)->orWhereNull('isclosed'))
            ->orderByDesc('journalcaisseid')->first();
        if (!$this->journal) {
            $this->markTestSkipped('Aucune journée de caisse ouverte en base.');
        }
        $row = DB::table('users')->where('userid', $this->journal->userid)->first();
        $this->user = User::find($row->userid);
        $this->actingAs($this->user);
        $this->produit2 = DB::table('produit2s')->first();
    }

    private function payload(float $total, array $reglements = [], bool $enAttente = false, array $extra = []): array
    {
        return array_merge([
            'clientid' => (int) config('pos.client_passager_id'),
            'vendeurid' => $this->user->employeeid,
            'en_attente' => $enAttente,
            'lignes' => [[
                'produit2id' => $this->produit2->produit2id,
                'qte' => 1, 'prix' => $total, 'prixNet' => $total, 'remise' => 0, 'total' => $total,
            ]],
            'reglements' => $reglements,
        ], $extra);
    }

    public function test_reglement_au_format_du_logiciel_de_bureau(): void
    {
        $res = $this->postJson(route('vente.caisse.store'), $this->payload(80, [
            ['modereglementid' => 1, 'montant' => 50],
            ['modereglementid' => 4, 'montant' => 30],
        ]))->assertJson(['success' => true]);
        $id = (int) $res->json('cticketid');

        $dets = DB::table('creglementdets')->where('documentid', $id)->where('typeid', 1)->get();
        $this->assertCount(2, $dets, 'une ligne creglementdets par règlement');
        $regs = DB::table('creglements')->whereIn('creglementid', $dets->pluck('creglementid'))->get();
        $this->assertCount(2, $regs);
        foreach ($regs as $r) {
            $this->assertEquals(0, (int) $r->documentid);
            $this->assertEquals(1, (int) $r->typeid);
            $this->assertEquals(2, (int) $r->statusreglementid);
            $this->assertEquals($this->journal->journalcaisseid, $r->journalcaisseid);
            $this->assertEqualsWithDelta(-(float) $r->montant, (float) $r->montantav, 0.001);
        }

        // Le trigger creglement_calc (inchangé) a soldé le ticket à partir de creglementdets
        $ticket = DB::table('ctickets')->where('cticketid', $id)->first();
        $this->assertEqualsWithDelta(80, $ticket->acompte, 0.001);
        $this->assertEqualsWithDelta(0, $ticket->netapayer, 0.001);

        // Le ticket imprimé liste bien les deux règlements
        $this->get(route('vente.tickets.show', $id), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->assertSee('Espèce')->assertSee('Carte Bancaire');
    }

    public function test_complement_acompte_au_format_du_bureau(): void
    {
        $res = $this->postJson(route('vente.caisse.store'), $this->payload(100, [['modereglementid' => 1, 'montant' => 40]]))
            ->assertJson(['success' => true]);
        $id = (int) $res->json('cticketid');

        $this->postJson(route('vente.caisse.store_complement_acompte'), [
            'cticketid' => $id,
            'reglements' => [['modereglementid' => 4, 'montant' => 60]],
        ])->assertJson(['success' => true, 'nouveau_reste' => 0]);

        $complement = DB::table('creglementdets as d')->join('creglements as r', 'r.creglementid', '=', 'd.creglementid')
            ->where('d.documentid', $id)->where('r.iscomplement', true)->first(['r.*']);
        $this->assertNotNull($complement);
        $this->assertEquals(0, (int) $complement->documentid);
        $this->assertEqualsWithDelta(60, $complement->montant, 0.001);
    }

    public function test_reprise_sans_perte_puis_suppression_a_la_validation(): void
    {
        $attente = $this->postJson(route('vente.caisse.store'), $this->payload(45, [], true))->assertJson(['success' => true]);
        $attenteId = (int) $attente->json('cticketid');

        // La reprise renvoie les lignes mais ne supprime rien (fermer la caisse ne perd pas le ticket)
        $this->postJson('/vente/caisse/reprise/' . $attenteId)
            ->assertJson(['success' => true, 'cticketid' => $attenteId])
            ->assertJsonPath('lignes.0.produit2id', $this->produit2->produit2id);
        $this->assertTrue(DB::table('ctickets')->where('cticketid', $attenteId)->where('enattente', true)->exists());
        $this->getJson(route('vente.caisse.en_attente'))->assertJsonFragment(['cticketid' => $attenteId]);

        // Validation de la vente reprise : le ticket en attente est supprimé dans la même transaction
        $vente = $this->postJson(route('vente.caisse.store'), $this->payload(45, [['modereglementid' => 1, 'montant' => 45]], false, [
            'reprise_cticketid' => $attenteId,
        ]))->assertJson(['success' => true]);
        $this->assertFalse(DB::table('ctickets')->where('cticketid', $attenteId)->exists());
        $this->assertTrue(DB::table('ctickets')->where('cticketid', $vente->json('cticketid'))->where('enattente', false)->exists());

        // Une seconde validation de la même reprise (autre poste) est refusée : pas de double vente
        $this->postJson(route('vente.caisse.store'), $this->payload(45, [['modereglementid' => 1, 'montant' => 45]], false, [
            'reprise_cticketid' => $attenteId,
        ]))->assertJson(['success' => false]);
    }

    public function test_remettre_en_attente_un_ticket_repris_ne_le_duplique_pas(): void
    {
        $attenteId = (int) $this->postJson(route('vente.caisse.store'), $this->payload(30, [], true))->json('cticketid');
        $this->postJson('/vente/caisse/reprise/' . $attenteId)->assertJson(['success' => true]);

        $nouveauId = (int) $this->postJson(route('vente.caisse.store'), $this->payload(30, [], true, ['reprise_cticketid' => $attenteId]))
            ->assertJson(['success' => true])->json('cticketid');

        $this->assertFalse(DB::table('ctickets')->where('cticketid', $attenteId)->exists());
        $this->assertTrue(DB::table('ctickets')->where('cticketid', $nouveauId)->where('enattente', true)->exists());
    }

    public function test_client_et_vendeur_inexistants_refuses(): void
    {
        $this->postJson(route('vente.caisse.store'), $this->payload(10, [['modereglementid' => 1, 'montant' => 10]], false, ['clientid' => 999999999]))
            ->assertStatus(422)->assertJsonValidationErrors('clientid');
        $this->postJson(route('vente.caisse.store'), $this->payload(10, [['modereglementid' => 1, 'montant' => 10]], false, ['vendeurid' => 999999]))
            ->assertStatus(422)->assertJsonValidationErrors('vendeurid');
    }

    public function test_journee_ouverte_meme_regle_partout(): void
    {
        $service = app(JourneeCaisseService::class);
        $this->assertEquals($this->journal->journalcaisseid, $service->journeeOuverte($this->user)->journalcaisseid);

        // La validation d'un ticket utilise la même journée que l'écran caisse
        $this->postJson(route('vente.caisse.store'), $this->payload(12, [['modereglementid' => 1, 'montant' => 12]]))
            ->assertJson(['success' => true]);

        // Un utilisateur d'un autre site ne voit pas cette journée
        $autre = clone $this->user;
        $autre->siteid = (int) DB::table('sites')->where('siteid', '!=', $this->journal->siteid)->value('siteid');
        $autre->userid = -1;
        $this->assertNull($service->journeeOuverte($autre));
    }

    public function test_utilisateur_sans_site_refuse(): void
    {
        $this->user->siteid = null;
        $this->actingAs($this->user);
        $this->get(route('stock.consultation.index'))->assertStatus(403);
        $this->postJson(route('vente.caisse.store'), $this->payload(10, [['modereglementid' => 1, 'montant' => 10]]))
            ->assertJson(['success' => false]);
    }

    public function test_tva_reelle_sur_les_lignes_de_transfert(): void
    {
        $produit = DB::table('vproduit2stocks')->where('siteid', $this->journal->siteid)->where('qtestock', '>', 0)->first();
        $recepteur = DB::table('sites')->where('siteid', '!=', $this->journal->siteid)->where('istransfert', true)->first()
            ?? DB::table('sites')->where('siteid', '!=', $this->journal->siteid)->first();
        if (!$produit || !$recepteur) {
            $this->markTestSkipped('Pas de produit en stock ou de site récepteur.');
        }

        $this->post('/transfert/envoye', [
            'siterecepteurid' => $recepteur->siteid,
            'description' => 'Test TVA reelle',
            'action_type' => 'save',
            'lignes' => [['produitid' => $produit->produitid, 'produit2id' => $produit->produit2id, 'qte' => 1, 'qteenvoi' => 1, 'prix' => 119]],
        ])->assertRedirect('/transfert/envoye');

        $bon = DB::table('bontransferts')->where('description', 'Test TVA reelle')->first();
        $ligne = DB::table('detbontransferts')->where('bontransfertid', $bon->bontransfertid)->first();
        $taxe = TaxeProduit::pour($produit->produitid);
        $this->assertEquals($taxe['taxefamilleid'], (int) $ligne->taxefamilleid);
        $this->assertEqualsWithDelta($taxe['tva'], (float) $ligne->tva, 0.001);
        $this->assertEqualsWithDelta(119 / (1 + $taxe['tva'] / 100), (float) $ligne->ht, 0.001);
        $this->assertEqualsWithDelta(119 - 119 / (1 + $taxe['tva'] / 100), (float) $bon->totaltva, 0.01);
    }
}
