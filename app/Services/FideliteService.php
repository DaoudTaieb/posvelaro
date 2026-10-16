<?php

namespace App\Services;

use App\Support\RetailConfig;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Programme de fidélité Golden Pos.
 *
 * Règles (cahier des charges) :
 *  - remise sur les articles des sous-familles fidélité : 1ère vente 10 %, 2ème 20 %, 3ème et suivantes 30 % ;
 *  - à partir de la 4ème vente, un bon d'achat de 10 DT par paire (article de sous-famille fidélité),
 *    utilisable sur une vente suivante, valable 3 mois ;
 *  - la carte reste valide si le client réalise au moins 2 ventes par année, à compter de la date
 *    de sa 1ère vente (année anniversaire). Sinon le cycle repart de zéro (nouvelle 1ère vente).
 *
 * Le nombre de ventes est toujours calculé depuis les tickets (ctickets), jamais stocké : il reste
 * donc exact même pour les ventes saisies dans le logiciel de bureau. Seul le début du cycle
 * (clients.fidelite_debut) est mémorisé. Un retour (ticket négatif) ne compte pas comme une vente.
 */
class FideliteService
{
    /** @return array{paliers: int[], seuil_bon: int, montant_bon: float, validite_bon_mois: int, ventes_par_an: int, prefixe_bon: string} */
    public function parametres(): array
    {
        $d = config('pos.fidelite');

        return [
            'paliers' => [
                RetailConfig::int('fidelite_palier1', (int) $d['paliers'][0]),
                RetailConfig::int('fidelite_palier2', (int) $d['paliers'][1]),
                RetailConfig::int('fidelite_palier3', (int) $d['paliers'][2]),
            ],
            'seuil_bon' => max(1, RetailConfig::int('fidelite_seuil_bon', (int) $d['seuil_bon'])),
            'montant_bon' => max(0.0, RetailConfig::float('fidelite_montant_bon', (float) $d['montant_bon'])),
            'validite_bon_mois' => max(1, RetailConfig::int('fidelite_validite_bon_mois', (int) $d['validite_bon_mois'])),
            'ventes_par_an' => max(1, RetailConfig::int('fidelite_ventes_par_an', (int) $d['ventes_par_an'])),
            'prefixe_bon' => (string) $d['prefixe_bon'],
        ];
    }

    /** Le client participe-t-il au programme ? (fiche fidélité cochée, et pas le client PASSAGER) */
    public function estEligible(?object $client): bool
    {
        if (!$client || empty($client->clientid)) {
            return false;
        }
        if ((int) $client->clientid === (int) config('pos.client_passager_id')) {
            return false;
        }
        if (strtoupper(trim((string) ($client->nom ?? ''))) === 'PASSAGER') {
            return false;
        }

        return (bool) ($client->fidelite ?? false);
    }

    /**
     * État de la carte, en lecture seule (aucune écriture).
     *
     * @param  int|null $exclureTicketId  ticket à ne pas compter (celui en cours d'enregistrement)
     * @return array{eligible: bool, ventes: int, palier: int, debut: ?Carbon, expiree: bool, bons_disponibles: float}
     */
    public function etat(?object $client, ?Carbon $now = null, ?int $exclureTicketId = null): array
    {
        $now = $now ?? now();
        $p = $this->parametres();
        $etat = ['eligible' => false, 'ventes' => 0, 'palier' => 0, 'debut' => null, 'expiree' => false, 'bons_disponibles' => 0.0];

        if (!$this->estEligible($client)) {
            return $etat;
        }
        $etat['eligible'] = true;
        $etat['bons_disponibles'] = $this->soldeBons((int) $client->clientid, $now);

        $debut = !empty($client->fidelite_debut) ? Carbon::parse($client->fidelite_debut) : null;

        if ($debut) {
            // Nombre d'années anniversaire complètes écoulées depuis la 1ère vente
            $k = 0;
            while ($k < 200 && $debut->copy()->addYears($k + 1)->lte($now)) {
                $k++;
            }
            if ($k >= 1) {
                // La dernière année anniversaire complète doit contenir au moins N ventes
                $n = $this->compterVentes((int) $client->clientid, $debut->copy()->addYears($k - 1), $debut->copy()->addYears($k), $exclureTicketId);
                if ($n < $p['ventes_par_an']) {
                    $etat['expiree'] = true;
                    $debut = null;
                }
            }
        }

        $etat['debut'] = $debut;
        $etat['ventes'] = $debut ? $this->compterVentes((int) $client->clientid, $debut, null, $exclureTicketId) : 0;
        $etat['palier'] = $this->palierPourVentes($etat['ventes'], $p['paliers']);

        return $etat;
    }

    /**
     * État de la carte pour toute une liste de clients en 2 requêtes (au lieu de 3 ou 4 par client) :
     * utilisé par la recherche clients de la caisse. Mêmes règles que etat().
     *
     * @param  iterable<object> $clients
     * @return array<int, array{eligible: bool, ventes: int, palier: int, debut: ?Carbon, expiree: bool, bons_disponibles: float, bons: Collection}>
     */
    public function etatsEnLot(iterable $clients, ?Carbon $now = null): array
    {
        $now = $now ?? now();
        $p = $this->parametres();
        $eligibles = [];
        $resultats = [];
        foreach ($clients as $client) {
            $resultats[(int) $client->clientid] = ['eligible' => false, 'ventes' => 0, 'palier' => 0, 'debut' => null, 'expiree' => false, 'bons_disponibles' => 0.0, 'bons' => collect()];
            if ($this->estEligible($client)) {
                $eligibles[(int) $client->clientid] = $client;
            }
        }
        if (!$eligibles) {
            return $resultats;
        }

        // Dates des ventes validées (tickets positifs) de tous les clients éligibles
        $ventes = DB::table('ctickets')
            ->whereIn('clientid', array_keys($eligibles))
            ->where('enattente', false)
            ->where('totalttc', '>', 0)
            ->get(['clientid', 'cticketdate'])
            ->groupBy('clientid')
            ->map(fn ($lignes) => $lignes->map(fn ($l) => Carbon::parse($l->cticketdate))->all());

        // Bons d'achat utilisables, groupés par client
        $bons = DB::table('bons_achat')
            ->whereIn('clientid', array_keys($eligibles))
            ->where('utilise', false)
            ->where('annule', false)
            ->where('montant', '>', 0)
            ->whereDate('date_expiration', '>=', $now->toDateString())
            ->orderBy('date_expiration')
            ->get()
            ->map(function ($bon) {
                $bon->numero = $this->numeroBon((int) $bon->id);
                return $bon;
            })
            ->groupBy('clientid');

        $compter = fn (array $dates, Carbon $debut, ?Carbon $fin) => count(array_filter($dates, fn (Carbon $d) => $d->gte($debut) && ($fin === null || $d->lt($fin))));

        foreach ($eligibles as $clientid => $client) {
            $dates = $ventes[$clientid] ?? [];
            $etat = &$resultats[$clientid];
            $etat['eligible'] = true;
            $etat['bons'] = ($bons[$clientid] ?? collect())->values();
            $etat['bons_disponibles'] = round((float) $etat['bons']->sum('montant'), 3);

            $debut = !empty($client->fidelite_debut) ? Carbon::parse($client->fidelite_debut) : null;
            if ($debut) {
                $k = 0;
                while ($k < 200 && $debut->copy()->addYears($k + 1)->lte($now)) {
                    $k++;
                }
                if ($k >= 1 && $compter($dates, $debut->copy()->addYears($k - 1), $debut->copy()->addYears($k)) < $p['ventes_par_an']) {
                    $etat['expiree'] = true;
                    $debut = null;
                }
            }
            $etat['debut'] = $debut;
            $etat['ventes'] = $debut ? $compter($dates, $debut, null) : 0;
            $etat['palier'] = $this->palierPourVentes($etat['ventes'], $p['paliers']);
            unset($etat);
        }

        return $resultats;
    }

    /** Remise (%) à appliquer aux articles fidélité pour la prochaine vente de ce client. */
    public function palier(?object $client): int
    {
        return $this->etat($client)['palier'];
    }

    /**
     * Enregistre les effets fidélité d'un ticket validé (vente positive) : démarrage / redémarrage
     * du cycle, émission du bon d'achat à partir de la Nième vente, recalcul du solde fidélité.
     * À appeler dans la transaction du ticket, après l'insertion de ctickets et detctickets.
     *
     * @param  array<int, array{produitid: mixed, qte: mixed}> $detLignes
     * @return array{ventes: int, palier_applique: int, expiree: bool, bon: ?object}|null  null si client non éligible
     */
    public function appliquerVente(object $client, array $detLignes, int $cticketid, ?Carbon $now = null): ?array
    {
        $now = $now ?? now();
        $p = $this->parametres();
        $etat = $this->etat($client, $now, $cticketid);
        if (!$etat['eligible']) {
            return null;
        }

        if ($etat['debut'] === null) {
            // Première vente du client, ou carte expirée : nouveau cycle
            DB::table('clients')->where('clientid', $client->clientid)->update(['fidelite_debut' => $now]);
        }

        $ventesApres = $etat['ventes'] + 1;
        $bon = null;

        if ($ventesApres >= $p['seuil_bon'] && $p['montant_bon'] > 0) {
            $paires = $this->pairesFidelite($detLignes);
            if ($paires > 0) {
                $montant = round($paires * $p['montant_bon'], 3);
                $id = DB::table('bons_achat')->insertGetId([
                    'clientid' => $client->clientid,
                    'montant' => $montant,
                    'date_emission' => $now->toDateString(),
                    'date_expiration' => $now->copy()->addMonths($p['validite_bon_mois'])->toDateString(),
                    'utilise' => false,
                    'annule' => false,
                    'ticketid_source' => $cticketid,
                    'motif' => sprintf('%d paire(s) x %s DT (vente n° %d du cycle)', $paires, number_format($p['montant_bon'], 3, '.', ''), $ventesApres),
                    'created_at' => $now,
                    'updated_at' => $now,
                ], 'id');
                $bon = DB::table('bons_achat')->where('id', $id)->first();
                $bon->numero = $this->numeroBon($id);
            }
        }

        $this->recalculerSolde((int) $client->clientid, $now);

        return [
            'ventes' => $ventesApres,
            'palier_applique' => $etat['palier'],
            'expiree' => $etat['expiree'],
            'bon' => $bon,
        ];
    }

    /**
     * Retour d'articles fidélité : les bons émis par le ticket d'origine et non encore consommés
     * sont réduits du montant correspondant aux paires retournées (annulés si plus rien ne reste).
     *
     * @param  array<int, array{produitid: mixed, qte: mixed, detventeid?: mixed}> $detLignes  lignes du ticket de retour
     * @return array<int, string>  messages d'information pour le caissier
     */
    public function annulerPourRetour(array $detLignes, ?Carbon $now = null): array
    {
        $now = $now ?? now();
        $p = $this->parametres();
        $messages = [];

        // Paires fidélité retournées, regroupées par ticket d'origine
        $parTicket = [];
        foreach ($detLignes as $det) {
            $qte = (float) ($det['qte'] ?? 0);
            $detventeid = (int) ($det['detventeid'] ?? 0);
            if ($qte >= 0 || $detventeid <= 0) {
                continue;
            }
            $origine = DB::table('detctickets as d')
                ->join('produits as p', 'p.produitid', '=', 'd.produitid')
                ->join('sousfamilles as sf', 'sf.sousfamilleid', '=', 'p.sousfamilleid')
                ->where('d.detcticketid', $detventeid)
                ->where('sf.is_loyalty_enabled', true)
                ->value('d.cticketid');
            if ($origine) {
                $parTicket[(int) $origine] = ($parTicket[(int) $origine] ?? 0) + abs($qte);
            }
        }

        foreach ($parTicket as $cticketid => $paires) {
            $bons = DB::table('bons_achat')
                ->where('ticketid_source', $cticketid)
                ->where('utilise', false)
                ->where('annule', false)
                ->lockForUpdate()
                ->get();

            $aReprendre = round($paires * $p['montant_bon'], 3);
            foreach ($bons as $bon) {
                if ($aReprendre <= 0.0005) {
                    break;
                }
                $montant = (float) $bon->montant;
                $reprise = min($montant, $aReprendre);
                $nouveau = round($montant - $reprise, 3);
                $aReprendre = round($aReprendre - $reprise, 3);

                DB::table('bons_achat')->where('id', $bon->id)->update([
                    'montant' => $nouveau,
                    'annule' => $nouveau <= 0.0005,
                    'motif' => trim(($bon->motif ?? '') . ' | Retour du ' . $now->format('d/m/Y') . ' : -' . number_format($reprise, 3, '.', '') . ' DT'),
                    'updated_at' => $now,
                ]);
                $messages[] = $nouveau <= 0.0005
                    ? sprintf("Bon d'achat %s annulé suite au retour.", $this->numeroBon($bon->id))
                    : sprintf("Bon d'achat %s réduit à %s DT suite au retour.", $this->numeroBon($bon->id), number_format($nouveau, 3, '.', ''));
                $this->recalculerSolde((int) $bon->clientid, $now);
            }
        }

        return $messages;
    }

    /** Bons utilisables du client (non consommés, non annulés, non expirés). */
    public function bonsDisponibles(int $clientid, ?Carbon $now = null): Collection
    {
        $now = $now ?? now();

        return DB::table('bons_achat')
            ->where('clientid', $clientid)
            ->where('utilise', false)
            ->where('annule', false)
            ->where('montant', '>', 0)
            ->whereDate('date_expiration', '>=', $now->toDateString())
            ->orderBy('date_expiration')
            ->get()
            ->map(function ($bon) {
                $bon->numero = $this->numeroBon((int) $bon->id);
                return $bon;
            });
    }

    public function soldeBons(int $clientid, ?Carbon $now = null): float
    {
        $now = $now ?? now();

        return round((float) DB::table('bons_achat')
            ->where('clientid', $clientid)
            ->where('utilise', false)
            ->where('annule', false)
            ->whereDate('date_expiration', '>=', $now->toDateString())
            ->sum('montant'), 3);
    }

    /** Synchronise clients.soldefidelite (affiché en caisse) avec les bons disponibles. */
    public function recalculerSolde(int $clientid, ?Carbon $now = null): void
    {
        DB::table('clients')->where('clientid', $clientid)->update([
            'soldefidelite' => $this->soldeBons($clientid, $now),
        ]);
    }

    /** Numéro imprimé / scanné d'un bon : préfixe + identifiant sur 6 chiffres (ex. BA000123). */
    public function numeroBon(int $id): string
    {
        return $this->parametres()['prefixe_bon'] . str_pad((string) $id, 6, '0', STR_PAD_LEFT);
    }

    /** Retrouve un bon depuis un numéro saisi ou scanné (« BA000123 », « 000123 » ou « 123 »). */
    public function trouverBon(?string $code): ?object
    {
        $id = $this->idDepuisCode($code);
        if ($id === null) {
            return null;
        }
        $bon = DB::table('bons_achat')->where('id', $id)->first();
        if ($bon) {
            $bon->numero = $this->numeroBon((int) $bon->id);
        }

        return $bon;
    }

    /**
     * Vérifie un bon avant encaissement. Retourne un message d'erreur, ou null si utilisable.
     *
     * @return array{erreur: ?string, bon: ?object}
     */
    public function verifierBon(?string $code, ?int $clientid = null, ?Carbon $now = null): array
    {
        $now = $now ?? now();
        $bon = $this->trouverBon($code);
        if (!$bon) {
            return ['erreur' => 'Bon d\'achat introuvable : ' . trim((string) $code), 'bon' => null];
        }
        if ($bon->annule) {
            return ['erreur' => "Le bon d'achat {$bon->numero} a été annulé (retour d'articles).", 'bon' => $bon];
        }
        if ($bon->utilise) {
            return ['erreur' => "Le bon d'achat {$bon->numero} a déjà été utilisé.", 'bon' => $bon];
        }
        if ($bon->date_expiration && $bon->date_expiration < $now->toDateString()) {
            return ['erreur' => "Le bon d'achat {$bon->numero} est expiré depuis le " . Carbon::parse($bon->date_expiration)->format('d/m/Y') . '.', 'bon' => $bon];
        }
        if ($clientid && (int) $bon->clientid !== (int) $clientid) {
            return ['erreur' => "Le bon d'achat {$bon->numero} appartient à un autre client.", 'bon' => $bon];
        }
        if ((float) $bon->montant <= 0.0005) {
            return ['erreur' => "Le bon d'achat {$bon->numero} n'a plus de solde.", 'bon' => $bon];
        }

        return ['erreur' => null, 'bon' => $bon];
    }

    /**
     * Consomme un bon lors de l'encaissement (mode de règlement « Bon d'achat »).
     * Un numéro qui n'a pas le format Golden Pos et n'existe pas est accepté tel quel (bon externe
     * géré par le logiciel de bureau). Retourne un message d'erreur, ou null si OK.
     */
    public function consommerBon(?string $code, float $montant, ?int $clientid, int $cticketid, ?Carbon $now = null): ?string
    {
        $now = $now ?? now();
        $code = trim((string) $code);
        if ($code === '') {
            return null;
        }

        $id = $this->idDepuisCode($code);
        $bon = $id !== null ? DB::table('bons_achat')->where('id', $id)->lockForUpdate()->first() : null;
        if (!$bon) {
            $prefixe = strtoupper($this->parametres()['prefixe_bon']);
            if ($prefixe !== '' && str_starts_with(strtoupper($code), $prefixe)) {
                return "Bon d'achat introuvable : {$code}";
            }
            return null; // bon externe
        }

        $verif = $this->verifierBon($code, $clientid, $now);
        if ($verif['erreur']) {
            return $verif['erreur'];
        }
        if ($montant > (float) $bon->montant + 0.0005) {
            return sprintf("Le montant dépasse la valeur du bon d'achat %s (%s DT).", $this->numeroBon((int) $bon->id), number_format((float) $bon->montant, 3, '.', ''));
        }

        DB::table('bons_achat')->where('id', $bon->id)->update([
            'utilise' => true,
            'cticketid_utilisation' => $cticketid,
            'date_utilisation' => $now,
            'updated_at' => $now,
        ]);
        $this->recalculerSolde((int) $bon->clientid, $now);

        return null;
    }

    /** Sous-familles ouvrant droit à la fidélité, pour l'affichage. */
    public function sousFamillesFidelite(): Collection
    {
        return DB::table('sousfamilles')->where('is_loyalty_enabled', true)->orderBy('sousfamillelibelle')->get(['sousfamilleid', 'sousfamillelibelle']);
    }

    // ------------------------------------------------------------------------------------------

    private function palierPourVentes(int $ventes, array $paliers): int
    {
        if ($ventes <= 0) {
            return (int) $paliers[0];
        }
        if ($ventes === 1) {
            return (int) $paliers[1];
        }

        return (int) $paliers[2];
    }

    /** Ventes validées (tickets positifs, hors tickets en attente) du client sur une période. */
    private function compterVentes(int $clientid, Carbon $debut, ?Carbon $fin, ?int $exclureTicketId): int
    {
        $q = DB::table('ctickets')
            ->where('clientid', $clientid)
            ->where('enattente', false)
            ->where('totalttc', '>', 0)
            ->where('cticketdate', '>=', $debut);
        if ($fin) {
            $q->where('cticketdate', '<', $fin);
        }
        if ($exclureTicketId) {
            $q->where('cticketid', '!=', $exclureTicketId);
        }

        return (int) $q->count();
    }

    /** Nombre de paires (quantités vendues) appartenant à une sous-famille fidélité. */
    private function pairesFidelite(array $detLignes): float
    {
        $parProduit = [];
        foreach ($detLignes as $det) {
            $qte = (float) ($det['qte'] ?? 0);
            if ($qte > 0 && !empty($det['produitid'])) {
                $parProduit[(int) $det['produitid']] = ($parProduit[(int) $det['produitid']] ?? 0) + $qte;
            }
        }
        if (!$parProduit) {
            return 0.0;
        }
        $eligibles = DB::table('produits as p')
            ->join('sousfamilles as sf', 'sf.sousfamilleid', '=', 'p.sousfamilleid')
            ->whereIn('p.produitid', array_keys($parProduit))
            ->where('sf.is_loyalty_enabled', true)
            ->pluck('p.produitid');

        $paires = 0.0;
        foreach ($eligibles as $produitid) {
            $paires += $parProduit[(int) $produitid] ?? 0;
        }

        return $paires;
    }

    private function idDepuisCode(?string $code): ?int
    {
        $code = strtoupper(trim((string) $code));
        if ($code === '') {
            return null;
        }
        $prefixe = strtoupper($this->parametres()['prefixe_bon']);
        if ($prefixe !== '' && str_starts_with($code, $prefixe)) {
            $code = substr($code, strlen($prefixe));
        }
        if (!ctype_digit($code) || strlen($code) > 18) {
            return null;
        }

        return (int) $code;
    }
}
