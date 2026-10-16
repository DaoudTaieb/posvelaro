<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Montants physiques comptés par le caissier à la clôture de la journée.
 */
class StoreClotureRequest extends FormRequest
{
    /** Champs « physiques » de journalcaisses saisis à la clôture. */
    public const MONTANTS = [
        'totalespecephys', 'totalchequephys', 'totaltpephys', 'totalcontrebonphys', 'totaltkrestophys',
        'totalbonconventionphys', 'totalregavoirphys', 'totalcouponphys', 'totalregautrephys',
    ];

    public function authorize(): bool
    {
        return true; // droits vérifiés par le middleware « droit:caisse,clot.caisse »
    }

    public function rules(): array
    {
        $rules = ['journalcaisseid' => 'required|integer'];
        foreach (self::MONTANTS as $champ) {
            $rules[$champ] = 'nullable|numeric|min:0';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            '*.numeric' => 'Les montants comptés doivent être des nombres.',
            '*.min' => 'Les montants comptés ne peuvent pas être négatifs.',
        ];
    }
}
