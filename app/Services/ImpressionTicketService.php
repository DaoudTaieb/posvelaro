<?php

namespace App\Services;

use App\Support\EscPos;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

/**
 * Impression thermique des tickets de caisse (ESC/POS).
 *
 * Réglages par caisse, dans la table caisseconfigs du logiciel de bureau (page « Impression ») :
 *  - ticketprinter             : nom de l'imprimante Windows (réglage du bureau, réutilisé tel quel) ;
 *  - goldenpos_impression_mode : navigateur (défaut) | windows | reseau ;
 *  - goldenpos_imprimante_reseau : adresse IP[:port] d'une imprimante réseau (port 9100 par défaut) ;
 *  - goldenpos_colonnes        : caractères par ligne (48 en 80 mm, 32 en 58 mm) ;
 *  - goldenpos_accents         : True = page de code PC858, False = texte sans accents ;
 *  - goldenpos_tiroir          : True = ouverture du tiroir-caisse à chaque ticket encaissé.
 *
 * Mode « windows » : le serveur web tourne sur le poste de caisse auquel l'imprimante est branchée
 * (raccourci « Golden Pos » / Apache local) ; le flux brut est remis au spouleur Windows.
 */
class ImpressionTicketService
{
    public const MODES = ['navigateur' => 'Navigateur (fenêtre d\'impression)', 'windows' => 'Imprimante Windows (ESC/POS)', 'reseau' => 'Imprimante réseau ESC/POS (port 9100)'];

    private const DEFAUTS = [
        'goldenpos_impression_mode' => ['Golden Pos : mode d\'impression du ticket', 'navigateur', 0],
        'goldenpos_imprimante_reseau' => ['Golden Pos : imprimante réseau (IP:port)', '', 0],
        'goldenpos_colonnes' => ['Golden Pos : caractères par ligne', '48', 1],
        'goldenpos_accents' => ['Golden Pos : imprimer les accents (PC858)', 'False', 2],
        'goldenpos_tiroir' => ['Golden Pos : ouvrir le tiroir-caisse', 'False', 2],
    ];

    private const CLE_CACHE = 'goldenpos.caisseconfigs.';

    /** @return array{caisseid: int, mode: string, imprimante: string, reseau: string, colonnes: int, accents: bool, tiroir: bool} */
    public function configuration(int $caisseid): array
    {
        // Relu à l'ouverture de la caisse et à chaque ticket : cache d'une minute, vidé à l'enregistrement
        // (un changement fait depuis le logiciel de bureau s'applique au plus tard une minute après).
        $valeurs = \Illuminate\Support\Facades\Cache::remember(
            self::CLE_CACHE . $caisseid,
            60,
            fn () => DB::table('caisseconfigs')->where('caisseid', $caisseid)->pluck('value', 'libelle')->all()
        );
        $mode = (string) ($valeurs['goldenpos_impression_mode'] ?? 'navigateur');

        return [
            'caisseid' => $caisseid,
            'mode' => array_key_exists($mode, self::MODES) ? $mode : 'navigateur',
            'imprimante' => trim((string) ($valeurs['ticketprinter'] ?? '')),
            'reseau' => trim((string) ($valeurs['goldenpos_imprimante_reseau'] ?? '')),
            'colonnes' => max(24, min(64, (int) ($valeurs['goldenpos_colonnes'] ?? 48) ?: 48)),
            'accents' => $this->booleen($valeurs['goldenpos_accents'] ?? 'False'),
            'tiroir' => $this->booleen($valeurs['goldenpos_tiroir'] ?? 'False'),
        ];
    }

    /** Enregistre les réglages d'une caisse (crée les lignes caisseconfigs manquantes). */
    public function enregistrerConfiguration(int $caisseid, array $c): void
    {
        DB::transaction(function () use ($caisseid, $c) {
            DB::statement('SELECT pg_advisory_xact_lock(?)', [910006]);
            // ticketprinter est aussi lu par le logiciel de bureau : on ne l'efface jamais
            if (trim((string) ($c['imprimante'] ?? '')) !== '') {
                $this->ecrire($caisseid, 'ticketprinter', trim((string) $c['imprimante']), 'Imprimant ticket', 0);
            }
            $this->ecrire($caisseid, 'goldenpos_impression_mode', array_key_exists($c['mode'] ?? '', self::MODES) ? $c['mode'] : 'navigateur');
            $this->ecrire($caisseid, 'goldenpos_imprimante_reseau', trim((string) ($c['reseau'] ?? '')));
            $this->ecrire($caisseid, 'goldenpos_colonnes', (string) max(24, min(64, (int) ($c['colonnes'] ?? 48))));
            $this->ecrire($caisseid, 'goldenpos_accents', !empty($c['accents']) ? 'True' : 'False');
            $this->ecrire($caisseid, 'goldenpos_tiroir', !empty($c['tiroir']) ? 'True' : 'False');
        });
        \Illuminate\Support\Facades\Cache::forget(self::CLE_CACHE . $caisseid);
    }

    /**
     * Imprime un ticket sur l'imprimante de sa caisse.
     *
     * @return array{ok: bool, mode: string, message: string}
     */
    public function imprimerTicket(int $cticketid, bool $ouvrirTiroir = false, ?int $caisseid = null): array
    {
        // Caisse connue de l'appelant (validation du ticket) : pas de relecture du ticket
        $caisseid = $caisseid ?: (int) DB::table('ctickets')->where('cticketid', $cticketid)->value('caisseid');
        $config = $this->configuration($caisseid);
        if ($config['mode'] === 'navigateur') {
            return ['ok' => false, 'mode' => 'navigateur', 'message' => 'Impression par le navigateur.'];
        }

        try {
            $flux = $this->ticketEscPos($cticketid, $config, $ouvrirTiroir && $config['tiroir']);
            $this->envoyer($config, $flux);

            return ['ok' => true, 'mode' => $config['mode'], 'message' => 'Ticket imprimé.'];
        } catch (\Throwable $e) {
            Log::warning('Impression ticket ' . $cticketid . ' : ' . $e->getMessage());

            return ['ok' => false, 'mode' => $config['mode'], 'message' => 'Impression impossible : ' . $e->getMessage()];
        }
    }

    /** Ticket de test pour vérifier le réglage d'une caisse. */
    public function imprimerTest(int $caisseid): array
    {
        $config = $this->configuration($caisseid);
        if ($config['mode'] === 'navigateur') {
            return ['ok' => false, 'mode' => 'navigateur', 'message' => 'Cette caisse imprime par le navigateur : choisissez une imprimante Windows ou réseau.'];
        }
        $caisse = DB::table('caisses')->where('caisseid', $caisseid)->first();
        $p = new EscPos($config['colonnes'], $config['accents']);
        $p->alignement(1)->gras()->taille(2)->ligne('GOLDEN POS')->taille(1)->gras(false)
            ->ligne('Ticket de test')->ligne(($caisse->libelle ?? 'Caisse') . ' - ' . now()->format('d/m/Y H:i'))
            ->separateur()->alignement(0)
            ->ligne('Accents : é è à ç ô ù €')
            ->gaucheDroite('Colonnes', (string) $config['colonnes'])
            ->gaucheDroite('Mode', $config['mode'])
            ->separateur()->alignement(1)->codeBarres('BA000123')->retour(2)->couper();

        try {
            $this->envoyer($config, $p->flux());
            return ['ok' => true, 'mode' => $config['mode'], 'message' => 'Ticket de test envoyé à l\'imprimante.'];
        } catch (\Throwable $e) {
            return ['ok' => false, 'mode' => $config['mode'], 'message' => 'Impression impossible : ' . $e->getMessage()];
        }
    }

    /** Flux ESC/POS complet d'un ticket (même contenu que le reçu navigateur vente.tickets.show). */
    public function ticketEscPos(int $cticketid, ?array $config = null, bool $ouvrirTiroir = false): string
    {
        $ticket = DB::table('ctickets')->where('cticketid', $cticketid)->first();
        abort_unless($ticket, 404, 'Ticket introuvable');
        $config ??= $this->configuration((int) $ticket->caisseid);

        $societe = \App\Support\Societe::courante();
        $caisse = DB::table('caisses')->where('caisseid', $ticket->caisseid)->first();
        $caissier = DB::table('users')->where('userid', $ticket->caissierid ?: $ticket->userid)->value('login');
        $vendeur = DB::table('employees')->where('employeeid', $ticket->employeeid)->first();
        $client = DB::table('clients')->where('clientid', $ticket->clientid)->first();
        $lignes = DB::table('detctickets as d')
            ->leftJoin('produits as p', 'p.produitid', '=', 'd.produitid')
            ->leftJoin('produit2s as v', 'v.produit2id', '=', 'd.produit2id')
            ->leftJoin('couleurs as c', 'c.couleurid', '=', 'v.couleurid')
            ->leftJoin('tailles as t', 't.tailleid', '=', 'v.tailleid')
            ->where('d.cticketid', $cticketid)
            ->orderBy('d.ordre')
            ->get(['d.*', 'p.produitlibelle', 'p.reference', 'p.produitcode', 'c.couleurlibelle', 't.taillelibelle']);
        $reglements = app(ReglementService::class)->reglementsDuTicket($cticketid);
        $fidelite = app(FideliteService::class);
        $bons = DB::table('bons_achat')->where('ticketid_source', $cticketid)->where('annule', false)->orderBy('id')->get();

        $p = new EscPos($config['colonnes'], $config['accents']);
        $dt = fn ($v) => number_format((float) $v, 3, '.', '');

        // En-tête
        $p->alignement(1)->gras()->taille(2)->ligne(mb_strtoupper(trim((string) ($societe->raison ?? 'GOLDEN POS'))))->taille(1)->gras(false);
        if (!empty($societe->adresse)) {
            $p->ligne(mb_strimwidth($societe->adresse, 0, $config['colonnes'] * 2, ''));
        }
        if (!empty($societe->tel)) {
            $p->ligne('Tel : ' . $societe->tel);
        }
        if (!empty($societe->mf)) {
            $p->ligne('MF : ' . $societe->mf);
        }
        $p->separateur()
            ->gras()->ligne('TICKET N° ' . $ticket->cticketnumero)->gras(false)
            ->ligne(Carbon::parse($ticket->cticketdate)->format('d/m/Y H:i:s'))
            ->codeBarres((string) $ticket->cticketnumero, 50)
            ->alignement(0)
            ->ligne('Caisse : ' . ($caisse->libelle ?? $ticket->caisseid) . '   Caissier : ' . $caissier);
        $nomVendeur = trim(($vendeur->nom ?? '') . (strcasecmp(trim($vendeur->nom ?? ''), trim($vendeur->prenom ?? '')) === 0 ? '' : ' ' . ($vendeur->prenom ?? '')));
        if ($nomVendeur !== '') {
            $p->ligne('Vendeur : ' . $nomVendeur);
        }
        $p->ligne('Client : ' . (trim(($client->nom ?? '') . ' ' . ($client->prenom ?? '')) ?: 'PASSAGER'))->separateur();

        // Lignes
        foreach ($lignes as $l) {
            $qte = (float) $l->qte;
            $total = (float) ($l->totalttcnet ?? $l->totalttc);
            $puNet = (float) ($l->puttcnet ?: ($qte ? $total / $qte : 0));
            $variante = trim(($l->couleurlibelle ?? '') . ' ' . ($l->taillelibelle ?? ''));
            $p->gras()->ligne(mb_strimwidth(mb_strtoupper((string) ($l->produitlibelle ?? 'Article')), 0, $config['colonnes'], ''))->gras(false);
            $p->gaucheDroite(trim(($l->reference ?? '') . ($variante !== '' ? ' - ' . $variante : '')), ((float) $l->remise > 0 ? '-' . rtrim(rtrim(number_format((float) $l->remise, 2, '.', ''), '0'), '.') . '% ' : '') . $dt($l->ttc));
            $p->gaucheDroite('  ' . rtrim(rtrim(number_format($qte, 3, '.', ''), '0'), '.') . ' x ' . $dt($puNet), $dt($total));
        }
        $p->separateur();

        // Totaux
        $remise = (float) ($ticket->totalavremise ?? 0) - (float) ($ticket->totalttc ?? 0);
        if ($remise > 0.0005) {
            $p->gaucheDroite('Total brut', $dt($ticket->totalavremise))->gaucheDroite('Remise', '-' . $dt($remise));
        }
        $p->gras()->taille(2)->gaucheDroite('TOTAL', $dt($ticket->totalttc), 2)->taille(1)->gras(false)
            ->gaucheDroite(count($lignes) . ' article(s)', 'DT');

        // Règlements
        if (count($reglements) > 0) {
            $p->separateur();
            foreach ($reglements as $r) {
                $p->gaucheDroite($r->mode_libelle ?? 'Règlement', $dt($r->montant));
            }
            if ((float) $ticket->netapayer > 0.0005) {
                $p->gras()->gaucheDroite('Reste à payer', $dt($ticket->netapayer))->gras(false);
            }
        }

        // Bons d'achat fidélité gagnés
        foreach ($bons as $bon) {
            $numero = $fidelite->numeroBon((int) $bon->id);
            $p->separateur()->alignement(1)->gras()->ligne('Félicitations !')->gras(false)
                ->ligne("Bon d'achat de " . $dt($bon->montant) . ' DT')
                ->gras()->ligne('BON N° ' . $numero)->gras(false)
                ->codeBarres($numero, 50)
                ->ligne("Valable jusqu'au " . Carbon::parse($bon->date_expiration)->format('d/m/Y'))
                ->alignement(0);
        }

        // Pied
        $p->separateur()->alignement(1)
            ->ligne('CONSERVEZ VOTRE TICKET POUR ECHANGE')
            ->ligne('DANS 48H SAUF PERIODE SOLDES')
            ->ligne('MERCI DE VOTRE VISITE')
            ->retour(3);
        if ($ouvrirTiroir) {
            $p->tiroir();
        }

        return $p->couper()->flux();
    }

    /** Envoie un flux brut à l'imprimante configurée (Windows ou réseau). */
    public function envoyer(array $config, string $flux): void
    {
        if ($config['mode'] === 'reseau') {
            $this->envoyerReseau($config['reseau'], $flux);
            return;
        }
        if ($config['mode'] === 'windows') {
            $this->envoyerWindows($config['imprimante'], $flux);
            return;
        }
        throw new \RuntimeException('Mode d\'impression navigateur : rien à envoyer.');
    }

    private function envoyerReseau(string $adresse, string $flux): void
    {
        if ($adresse === '') {
            throw new \RuntimeException('Adresse de l\'imprimante réseau non renseignée.');
        }
        [$hote, $port] = array_pad(explode(':', $adresse, 2), 2, '9100');
        $socket = @fsockopen($hote, (int) $port ?: 9100, $code, $erreur, 3);
        if (!$socket) {
            throw new \RuntimeException("Imprimante réseau {$hote}:{$port} injoignable ({$erreur}).");
        }
        stream_set_timeout($socket, 5);
        $ecrits = fwrite($socket, $flux);
        fclose($socket);
        if ($ecrits !== strlen($flux)) {
            throw new \RuntimeException('Envoi incomplet à l\'imprimante réseau.');
        }
    }

    private function envoyerWindows(string $imprimante, string $flux): void
    {
        if ($imprimante === '') {
            throw new \RuntimeException('Nom de l\'imprimante Windows non renseigné.');
        }
        $fichier = tempnam(sys_get_temp_dir(), 'gpticket');
        file_put_contents($fichier, $flux);
        try {
            $resultat = Process::timeout(30)->run([
                'powershell', '-NoProfile', '-NonInteractive', '-ExecutionPolicy', 'Bypass',
                '-File', resource_path('scripts/impression-brute.ps1'),
                '-Imprimante', $imprimante,
                '-Fichier', $fichier,
            ]);
            if (!$resultat->successful()) {
                $message = trim($resultat->errorOutput()) ?: trim($resultat->output());
                throw new \RuntimeException($message !== '' ? mb_strimwidth($message, 0, 300, '…') : 'Erreur du spouleur Windows.');
            }
        } finally {
            @unlink($fichier);
        }
    }

    private function ecrire(int $caisseid, string $libelle, string $valeur, ?string $caption = null, ?int $type = null): void
    {
        $existe = DB::table('caisseconfigs')->where('caisseid', $caisseid)->where('libelle', $libelle)->exists();
        if ($existe) {
            DB::table('caisseconfigs')->where('caisseid', $caisseid)->where('libelle', $libelle)->update(['value' => $valeur]);
            return;
        }
        [$captionDefaut, , $typeDefaut] = self::DEFAUTS[$libelle] ?? [$libelle, '', 0];
        $caption ??= $captionDefaut;
        $type ??= $typeDefaut;
        DB::table('caisseconfigs')->insert([
            'caisseconfigid' => \App\Support\Identifiants::prochain('caisseconfigs', 'caisseconfigid'),
            'libelle' => $libelle,
            'caption' => $caption,
            'caisseid' => $caisseid,
            'value' => $valeur,
            'typevalueid' => $type,
            'pageid' => 1,
            'pagelibelle' => 'Impression',
            'typeaffichage' => $type,
            'tablename' => null,
            'tableid' => null,
            'tablelibelle' => null,
            'ordreaffichage' => 90,
            'libelletooltip' => $caption,
            'description' => 'Paramètre Golden Pos',
        ]);
    }

    private function booleen($valeur): bool
    {
        return in_array(strtolower(trim((string) $valeur)), ['true', '1', 'on', 'oui'], true);
    }
}
