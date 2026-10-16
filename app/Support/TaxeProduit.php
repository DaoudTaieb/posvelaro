<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Famille de taxe et taux de TVA de vente d'un produit (produits.taxefamilleid → taxefamilles.tvavente).
 * Remplace le taux de 19 % codé en dur dans les documents de transfert.
 */
class TaxeProduit
{
    /** @var array<int, array{taxefamilleid: int, tva: float}> */
    private static array $cache = [];

    /** @return array{taxefamilleid: int, tva: float} */
    public static function pour($produitid): array
    {
        $id = (int) $produitid;
        if (!isset(self::$cache[$id])) {
            $row = DB::table('produits as p')
                ->leftJoin('taxefamilles as t', 't.taxefamilleid', '=', 'p.taxefamilleid')
                ->where('p.produitid', $id)
                ->first(['p.taxefamilleid', 't.tvavente']);

            self::$cache[$id] = [
                'taxefamilleid' => (int) ($row->taxefamilleid ?? 1),
                'tva' => $row && $row->tvavente !== null ? (float) $row->tvavente : 19.0,
            ];
        }

        return self::$cache[$id];
    }

    /** Prix HT à partir d'un prix TTC. */
    public static function ht(float $ttc, float $tva): float
    {
        return $ttc / (1 + $tva / 100);
    }
}
