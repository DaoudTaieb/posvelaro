<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Lecture des paramètres de la table retailconfigs (partagée avec le logiciel de bureau).
 *
 * Une valeur absente ou vide renvoie la valeur par défaut fournie. Les valeurs sont gardées pour la
 * durée de la requête, et en cache une minute entre les requêtes (base distante : la table complète
 * n'est plus relue à chaque recherche client ou vente). Le cache est vidé à l'enregistrement de la page
 * « Général » ; une modification faite depuis le logiciel de bureau s'applique au plus tard une minute après.
 */
class RetailConfig
{
    public static function get(string $libelle, mixed $defaut = null): mixed
    {
        $valeur = self::toutes()[$libelle] ?? null;
        if ($valeur === null || trim((string) $valeur) === '') {
            return $defaut;
        }

        return $valeur;
    }

    public static function int(string $libelle, int $defaut): int
    {
        $v = self::get($libelle);
        return is_numeric($v) ? (int) $v : $defaut;
    }

    public static function float(string $libelle, float $defaut): float
    {
        $v = self::get($libelle);
        if ($v === null) {
            return $defaut;
        }
        $v = str_replace(',', '.', (string) $v);
        return is_numeric($v) ? (float) $v : $defaut;
    }

    public static function bool(string $libelle, bool $defaut): bool
    {
        $v = self::get($libelle);
        if ($v === null) {
            return $defaut;
        }
        return in_array(strtolower(trim((string) $v)), ['true', '1', 'on', 'oui', 'yes'], true);
    }

    private const CLE_CACHE = 'goldenpos.retailconfigs';

    public static function vider(): void
    {
        if (app()->bound('retailconfigs.cache')) {
            app()->forgetInstance('retailconfigs.cache');
        }
        Cache::forget(self::CLE_CACHE);
    }

    /** @return array<string, string|null> */
    private static function toutes(): array
    {
        if (!app()->bound('retailconfigs.cache')) {
            $map = Cache::remember(self::CLE_CACHE, 60, function () {
                $map = [];
                // En cas de libellé en double, la première ligne (par ordre d'id) l'emporte
                foreach (DB::table('retailconfigs')->orderBy('retailconfigid')->get(['libelle', 'value']) as $row) {
                    if ($row->libelle !== null && !array_key_exists($row->libelle, $map)) {
                        $map[$row->libelle] = $row->value;
                    }
                }

                return $map;
            });
            app()->instance('retailconfigs.cache', $map);
        }

        return app('retailconfigs.cache');
    }
}
