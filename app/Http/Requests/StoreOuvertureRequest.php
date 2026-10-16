<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Ouverture d'une journée de caisse : la caisse doit appartenir au site de l'utilisateur.
 */
class StoreOuvertureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // droits vérifiés par le middleware « droit:caisse »
    }

    public function rules(): array
    {
        $siteid = $this->user()->siteid ?? null;

        return [
            'caisseid' => [
                'required',
                'integer',
                Rule::exists('caisses', 'caisseid')->where(function ($q) use ($siteid) {
                    if ($siteid) {
                        $q->where('siteid', $siteid);
                    }
                }),
            ],
            'fondcaisse' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'caisseid.exists' => 'Cette caisse n\'existe pas ou n\'appartient pas à votre site.',
        ];
    }
}
