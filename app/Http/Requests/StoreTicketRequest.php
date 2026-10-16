<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;

/**
 * Validation des données d'un ticket envoyé par la caisse (vente, retour ou mise en attente).
 * Les contrôles métier (droits du rôle, cohérence des montants, bons d'achat) restent dans
 * CaisseController / TicketService.
 */
class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // droits vérifiés par le middleware « droit:caisse » et verifierDroitsTicket()
    }

    public function rules(): array
    {
        return [
            'lignes' => 'required|array|min:1',
            'lignes.*.qte' => 'required|numeric',
            'lignes.*.prix' => 'required|numeric',
            'lignes.*.total' => 'required|numeric',
            'reglements' => 'nullable|array',
            'reglements.*.montant' => 'required|numeric',
            // Existence du client, du vendeur et des modes : une seule requête (after), pas une par règle
            'reglements.*.modereglementid' => 'required|integer',
            'clientid' => 'nullable|integer',
            'vendeurid' => 'nullable|integer',
            'reprise_cticketid' => 'nullable|integer',
        ];
    }

    /**
     * Contrôle d'existence groupé : client, vendeur et modes de règlement vérifiés en un seul
     * aller-retour avec la base (au lieu d'une requête « exists » par règle).
     */
    public function after(): array
    {
        return [function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $clientid = $this->filled('clientid') ? (int) $this->input('clientid') : null;
            $vendeurid = $this->filled('vendeurid') ? (int) $this->input('vendeurid') : null;
            $modes = collect($this->input('reglements', []))
                ->pluck('modereglementid')->filter(fn ($v) => $v !== null && $v !== '')
                ->map(fn ($v) => (int) $v)->unique()->values()->all();

            if ($clientid === null && $vendeurid === null && !$modes) {
                return;
            }

            $r = DB::selectOne(<<<'SQL'
                SELECT (?::bigint IS NULL OR EXISTS (SELECT 1 FROM clients WHERE clientid = ?::bigint)) AS client_ok,
                       (?::int IS NULL OR EXISTS (SELECT 1 FROM employees WHERE employeeid = ?::int)) AS vendeur_ok,
                       (SELECT COALESCE(array_to_json(array_agg(modereglementid)), '[]')::text
                          FROM modereglements WHERE modereglementid = ANY (?::int[])) AS modes
                SQL, [$clientid, $clientid, $vendeurid, $vendeurid, '{' . implode(',', $modes) . '}']);

            if (!$r->client_ok) {
                $validator->errors()->add('clientid', $this->messages()['clientid.exists']);
            }
            if (!$r->vendeur_ok) {
                $validator->errors()->add('vendeurid', $this->messages()['vendeurid.exists']);
            }
            $connus = array_map('intval', json_decode($r->modes, true) ?: []);
            foreach ($this->input('reglements', []) as $i => $reglement) {
                if (!in_array((int) ($reglement['modereglementid'] ?? 0), $connus, true)) {
                    $validator->errors()->add("reglements.$i.modereglementid", $this->messages()['reglements.*.modereglementid.exists']);
                }
            }
        }];
    }

    public function messages(): array
    {
        return [
            'lignes.required' => 'Le ticket est vide.',
            'clientid.exists' => 'Le client sélectionné n\'existe pas.',
            'vendeurid.exists' => 'Le vendeur sélectionné n\'existe pas.',
            'reglements.*.modereglementid.exists' => 'Mode de règlement inconnu.',
        ];
    }
}
