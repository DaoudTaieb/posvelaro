<?php

namespace App\Http\Controllers\Parametre;

use App\Http\Controllers\Controller;
use App\Support\SiteCourant;
use Illuminate\Support\Facades\DB;

/**
 * Contrôle des données du site (lecture seule) : incohérences entre tickets, règlements, journées
 * et stock, qu'elles viennent du logiciel de bureau ou de la caisse web. Rien n'est corrigé
 * automatiquement : la base est partagée avec le logiciel de bureau.
 */
class ControleDonneesController extends Controller
{
    private const LIMITE = 50;

    public function index()
    {
        $siteid = SiteCourant::id();
        $site = DB::table('sites')->where('siteid', $siteid)->value('libelle');

        $controles = [
            [
                'titre' => 'Tickets dont le total ne correspond pas aux lignes',
                'aide' => "L'entête (totalttc) diffère de la somme des lignes de plus de 0,010 DT. Souvent un ticket du bureau remis à zéro après saisie.",
                'gravite' => 'error',
                'colonnes' => ['N° ticket', 'Date', 'Total entête', 'Total lignes', 'Écart'],
                'lignes' => DB::select("
                    SELECT c.cticketid, c.cticketnumero, c.cticketdate, c.totalttc, SUM(d.totalttcnet) AS lignes
                    FROM ctickets c JOIN detctickets d ON d.cticketid = c.cticketid
                    WHERE c.siteid = ? AND c.enattente = false
                    GROUP BY c.cticketid
                    HAVING ABS(c.totalttc - SUM(d.totalttcnet)) > 0.01
                    ORDER BY c.cticketdate DESC", [$siteid]),
                'cellules' => fn ($r) => [$r->cticketnumero, $this->date($r->cticketdate), $this->dt($r->totalttc), $this->dt($r->lignes), $this->dt($r->totalttc - $r->lignes)],
            ],
            [
                'titre' => 'Tickets validés sans aucune ligne',
                'aide' => 'Ticket enregistré sans article : à vérifier ou à annuler dans le logiciel de bureau.',
                'gravite' => 'warning',
                'colonnes' => ['N° ticket', 'Date', 'Total TTC', 'Acompte'],
                'lignes' => DB::select("
                    SELECT c.cticketid, c.cticketnumero, c.cticketdate, c.totalttc, c.acompte
                    FROM ctickets c
                    WHERE c.siteid = ? AND c.enattente = false
                      AND NOT EXISTS (SELECT 1 FROM detctickets d WHERE d.cticketid = c.cticketid)
                    ORDER BY c.cticketdate DESC", [$siteid]),
                'cellules' => fn ($r) => [$r->cticketnumero, $this->date($r->cticketdate), $this->dt($r->totalttc), $this->dt($r->acompte)],
            ],
            [
                'titre' => 'Tickets payés au-delà de leur montant',
                'aide' => 'Reste à payer négatif : le client a réglé plus que le total du ticket.',
                'gravite' => 'error',
                'colonnes' => ['N° ticket', 'Date', 'Total TTC', 'Acompte', 'Reste'],
                'lignes' => DB::select("
                    SELECT cticketid, cticketnumero, cticketdate, totalttc, acompte, netapayer
                    FROM ctickets WHERE siteid = ? AND enattente = false AND netapayer < -0.001
                    ORDER BY cticketdate DESC", [$siteid]),
                'cellules' => fn ($r) => [$r->cticketnumero, $this->date($r->cticketdate), $this->dt($r->totalttc), $this->dt($r->acompte), $this->dt($r->netapayer)],
            ],
            [
                'titre' => 'Tickets avec un reste à payer',
                'aide' => 'Ventes à crédit ou avec acompte : à encaisser par « Complément d\'acompte » en caisse.',
                'gravite' => 'info',
                'colonnes' => ['N° ticket', 'Date', 'Client', 'Total TTC', 'Reste'],
                'lignes' => DB::select("
                    SELECT c.cticketid, c.cticketnumero, c.cticketdate, c.totalttc, c.netapayer, cl.nom
                    FROM ctickets c LEFT JOIN clients cl ON cl.clientid = c.clientid
                    WHERE c.siteid = ? AND c.enattente = false AND c.netapayer > 0.001
                    ORDER BY c.cticketdate DESC", [$siteid]),
                'cellules' => fn ($r) => [$r->cticketnumero, $this->date($r->cticketdate), $r->nom, $this->dt($r->totalttc), $this->dt($r->netapayer)],
            ],
            [
                'titre' => 'Journées de caisse ouvertes depuis plus d\'un jour',
                'aide' => 'Une journée doit être clôturée chaque soir pour que les états et les écarts de caisse soient justes.',
                'gravite' => 'warning',
                'colonnes' => ['N° journée', 'Caisse', 'Ouverte le', 'Ouverte par'],
                'lignes' => DB::select("
                    SELECT j.journalcaisseid, j.journalcaissenumero, j.dateouverture, c.libelle AS caisse, u.login
                    FROM journalcaisses j
                    LEFT JOIN caisses c ON c.caisseid = j.caisseid
                    LEFT JOIN users u ON u.userid = j.userid
                    WHERE j.siteid = ? AND j.isclosed = false AND j.dateouverture < CURRENT_DATE
                    ORDER BY j.dateouverture", [$siteid]),
                'cellules' => fn ($r) => [$r->journalcaissenumero ?: $r->journalcaisseid, $r->caisse, $this->date($r->dateouverture), $r->login],
            ],
            [
                'titre' => 'Tickets en attente de plus de 7 jours',
                'aide' => 'Tickets mis en attente puis jamais repris : à reprendre ou à supprimer depuis la caisse.',
                'gravite' => 'info',
                'colonnes' => ['N° ticket', 'Créé le', 'Total TTC'],
                'lignes' => DB::select("
                    SELECT cticketid, cticketnumero, datecreation, totalttc
                    FROM ctickets WHERE siteid = ? AND enattente = true AND datecreation < NOW() - INTERVAL '7 days'
                    ORDER BY datecreation", [$siteid]),
                'cellules' => fn ($r) => [$r->cticketnumero, $this->date($r->datecreation), $this->dt($r->totalttc)],
            ],
            [
                'titre' => 'Bons d\'achat fidélité expirés sans avoir été utilisés',
                'aide' => 'Information : bons émis il y a plus de 3 mois et non présentés en caisse.',
                'gravite' => 'info',
                'colonnes' => ['N° bon', 'Client', 'Montant', 'Expiré le'],
                'lignes' => DB::select("
                    SELECT b.id, b.montant, b.date_expiration, cl.nom
                    FROM bons_achat b JOIN ctickets t ON t.cticketid = b.ticketid_source
                    LEFT JOIN clients cl ON cl.clientid = b.clientid
                    WHERE t.siteid = ? AND b.utilise = false AND b.annule = false AND b.date_expiration < CURRENT_DATE
                    ORDER BY b.date_expiration DESC", [$siteid]),
                'cellules' => fn ($r) => [app(\App\Services\FideliteService::class)->numeroBon((int) $r->id), $r->nom, $this->dt($r->montant), $this->date($r->date_expiration, false)],
            ],
        ];

        foreach ($controles as &$c) {
            $c['total'] = count($c['lignes']);
            $c['lignes'] = array_map($c['cellules'], array_slice($c['lignes'], 0, self::LIMITE));
            unset($c['cellules']);
        }
        unset($c);

        $stockNegatif = (int) DB::table('stock2s')->where('siteid', $siteid)->where('qtestock', '<', 0)->count();

        return view('parametre.controle.index', [
            'controles' => $controles,
            'stockNegatif' => $stockNegatif,
            'site' => $site,
            'limite' => self::LIMITE,
        ]);
    }

    private function dt($montant): string
    {
        return number_format((float) $montant, 3, ',', ' ');
    }

    private function date($valeur, bool $heure = true): string
    {
        return $valeur ? \Carbon\Carbon::parse($valeur)->format($heure ? 'd/m/Y H:i' : 'd/m/Y') : '';
    }
}
