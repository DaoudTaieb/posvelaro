<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use App\Models\User;

/**
 * Corrections du module transfert : crédit du stock à la réception et envoi d'un brouillon via update.
 * Le stock est calculé exclusivement par les triggers (calcul_stock) à partir de l'état de chaque ligne.
 */
class TransfertCorrectionsTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $siteExpediteur;
    protected $siteRecepteur;
    protected $produit;

    protected function setUp(): void
    {
        parent::setUp();

        $dbUser = DB::table('users')->orderBy('userid')->first();
        $this->user = new User();
        $this->user->userid = $dbUser->userid;
        $this->user->login = $dbUser->login;
        $this->user->siteid = $dbUser->siteid ?? 101;
        $this->user->exists = true;
        $this->actingAs($this->user);

        $this->siteExpediteur = DB::table('sites')->where('siteid', $this->user->siteid)->first() ?? DB::table('sites')->first();
        $this->siteRecepteur = DB::table('sites')->where('siteid', '!=', $this->siteExpediteur->siteid)->first();

        $this->produit = DB::table('vproduit2stocks')
            ->where('siteid', $this->siteExpediteur->siteid)
            ->first();

        if (!$this->siteRecepteur || !$this->produit) {
            $this->markTestSkipped('Pas de site récepteur ou de produit avec stock pour tester.');
        }
    }

    private function stock($siteid): float
    {
        return (float) (DB::table('stock2s')
            ->where('produit2id', $this->produit->produit2id)
            ->where('siteid', $siteid)
            ->value('qtestock') ?? 0);
    }

    private function forcerStockExpediteur(float $qte): void
    {
        DB::table('stock2s')
            ->where('produit2id', $this->produit->produit2id)
            ->where('siteid', $this->siteExpediteur->siteid)
            ->update(['qtestock' => $qte]);
    }

    public function test_reception_credite_le_stock_recepteur_et_refuse_une_double_reception()
    {
        $this->forcerStockExpediteur(1000);

        $this->post('/transfert/envoye', [
            'siterecepteurid' => $this->siteRecepteur->siteid,
            'description' => 'Test reception credit stock',
            'action_type' => 'envoyer',
            'lignes' => [[
                'produitid' => $this->produit->produitid,
                'produit2id' => $this->produit->produit2id,
                'qte' => 5,
                'qteenvoi' => 5,
                'prix' => 10,
            ]],
        ])->assertRedirect('/transfert/envoye');

        $bon = DB::table('bontransferts')->where('description', 'Test reception credit stock')->first();
        $this->assertNotNull($bon);
        $ligne = DB::table('detbontransferts')->where('bontransfertid', $bon->bontransfertid)->first();
        $this->assertEquals(2, $ligne->etatbontransfertid);

        $stockRecepteurAvant = $this->stock($this->siteRecepteur->siteid);

        // Le récepteur réceptionne 3 sur 5 (une valeur > qteenvoi serait plafonnée)
        $this->user->siteid = $this->siteRecepteur->siteid;
        $this->post('/transfert/recu/' . $bon->bontransfertid . '/receptionner', [
            'reception' => [$ligne->detbontransfertid => ['qte_recue' => 3, 'observation' => 'Manque 2']],
        ])->assertSessionHas('success');

        $ligne = DB::table('detbontransferts')->where('detbontransfertid', $ligne->detbontransfertid)->first();
        $this->assertEquals(3, $ligne->etatbontransfertid);
        $this->assertEquals(3, (float) $ligne->qterecu);
        $this->assertEquals(2, (float) $ligne->qteecart);
        $this->assertEquals(3, DB::table('bontransferts')->where('bontransfertid', $bon->bontransfertid)->value('etatbontransfertid'));

        // Le trigger crédite le site récepteur de la quantité reçue
        $this->assertEquals($stockRecepteurAvant + 3, $this->stock($this->siteRecepteur->siteid));

        // Deuxième réception refusée, stock inchangé
        $this->post('/transfert/recu/' . $bon->bontransfertid . '/receptionner', [
            'reception' => [$ligne->detbontransfertid => ['qte_recue' => 5]],
        ])->assertSessionHas('error');
        $this->assertEquals($stockRecepteurAvant + 3, $this->stock($this->siteRecepteur->siteid));
    }

    public function test_reception_refusee_pour_un_brouillon_ou_un_autre_site()
    {
        $this->post('/transfert/envoye', [
            'siterecepteurid' => $this->siteRecepteur->siteid,
            'description' => 'Test reception brouillon',
            'action_type' => 'save',
            'lignes' => [[
                'produitid' => $this->produit->produitid,
                'produit2id' => $this->produit->produit2id,
                'qte' => 2,
                'prix' => 10,
            ]],
        ])->assertRedirect('/transfert/envoye');

        $bon = DB::table('bontransferts')->where('description', 'Test reception brouillon')->first();
        $ligne = DB::table('detbontransferts')->where('bontransfertid', $bon->bontransfertid)->first();

        // Expéditeur (autre site que le récepteur) : refusé
        $this->post('/transfert/recu/' . $bon->bontransfertid . '/receptionner', [
            'reception' => [$ligne->detbontransfertid => ['qte_recue' => 2]],
        ])->assertSessionHas('error');

        // Récepteur mais bon en brouillon : refusé
        $this->user->siteid = $this->siteRecepteur->siteid;
        $this->get('/transfert/recu/' . $bon->bontransfertid . '/receptionner')->assertRedirect('/transfert/recu');
        $this->post('/transfert/recu/' . $bon->bontransfertid . '/receptionner', [
            'reception' => [$ligne->detbontransfertid => ['qte_recue' => 2]],
        ])->assertSessionHas('error');

        $this->assertEquals(1, DB::table('detbontransferts')->where('detbontransfertid', $ligne->detbontransfertid)->value('etatbontransfertid'));
        $this->assertEquals(1, DB::table('bontransferts')->where('bontransfertid', $bon->bontransfertid)->value('etatbontransfertid'));
    }

    public function test_update_brouillon_avec_envoyer_passe_en_etat_2_et_debite_l_expediteur()
    {
        $this->post('/transfert/envoye', [
            'siterecepteurid' => $this->siteRecepteur->siteid,
            'description' => 'Test brouillon puis envoi',
            'action_type' => 'save',
            'lignes' => [[
                'produitid' => $this->produit->produitid,
                'produit2id' => $this->produit->produit2id,
                'qte' => 4,
                'qteenvoi' => 4,
                'prix' => 10,
            ]],
        ])->assertRedirect('/transfert/envoye');

        $bon = DB::table('bontransferts')->where('description', 'Test brouillon puis envoi')->first();
        $this->assertEquals(1, $bon->etatbontransfertid);

        // Stock calculé par le trigger avec la ligne brouillon (aucun débit)
        $stockBase = $this->stock($this->siteExpediteur->siteid);

        // Stock suffisant pour la vérification avant envoi
        $this->forcerStockExpediteur(1000);

        $this->put('/transfert/envoye/' . $bon->bontransfertid, [
            'description' => 'Test brouillon puis envoi',
            'action_type' => 'envoyer',
            'lignes' => [[
                'produitid' => $this->produit->produitid,
                'produit2id' => $this->produit->produit2id,
                'qte' => 4,
                'qteenvoi' => 3,
                'prix' => 10,
            ]],
        ])->assertRedirect('/transfert/envoye');

        $this->assertEquals(2, DB::table('bontransferts')->where('bontransfertid', $bon->bontransfertid)->value('etatbontransfertid'));
        $lignes = DB::table('detbontransferts')->where('bontransfertid', $bon->bontransfertid)->get();
        $this->assertCount(1, $lignes);
        $this->assertEquals(2, $lignes[0]->etatbontransfertid);

        // Le trigger débite l'expéditeur de qteenvoi
        $this->assertEquals($stockBase - 3, $this->stock($this->siteExpediteur->siteid));

        // Un bon envoyé ne peut plus être modifié ni supprimé
        $this->put('/transfert/envoye/' . $bon->bontransfertid, ['description' => 'Modif interdite'])
            ->assertSessionHas('error');
        $this->delete('/transfert/envoye/' . $bon->bontransfertid)->assertSessionHas('error');
        $this->assertNotNull(DB::table('bontransferts')->where('bontransfertid', $bon->bontransfertid)->first());
    }

    public function test_envoi_refuse_si_quantite_negative_ou_stock_insuffisant()
    {
        $this->post('/transfert/envoye', [
            'siterecepteurid' => $this->siteRecepteur->siteid,
            'description' => 'Test qte negative',
            'action_type' => 'envoyer',
            'lignes' => [[
                'produitid' => $this->produit->produitid,
                'produit2id' => $this->produit->produit2id,
                'qte' => 1,
                'qteenvoi' => -5,
                'prix' => 10,
            ]],
        ])->assertSessionHasErrors('lignes.0.qteenvoi');

        $this->forcerStockExpediteur(2);

        $this->post('/transfert/envoye', [
            'siterecepteurid' => $this->siteRecepteur->siteid,
            'description' => 'Test stock insuffisant',
            'action_type' => 'envoyer',
            'lignes' => [[
                'produitid' => $this->produit->produitid,
                'produit2id' => $this->produit->produit2id,
                'qte' => 5,
                'qteenvoi' => 5,
                'prix' => 10,
            ]],
        ])->assertSessionHas('error');

        $this->assertNull(DB::table('bontransferts')->where('description', 'Test qte negative')->first());
        $this->assertNull(DB::table('bontransferts')->where('description', 'Test stock insuffisant')->first());
    }

    public function test_bon_lie_a_la_demande_importee()
    {
        // Demande émise par le site récepteur vers notre site, pointée (4 validées)
        $demandeId = (DB::table('demandetransferts')->max('demandetransfertid') ?? 0) + 1;
        DB::table('demandetransferts')->insert([
            'demandetransfertid' => $demandeId,
            'siteid' => $this->siteRecepteur->siteid,
            'siterecepteurid' => $this->siteExpediteur->siteid,
            'etatdemandetransfertid' => 3,
            'demandetransfertnumero' => (DB::table('demandetransferts')->max('demandetransfertnumero') ?? 0) + 1,
            'numerointerne' => 'DTE-LIEN',
            'datecreation' => now(), 'demandetransfertdate' => now(), 'datedebut' => now(), 'datefin' => now(),
            'confirmer' => false, 'totalqte' => 6,
            'totalbrutht' => 0, 'remise' => 0, 'vremise' => 0, 'totalnetht' => 0, 'totaltva' => 0, 'totalttc' => 0, 'acompte' => 0, 'netapayer' => 0
        ]);
        $detId = (DB::table('detdemandetransferts')->max('detdemandetransfertid') ?? 0) + 1;
        DB::table('detdemandetransferts')->insert([
            'detdemandetransfertid' => $detId,
            'demandetransfertid' => $demandeId,
            'siteid' => $this->siteRecepteur->siteid,
            'siterecepteurid' => $this->siteExpediteur->siteid,
            'produitid' => $this->produit->produitid,
            'produit2id' => $this->produit->produit2id,
            'taxefamilleid' => 1, 'ht' => 10, 'ttc' => 11.9,
            'qte' => 6, 'qteenvoi' => 4, 'qterecu' => 0, 'qteecart' => 0,
            'etatdemandetransfertid' => 3,
            'totalht' => 60, 'remise' => 0, 'remise2' => 0, 'totalhtnet' => 60,
            'taxe1' => 0, 'vtaxe1' => 0, 'taxe2' => 0, 'vtaxe2' => 0, 'taxe3' => 0, 'vtaxe3' => 0, 'taxe4' => 0, 'vtaxe4' => 0,
            'tva' => 19, 'vtva' => 0, 'totalttc' => 71.4, 'totalttcnet' => 71.4,
            'date' => now(), 'largeur' => 0, 'longueur' => 0, 'surface' => 0,
            'pointer' => true, 'ordre' => 1, 'prodid' => 0, 'modestock' => 1
        ]);

        $json = $this->get('/transfert/envoye/api/demande-lignes/' . $demandeId)->assertStatus(200)->json();
        $this->assertCount(1, $json);
        $this->assertEquals(4, $json[0]['qte_validee']);

        $this->forcerStockExpediteur(1000);
        $this->post('/transfert/envoye', [
            'siterecepteurid' => $this->siteRecepteur->siteid,
            'description' => 'Test lien demande',
            'action_type' => 'envoyer',
            'lignes' => [[
                'produitid' => $this->produit->produitid,
                'produit2id' => $this->produit->produit2id,
                'detdemandetransfertid' => $detId,
                'qte' => 6,
                'qteenvoi' => 4,
                'prix' => 10,
            ]],
        ])->assertRedirect('/transfert/envoye');

        $bon = DB::table('bontransferts')->where('description', 'Test lien demande')->first();
        $this->assertEquals($demandeId, $bon->demandetransfertid);
        $this->assertEquals($detId, DB::table('detbontransferts')->where('bontransfertid', $bon->bontransfertid)->value('detdemandetransfertid'));

        // La demande n'est plus importable
        $this->assertCount(0, $this->get('/transfert/envoye/api/demande-lignes/' . $demandeId)->json());
        $this->get('/transfert/envoye/create')->assertViewHas('demandes_validees', function ($demandes) use ($demandeId) {
            return !$demandes->contains('demandetransfertid', $demandeId);
        });
    }

    public function test_recherche_globale_sur_numero_ne_plante_pas()
    {
        $this->get('/transfert/envoye?search=12', ['X-Requested-With' => 'XMLHttpRequest'])->assertStatus(200);
        $this->get('/transfert/recu?search=12', ['X-Requested-With' => 'XMLHttpRequest'])->assertStatus(200);
        $this->get('/transfert/demande-recu?search=12', ['X-Requested-With' => 'XMLHttpRequest'])->assertStatus(200);
    }
}
