<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'reglements.*.modereglementid' => 'required|integer|exists:modereglements,modereglementid',
            'clientid' => 'nullable|integer|exists:clients,clientid',
            'vendeurid' => 'nullable|integer|exists:employees,employeeid',
            'reprise_cticketid' => 'nullable|integer',
        ];
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
