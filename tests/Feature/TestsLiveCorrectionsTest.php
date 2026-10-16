<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Non-régression des bugs trouvés lors des tests en direct (caisse, retours, remboursement,
 * chèques, configuration, messages de validation).
 */
class TestsLiveCorrectionsTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $produit2;

    protected function setUp(): void
    {
        parent::setUp();

        $row = DB::table('users')->orderBy('userid')->first();
        $this->produit2 = DB::table('produit2s')->first();
        if (!$row || !$this->produit2) {
            $this->markTestSkipped('Données insuffisantes.');
        }
        $this->user = User::find($row->userid);
        $this->actingAs($this->user);

        if (!DB::table('journalcaisses')->where('userid', $this->user->userid)->where('isclosed', 0)->exists()) {
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
        DB::statement("SELECT setval('depenses_depenseid_seq', coalesce(max(depenseid), 1), max(depenseid) IS NOT null) FROM depenses;");
    }

    private function ligne(array $x = []): array
    {
        return array_merge([
            'produit2id' => $this->produit2->produit2id,
            'designation' => 'Article test',
            'qte' => 1, 'prix' => 50, 'prixNet' => 50, 'remise' => 0, 'total' => 50,
        ], $x);
    }

    private function vendre(array $lignes, array $reglements)
    {
        return $this->postJson(route('vente.caisse.store'), [
            'clientid' => 1, 'lignes' => $lignes, 'reglements' => $reglements,
        ]);
    }

    public function test_ligne_a_quantite_nulle_refusee()
    {
        $avant = DB::table('ctickets')->count();
        $this->vendre([$this->ligne(['qte' => 0, 'total' => 0])], [])
            ->assertJson(['success' => false]);
        $this->assertEquals($avant, DB::table('ctickets')->count(), 'Aucun ticket ne doit être créé (article gratuit)');
    }

    public function test_total_de_ligne_incoherent_refuse()
    {
        $this->vendre([$this->ligne(['qte' => 3, 'total' => 1])], [['modereglementid' => 1, 'montant' => 1]])
            ->assertJson(['success' => false]);
    }

    public function test_retour_lie_a_la_vente_et_plafonne()
    {
        $vente = $this->vendre([$this->ligne(['qte' => 2, 'total' => 100])], [['modereglementid' => 1, 'montant' => 100]])
            ->assertJson(['success' => true]);
        $detId = DB::table('detctickets')->where('cticketid', $vente->json('cticketid'))->value('detcticketid');

        // Échange : retour d'une pièce + nouvelle vente
        $this->vendre([
            $this->ligne(['qte' => -1, 'total' => -50, 'detventeid' => $detId]),
            $this->ligne(['qte' => 1, 'total' => 50]),
        ], [])->assertJson(['success' => true]);

        $this->assertEquals(1, DB::table('detctickets')->where('detventeid', $detId)->where('qte', '<', 0)->count());

        // Il ne reste qu'une pièce à retourner
        $this->vendre([$this->ligne(['qte' => -2, 'total' => -100, 'detventeid' => $detId])], [])
            ->assertJson(['success' => false]);

        // Le détail du ticket d'origine indique la quantité déjà retournée
        $details = $this->getJson('/vente/caisse/ticket-details/' . $vente->json('cticketnumero') . '?par=numero')->json('lines.0');
        $this->assertEquals(1, (float) $details['qte_retournee']);
    }

    public function test_remboursement_enregistre_un_retour_argent()
    {
        $vente = $this->vendre([$this->ligne()], [['modereglementid' => 1, 'montant' => 50]]);
        $detId = DB::table('detctickets')->where('cticketid', $vente->json('cticketid'))->value('detcticketid');

        $res = $this->vendre([$this->ligne(['qte' => -1, 'total' => -50, 'detventeid' => $detId])], [['modereglementid' => 1, 'montant' => -50]])
            ->assertJson(['success' => true]);

        $ticket = DB::table('ctickets')->where('cticketid', $res->json('cticketid'))->first();
        $this->assertEqualsWithDelta(-50, $ticket->totalttc, 0.001);
        $this->assertEqualsWithDelta(0, $ticket->netapayer, 0.001, 'Le ticket de remboursement doit être soldé');

        $depense = DB::table('depenses')->where('cticketid', $ticket->cticketid)->first();
        $this->assertNotNull($depense);
        $this->assertEquals(10, $depense->familledepenseid);
        $this->assertEqualsWithDelta(50, $depense->montant, 0.001);
        $this->assertEquals(0, DB::table('creglementdets')->where('documentid', $ticket->cticketid)->where('typeid', 1)->count());
    }

    public function test_reglement_negatif_refuse_sur_une_vente()
    {
        $this->vendre([$this->ligne()], [['modereglementid' => 1, 'montant' => 80], ['modereglementid' => 1, 'montant' => -30]])
            ->assertJson(['success' => false]);
    }

    public function test_details_du_cheque_enregistres()
    {
        $res = $this->vendre([$this->ligne()], [[
            'modereglementid' => 2, 'montant' => 50, 'numero' => 'CHQ-778', 'banque' => 'STB',
            'date' => '2026-12-31', 'proprietaire' => 'Client Test',
        ]])->assertJson(['success' => true]);

        // Format du logiciel de bureau : le ticket est rattaché par creglementdets
        $reg = DB::table('creglements')->whereIn('creglementid', DB::table('creglementdets')->where('documentid', $res->json('cticketid'))->where('typeid', 1)->select('creglementid'))->first();
        $this->assertEquals('CHQ-778', $reg->numero);
        $this->assertEquals('STB', $reg->agencereglement);
        $this->assertEquals('Client Test', $reg->raisonreglement);
        $this->assertStringStartsWith('2026-12-31', $reg->echeance);
    }

    public function test_configuration_enregistree_sans_modification_ne_change_rien()
    {
        $avant = DB::table('retailconfigs')->where('pagelibelle', 'Général')->pluck('value', 'retailconfigid');

        // Soumission du formulaire tel qu'affiché : interrupteurs et champs texte avec leurs valeurs actuelles
        $html = $this->get('/parametre/configuration/general')->assertStatus(200)->getContent();
        $donnees = [];
        foreach ($avant as $id => $valeur) {
            $estInterrupteur = str_contains($html, 'type="checkbox" name="config[' . $id . ']"');
            if ($estInterrupteur) {
                $donnees[$id] = strtolower($valeur) === 'true' ? 'True' : 'False';
            } else {
                $donnees[$id] = $valeur;
            }
        }
        $this->post('/parametre/configuration/general', ['config' => $donnees])->assertRedirect();

        $apres = DB::table('retailconfigs')->where('pagelibelle', 'Général')->pluck('value', 'retailconfigid');
        $this->assertEquals($avant->all(), $apres->all());
    }

    public function test_avoir_consomme_en_plusieurs_fois_puis_cloture()
    {
        $avoirId = DB::table('cavoirs')->insertGetId([
            'clientid' => 1, 'siteid' => $this->user->siteid, 'cavoirnumero' => 999999901, 'numerointerne' => 'AV-TEST-PHPUNIT',
            'modepieceid' => 1, 'etatfactureid' => 1, 'cloture' => false, 'datecreation' => now(), 'cavoirdate' => now(),
            'totalbrutht' => 30, 'remise' => 0, 'vremise' => 0, 'totalnetht' => 30, 'totaltva' => 0, 'droittimbre' => 0,
            'totalttc' => 30, 'acompte' => 0, 'netapayer' => 30, 'totalqte' => 1, 'totalrs' => 0, 'rspaye' => 0, 'tauxrs' => 0,
        ], 'cavoirid');

        $payer = fn (float $avoir) => $this->vendre([$this->ligne()], [
            ['modereglementid' => 9, 'montant' => $avoir, 'cavoirid' => $avoirId],
            ['modereglementid' => 1, 'montant' => 50 - $avoir],
        ]);

        $payer(20)->assertJson(['success' => true]);
        $this->assertEqualsWithDelta(10, DB::table('cavoirs')->where('cavoirid', $avoirId)->value('netapayer'), 0.001);

        $payer(15)->assertJson(['success' => false]); // reste 10 seulement

        $payer(10)->assertJson(['success' => true]);
        $avoir = DB::table('cavoirs')->where('cavoirid', $avoirId)->first();
        $this->assertEqualsWithDelta(0, $avoir->netapayer, 0.001);
        $this->assertTrue((bool) $avoir->cloture);
        $this->assertEquals(2, DB::table('creglements')->where('documentavid', $avoirId)->where('modereglementid', 9)->count());
    }

    public function test_champs_attendus_par_le_logiciel_de_bureau()
    {
        $res = $this->vendre([$this->ligne()], [['modereglementid' => 1, 'montant' => 50]])->assertJson(['success' => true]);

        $ticket = DB::table('ctickets')->where('cticketid', $res->json('cticketid'))->first();
        $this->assertNotNull($ticket->caisseid, 'ctickets.caisseid doit être renseigné');

        $reg = DB::table('creglements')->whereIn('creglementid', DB::table('creglementdets')->where('documentid', $ticket->cticketid)->where('typeid', 1)->select('creglementid'))->first();
        $this->assertNotNull($reg, 'Le règlement doit être rattaché au ticket par creglementdets');
        $this->assertEquals(0, (int) $reg->documentid, 'creglements.documentid = 0 (convention du bureau)');
        $this->assertEquals(2, (int) $reg->statusreglementid, 'Statut « Payé » comme le bureau');
        $this->assertEqualsWithDelta(-50, $reg->montantav, 0.001);
        $this->assertEqualsWithDelta(50, $reg->montantrecu, 0.001);
    }

    public function test_cloture_reservee_aux_roles_caisse()
    {
        // Rôle temporaire sans accès caisse mais avec le droit de clôture non bloqué
        $roleId = DB::table('userdroits')->max('userdroitid') + 1;
        DB::table('userdroits')->insert(['userdroitid' => $roleId, 'libelle' => 'Test sans caisse', 'notcaisse' => true, 'notbackoffice' => false]);
        $user = clone $this->user;
        $user->userdroitid = $roleId;
        \App\Support\Droits::viderCache();
        $this->actingAs($user);

        $this->get('/vente/journee/cloture')->assertStatus(403);
    }

    public function test_messages_de_validation_en_francais()
    {
        $this->postJson(route('vente.caisse.pos.store_client'), [])
            ->assertStatus(422)
            ->assertJsonPath('errors.raison.0', 'Le champ raison sociale est obligatoire.');
    }

    public function test_page_caisse_expose_le_jeton_csrf()
    {
        $html = $this->get('/vente/caisse/pos')->getContent();
        $this->assertStringContainsString('<meta name="csrf-token"', $html);
    }
}
