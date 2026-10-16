<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\FideliteService;
use App\Support\Code128;
use App\Support\RetailConfig;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Programme de fidélité : paliers 10/20/30 %, bon d'achat à partir de la 4ème vente (10 DT / paire),
 * validité 3 mois, carte valable si 2 ventes par année anniversaire, annulation sur retour.
 *
 * Toutes les écritures sont annulées en fin de test (DatabaseTransactions).
 */
class FideliteTest extends TestCase
{
    use DatabaseTransactions;

    private FideliteService $fidelite;
    private int $siteid;
    private int $produitFidelite;   // produit d'une sous-famille fidélité
    private int $produitHorsFidelite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fidelite = app(FideliteService::class);
        RetailConfig::vider();

        $row = DB::table('users')->orderBy('userid')->first();
        $user = User::find($row->userid);
        $this->actingAs($user);
        $this->siteid = (int) ($user->siteid ?: 102);

        // Une sous-famille fidélité et une hors fidélité, choisies parmi celles qui ont des produits
        $sfFid = DB::table('sousfamilles as sf')->join('produits as p', 'p.sousfamilleid', '=', 'sf.sousfamilleid')
            ->orderBy('sf.sousfamilleid')->value('sf.sousfamilleid');
        $sfAutre = DB::table('sousfamilles as sf')->join('produits as p', 'p.sousfamilleid', '=', 'sf.sousfamilleid')
            ->where('sf.sousfamilleid', '!=', $sfFid)->orderBy('sf.sousfamilleid')->value('sf.sousfamilleid');
        DB::table('sousfamilles')->update(['is_loyalty_enabled' => false]);
        DB::table('sousfamilles')->where('sousfamilleid', $sfFid)->update(['is_loyalty_enabled' => true]);

        $this->produitFidelite = (int) DB::table('produits')->where('sousfamilleid', $sfFid)->value('produitid');
        $this->produitHorsFidelite = (int) DB::table('produits')->where('sousfamilleid', $sfAutre)->value('produitid');
    }

    private function creerClient(bool $fidelite = true): object
    {
        $id = DB::table('clients')->insertGetId([
            'nom' => 'TEST FIDELITE', 'prenom' => 'Claude', 'clientcode' => 'TST' . uniqid(), 'code' => 0,
            'fidelite' => $fidelite, 'credit' => true, 'bloque' => false, 'barcode' => 'CARTE' . uniqid(),
            'clientfamilleid' => DB::table('clientfamilles')->value('clientfamilleid'),
            'echeance' => 0, 'impaye' => 0, 'plafonecheance' => 0, 'plafonsolde' => 0,
            'solde' => 0, 'soldeinitial' => 0, 'remise' => 0, 'soldefidelite' => 0, 'cumulfidelite' => 0, 'pointfidelite' => 0,
        ], 'clientid');

        return DB::table('clients')->where('clientid', $id)->first();
    }

    /** Insère un ticket validé (sans lignes : les compteurs ne lisent que ctickets). */
    private function creerTicket(int $clientid, Carbon $date, float $total = 100): int
    {
        $journal = DB::table('journalcaisses')->orderByDesc('journalcaisseid')->first();
        $this->assertNotNull($journal, 'Une journée de caisse doit exister en base pour créer un ticket de test.');

        return (int) DB::table('ctickets')->insertGetId([
            'clientid' => $clientid, 'siteid' => $this->siteid, 'cticketnumero' => 9000000000 + random_int(1, 999999999),
            'modepieceid' => 1, 'etatblid' => 1, 'transfere' => 0, 'datecreation' => $date, 'cticketdate' => $date,
            'totalbrutht' => $total, 'remise' => 0, 'vremise' => 0, 'totalnetht' => $total, 'totaltva' => 0, 'totalttc' => $total,
            'acompte' => $total, 'netapayer' => 0, 'totalqte' => 1, 'caissierid' => auth()->id(), 'employeeid' => $journal->employeeid,
            'journalcaisseid' => $journal->journalcaisseid, 'caisseid' => $journal->caisseid, 'retour' => 0, 'totalavremise' => $total, 'userid' => auth()->id(),
            'vierge' => 0, 'totalfidelite' => 0, 'totalhtachat' => 0, 'totalttcachat' => 0, 'margeht' => 0, 'margettc' => 0,
            'pmargeht' => 0, 'pmargettc' => 0, 'enattente' => false, 'modestock' => 1, 'totalqtettans' => 1,
        ], 'cticketid');
    }

    private function client(int $id): object
    {
        return DB::table('clients')->where('clientid', $id)->first();
    }

    public function test_paliers_10_20_30_puis_bon_a_la_4eme_vente(): void
    {
        $client = $this->creerClient();
        $now = Carbon::parse('2026-10-16 10:00:00');
        $lignes = [['produitid' => $this->produitFidelite, 'qte' => 2], ['produitid' => $this->produitHorsFidelite, 'qte' => 1]];

        $this->assertSame(10, $this->fidelite->palier($client), '1ère vente : 10 %');

        $t1 = $this->creerTicket($client->clientid, $now);
        $r1 = $this->fidelite->appliquerVente($this->client($client->clientid), $lignes, $t1, $now);
        $this->assertSame(1, $r1['ventes']);
        $this->assertNull($r1['bon'], 'pas de bon avant la 4ème vente');
        $this->assertSame(20, $this->fidelite->palier($this->client($client->clientid)), '2ème vente : 20 %');

        $t2 = $this->creerTicket($client->clientid, $now->copy()->addDay());
        $this->fidelite->appliquerVente($this->client($client->clientid), $lignes, $t2, $now->copy()->addDay());
        $this->assertSame(30, $this->fidelite->palier($this->client($client->clientid)), '3ème vente : 30 %');

        $t3 = $this->creerTicket($client->clientid, $now->copy()->addDays(2));
        $r3 = $this->fidelite->appliquerVente($this->client($client->clientid), $lignes, $t3, $now->copy()->addDays(2));
        $this->assertNull($r3['bon']);
        $this->assertSame(30, $this->fidelite->palier($this->client($client->clientid)), '4ème vente : toujours 30 %');

        $d4 = $now->copy()->addDays(3);
        $t4 = $this->creerTicket($client->clientid, $d4);
        $r4 = $this->fidelite->appliquerVente($this->client($client->clientid), $lignes, $t4, $d4);
        $this->assertSame(4, $r4['ventes']);
        $this->assertNotNull($r4['bon'], 'un bon est émis à la 4ème vente');
        $this->assertEquals(20.0, (float) $r4['bon']->montant, '2 paires fidélité x 10 DT ; l\'article hors fidélité ne compte pas');
        $this->assertSame($d4->copy()->addMonths(3)->toDateString(), (string) $r4['bon']->date_expiration, 'validité 3 mois');
        $this->assertMatchesRegularExpression('/^BA\d{6}$/', $r4['bon']->numero);
        $this->assertEquals(20.0, (float) $this->client($client->clientid)->soldefidelite, 'solde fidélité = bons disponibles');
    }

    public function test_bon_consomme_une_seule_fois_et_par_le_bon_client(): void
    {
        $client = $this->creerClient();
        $autre = $this->creerClient();
        $now = Carbon::parse('2026-10-16 10:00:00');
        $lignes = [['produitid' => $this->produitFidelite, 'qte' => 1]];
        $bon = null;
        for ($i = 0; $i < 4; $i++) {
            $d = $now->copy()->addDays($i);
            $t = $this->creerTicket($client->clientid, $d);
            $bon = $this->fidelite->appliquerVente($this->client($client->clientid), $lignes, $t, $d)['bon'] ?? $bon;
        }
        $this->assertNotNull($bon);

        $this->assertStringContainsString('autre client', (string) $this->fidelite->verifierBon($bon->numero, (int) $autre->clientid)['erreur']);
        // Bon expiré refusé (3 mois de validité : au 4ème mois il n'est plus utilisable)
        $this->assertStringContainsString('expiré', (string) $this->fidelite->verifierBon($bon->numero, (int) $client->clientid, $now->copy()->addMonths(4))['erreur']);
        $this->assertNull($this->fidelite->verifierBon($bon->numero, (int) $client->clientid, $now->copy()->addMonths(2))['erreur']);
        $this->assertStringContainsString('dépasse', (string) $this->fidelite->consommerBon($bon->numero, 999, (int) $client->clientid, 1, $now));

        $t5 = $this->creerTicket($client->clientid, $now->copy()->addDays(5));
        $this->assertNull($this->fidelite->consommerBon($bon->numero, 10, (int) $client->clientid, $t5, $now->copy()->addDays(5)));
        $this->assertStringContainsString('déjà été utilisé', (string) $this->fidelite->consommerBon($bon->numero, 10, (int) $client->clientid, $t5, $now));
        $this->assertEquals(0.0, (float) $this->client($client->clientid)->soldefidelite);

        // Numéro saisi sans préfixe ou avec zéros : même bon
        $this->assertSame($bon->id, $this->fidelite->trouverBon((string) $bon->id)->id);
        $this->assertSame($bon->id, $this->fidelite->trouverBon('ba' . str_pad((string) $bon->id, 6, '0', STR_PAD_LEFT))->id);
    }

    public function test_carte_expire_sans_deux_ventes_dans_l_annee_anniversaire(): void
    {
        $client = $this->creerClient();
        $debut = Carbon::parse('2025-03-10 10:00:00');
        $lignes = [['produitid' => $this->produitFidelite, 'qte' => 1]];

        // 3 ventes au printemps 2025 : le client est au palier 30 %
        foreach ([0, 10, 20] as $j) {
            $d = $debut->copy()->addDays($j);
            $this->fidelite->appliquerVente($this->client($client->clientid), $lignes, $this->creerTicket($client->clientid, $d), $d);
        }
        $this->assertSame(30, $this->fidelite->etat($this->client($client->clientid), Carbon::parse('2025-12-01'))['palier']);

        // Année anniversaire 10/03/2025 → 10/03/2026 : 3 ventes, carte conservée au 11/03/2026
        $etat = $this->fidelite->etat($this->client($client->clientid), Carbon::parse('2026-03-11'));
        $this->assertFalse($etat['expiree']);
        $this->assertSame(30, $etat['palier']);

        // Année 10/03/2026 → 10/03/2027 : aucune vente → au 11/03/2027 la carte est expirée, retour à 10 %
        $etat = $this->fidelite->etat($this->client($client->clientid), Carbon::parse('2027-03-11'));
        $this->assertTrue($etat['expiree']);
        $this->assertSame(10, $etat['palier']);

        // La vente suivante redémarre un cycle (nouvelle « 1ère vente »)
        $d = Carbon::parse('2027-03-12 10:00:00');
        $r = $this->fidelite->appliquerVente($this->client($client->clientid), $lignes, $this->creerTicket($client->clientid, $d), $d);
        $this->assertTrue($r['expiree']);
        $this->assertSame(1, $r['ventes']);
        $this->assertSame($d->toDateTimeString(), Carbon::parse($this->client($client->clientid)->fidelite_debut)->toDateTimeString());
    }

    public function test_retour_annule_le_bon_du_ticket_d_origine(): void
    {
        $client = $this->creerClient();
        $now = Carbon::parse('2026-10-16 10:00:00');
        $lignes = [['produitid' => $this->produitFidelite, 'qte' => 2]];
        $t4 = 0;
        $bon = null;
        for ($i = 0; $i < 4; $i++) {
            $d = $now->copy()->addDays($i);
            $t4 = $this->creerTicket($client->clientid, $d);
            $bon = $this->fidelite->appliquerVente($this->client($client->clientid), $lignes, $t4, $d)['bon'] ?? $bon;
        }
        $this->assertEquals(20.0, (float) $bon->montant);

        // Ligne du ticket d'origine (2 paires), puis retour d'une paire
        $detId = (int) DB::table('detctickets')->insertGetId([
            'cticketid' => $t4, 'produitid' => $this->produitFidelite,
            'produit2id' => DB::table('produit2s')->where('produitid', $this->produitFidelite)->value('produit2id'),
            'taxefamilleid' => 7, 'ht' => 50, 'ttc' => 50, 'qte' => 2, 'totalht' => 100, 'remise' => 0, 'remise2' => 0, 'totalhtnet' => 100,
            'taxe1' => 0, 'vtaxe1' => 0, 'taxe2' => 0, 'vtaxe2' => 0, 'taxe3' => 0, 'vtaxe3' => 0, 'taxe4' => 0, 'vtaxe4' => 0,
            'tva' => 0, 'vtva' => 0, 'totalttc' => 100, 'totalttcnet' => 100, 'qtetrans' => 0, 'siteid' => $this->siteid, 'date' => $now,
            'largeur' => 0, 'longueur' => 0, 'surface' => 0, 'ordre' => 1, 'retour' => 0, 'stocknegatif' => 0, 'modestock' => 1,
            'puttcnet' => 50, 'htachat' => 0, 'ttcachat' => 0, 'detventeid' => 0,
        ], 'detcticketid');

        $msgs = $this->fidelite->annulerPourRetour([['produitid' => $this->produitFidelite, 'qte' => -1, 'detventeid' => $detId]], $now->copy()->addDays(5));
        $this->assertCount(1, $msgs);
        $bonApres = DB::table('bons_achat')->where('id', $bon->id)->first();
        $this->assertEquals(10.0, (float) $bonApres->montant, 'une paire retournée = 10 DT repris');
        $this->assertFalse((bool) $bonApres->annule);

        $this->fidelite->annulerPourRetour([['produitid' => $this->produitFidelite, 'qte' => -1, 'detventeid' => $detId]], $now->copy()->addDays(6));
        $bonApres = DB::table('bons_achat')->where('id', $bon->id)->first();
        $this->assertTrue((bool) $bonApres->annule, 'deuxième paire retournée : bon annulé');
        $this->assertEquals(0.0, (float) $this->client($client->clientid)->soldefidelite);
    }

    public function test_client_passager_et_non_fidele_sans_remise(): void
    {
        $passager = DB::table('clients')->where('clientid', config('pos.client_passager_id'))->first();
        $this->assertSame(0, $this->fidelite->palier($passager));
        $this->assertSame(0, $this->fidelite->palier($this->creerClient(false)));
    }

    public function test_parametres_lus_depuis_retailconfigs(): void
    {
        DB::table('retailconfigs')->where('libelle', 'fidelite_montant_bon')->update(['value' => '15.500']);
        DB::table('retailconfigs')->where('libelle', 'fidelite_palier1')->update(['value' => '5']);
        RetailConfig::vider();
        $p = $this->fidelite->parametres();
        $this->assertEquals(15.5, $p['montant_bon']);
        $this->assertSame(5, $p['paliers'][0]);
        $this->assertSame(5, $this->fidelite->palier($this->creerClient()));
    }

    public function test_code128_svg(): void
    {
        $svg = Code128::svg('BA000123');
        $this->assertStringStartsWith('<svg', $svg);
        // Start B + 8 caractères + contrôle + Stop = 11 symboles de 3 barres (le Stop en a 4) = 34 barres
        $this->assertSame(34, substr_count($svg, '<rect'));
    }

    public function test_ecran_caisse_et_ticket_imprime_avec_bon(): void
    {
        // Un utilisateur administrateur dont le site a une journée de caisse ouverte
        $journal = DB::table('journalcaisses')->where(fn ($q) => $q->where('isclosed', false)->orWhereNull('isclosed'))->orderByDesc('journalcaisseid')->first();
        $this->assertNotNull($journal, 'Aucune journée ouverte en base.');
        $row = DB::table('users')->where('siteid', $journal->siteid)->where('userdroitid', 1)->first()
            ?? DB::table('users')->where('siteid', $journal->siteid)->first();
        $this->assertNotNull($row);
        $this->actingAs(User::find($row->userid));

        $this->get(route('vente.caisse.pos'))
            ->assertOk()
            ->assertSee('baBonsClient', false)
            ->assertSee('check-bon-achat', false);

        // Ticket imprimé : le bon gagné apparaît avec son numéro et son code-barres
        $ticket = DB::table('ctickets')->where('siteid', $journal->siteid)->where('enattente', false)->orderByDesc('cticketid')->first();
        $this->assertNotNull($ticket);
        $client = $this->creerClient();
        DB::table('ctickets')->where('cticketid', $ticket->cticketid)->update(['clientid' => $client->clientid]);
        $id = DB::table('bons_achat')->insertGetId([
            'clientid' => $client->clientid, 'montant' => 10, 'date_emission' => now()->toDateString(),
            'date_expiration' => now()->addMonths(3)->toDateString(), 'utilise' => false, 'annule' => false,
            'ticketid_source' => $ticket->cticketid, 'created_at' => now(), 'updated_at' => now(),
        ], 'id');

        $this->get(route('vente.tickets.show', $ticket->cticketid), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertSee('BON N° ' . $this->fidelite->numeroBon($id))
            ->assertSee('<svg', false)
            ->assertSee('Félicitations');

        // Historique « consultation article » par n° de carte
        $this->getJson(route('vente.caisse.pos.article_history', ['q' => $client->barcode]))
            ->assertOk()->assertJsonPath('success', true);
    }

    public function test_api_caisse_bons_et_verification(): void
    {
        $client = $this->creerClient();
        $now = now();
        $lignes = [['produitid' => $this->produitFidelite, 'qte' => 1]];
        $bon = null;
        for ($i = 0; $i < 4; $i++) {
            $t = $this->creerTicket($client->clientid, $now->copy()->subDays(4 - $i));
            $bon = $this->fidelite->appliquerVente($this->client($client->clientid), $lignes, $t, $now->copy()->subDays(4 - $i))['bon'] ?? $bon;
        }

        $this->getJson(route('vente.caisse.pos.bons_client', $client->clientid))
            ->assertOk()->assertJsonPath('success', true)->assertJsonPath('bons.0.numero', $bon->numero);

        $this->getJson(route('vente.caisse.pos.check_bon_achat', ['code' => $bon->numero, 'clientid' => $client->clientid]))
            ->assertOk()->assertJsonPath('success', true)->assertJsonPath('bon.montant', 10);

        $this->getJson(route('vente.caisse.pos.check_bon_achat', ['code' => 'BA999999']))
            ->assertOk()->assertJsonPath('success', false);

        $this->getJson(route('vente.caisse.pos.get_client', $client->clientid))
            ->assertOk()->assertJsonPath('client.loyalty_tier', 30)->assertJsonPath('client.num_fidelite', $client->barcode);
    }
}
