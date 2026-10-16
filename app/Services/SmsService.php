<?php

namespace App\Services;

use App\Support\RetailConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Envoi de SMS via l'API Bip SMS (bulksms.bipsms.tn) et journal des envois (table sms_envois).
 *
 * Configuration, par ordre de priorité : retailconfigs (bipsms_key, bipsms_sender, bipsms_url),
 * puis config/services.php (variables BIPSMS_* du fichier .env).
 */
class SmsService
{
    /** @return array{url: string, key: ?string, sender: string} */
    public function configuration(): array
    {
        $c = config('services.bipsms');

        return [
            'url' => (string) RetailConfig::get('bipsms_url', $c['url']),
            'key' => RetailConfig::get('bipsms_key', $c['key'] ?: null),
            'sender' => (string) RetailConfig::get('bipsms_sender', $c['sender'] ?: 'GOLDENPOS'),
        ];
    }

    public function estConfigure(): bool
    {
        return !empty($this->configuration()['key']);
    }

    /**
     * Numéro au format international sans « + » (ex. 21698765432). Un numéro tunisien à 8 chiffres
     * est préfixé par 216. Retourne null si le numéro n'est pas exploitable.
     */
    public function normaliserTelephone(?string $tel): ?string
    {
        $t = preg_replace('/\D+/', '', (string) $tel) ?? '';
        if (str_starts_with($t, '00')) {
            $t = substr($t, 2);
        }
        if (strlen($t) === 8) {
            $t = '216' . $t;
        }
        if (strlen($t) < 10 || strlen($t) > 15) {
            return null;
        }

        return $t;
    }

    /**
     * Envoie un SMS et journalise le résultat.
     *
     * @param  array{clientid?: int|null, cticketid?: int|null} $contexte
     * @return array{success: bool, message: string}
     */
    public function envoyer(?string $telephone, string $message, array $contexte = []): array
    {
        $tel = $this->normaliserTelephone($telephone);
        if ($tel === null) {
            return ['success' => false, 'message' => 'Numéro de téléphone invalide.'];
        }
        $message = trim($message);
        if ($message === '') {
            return ['success' => false, 'message' => 'Le message est vide.'];
        }

        $conf = $this->configuration();
        if (empty($conf['key'])) {
            $this->journaliser($tel, $message, 'non_configure', null, $contexte);
            return ['success' => false, 'message' => 'Service SMS non configuré : renseignez la clé API Bip SMS (Paramètres > Général ou fichier .env).'];
        }

        try {
            $response = Http::timeout(15)->get($conf['url'], [
                'fct' => 'sms',
                'key' => $conf['key'],
                'sender' => $conf['sender'],
                'mobile' => $tel,
                'sms' => $message,
                'date' => date('d/m/Y'),
                'heure' => date('H:i:s'),
            ]);
            $body = $response->body();
            $ok = $response->successful() && $this->reponseAcceptee($body);

            $this->journaliser($tel, $message, $ok ? 'envoye' : 'echec', $body, $contexte);

            return $ok
                ? ['success' => true, 'message' => 'SMS envoyé avec succès.']
                : ['success' => false, 'message' => "L'opérateur SMS a refusé l'envoi : " . $this->resumerReponse($body)];
        } catch (\Throwable $e) {
            Log::warning('Bip SMS : ' . $e->getMessage());
            $this->journaliser($tel, $message, 'erreur', $e->getMessage(), $contexte);

            return ['success' => false, 'message' => 'Impossible de joindre le service SMS : ' . $e->getMessage()];
        }
    }

    /** Message proposé après la validation d'un ticket. */
    public function messageApresVente(?string $nomClient, $numeroTicket, float $totalTtc, bool $premiereVente, ?object $bon = null): string
    {
        $magasin = $this->nomMagasin();
        $client = trim((string) $nomClient);
        $salut = $client !== '' ? "Bonjour {$client}, " : 'Bonjour, ';
        $montant = number_format($totalTtc, 3, '.', '');

        $texte = $premiereVente
            ? "{$salut}bienvenue chez {$magasin} ! Merci pour votre premier achat (ticket N° {$numeroTicket}, {$montant} DT). Votre carte fidélité est activée."
            : "{$salut}merci pour votre fidélité chez {$magasin}. Ticket N° {$numeroTicket} : {$montant} DT.";

        if ($bon) {
            $texte .= sprintf(' Vous avez gagné un bon d\'achat de %s DT (N° %s), valable jusqu\'au %s.',
                number_format((float) $bon->montant, 3, '.', ''), $bon->numero ?? $bon->id, \Carbon\Carbon::parse($bon->date_expiration)->format('d/m/Y'));
        }

        return $texte;
    }

    public function nomMagasin(): string
    {
        $societe = DB::table('societes')->orderBy('societeid')->first();

        return trim((string) ($societe->raison ?? $societe->libelle ?? '')) ?: (string) config('app.name', 'Golden Pos');
    }

    // ------------------------------------------------------------------------------------------

    /** L'API renvoie un XML <status_code>200</status_code> (ou « 200 » brut) en cas de succès. */
    private function reponseAcceptee(string $body): bool
    {
        $b = trim($body);
        if (preg_match('/<status_code>\s*200\s*<\/status_code>/i', $b)) {
            return true;
        }
        if (preg_match('/^200(\D|$)/', $b)) {
            return true;
        }
        $json = json_decode($b, true);
        if (is_array($json)) {
            $status = $json['status_code'] ?? $json['status'] ?? $json['code'] ?? null;
            return (string) $status === '200';
        }

        return false;
    }

    private function resumerReponse(string $body): string
    {
        $b = trim(strip_tags($body));
        $b = preg_replace('/\s+/', ' ', $b) ?? $b;

        return $b === '' ? 'réponse vide' : mb_strimwidth($b, 0, 160, '…');
    }

    private function journaliser(string $tel, string $message, string $statut, ?string $reponse, array $contexte): void
    {
        try {
            DB::table('sms_envois')->insert([
                'clientid' => $contexte['clientid'] ?? null,
                'cticketid' => $contexte['cticketid'] ?? null,
                'userid' => auth()->id(),
                'siteid' => auth()->user()->siteid ?? null,
                'telephone' => $tel,
                'message' => $message,
                'statut' => $statut,
                'reponse' => $reponse !== null ? mb_substr($reponse, 0, 2000) : null,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Journal SMS indisponible : ' . $e->getMessage());
        }
    }
}
