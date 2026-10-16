<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Lecture des paramètres de la table retailconfigs (partagée avec le logiciel de bureau).
 *
 * Une valeur absente ou vide renvoie la valeur par défaut fournie. Les valeurs sont mises en cache
 * pour la durée de la requête (instance liée au conteneur, remise à zéro à chaque démarrage).
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

    public static function vider(): void
    {
        if (app()->bound('retailconfigs.cache')) {
            app()->forgetInstance('retailconfigs.cache');
        }
    }

    /** @return array<string, string|null> */
    private static function toutes(): array
    {
        if (!app()->bound('retailconfigs.cache')) {
            $map = [];
            // En cas de libellé en double, la première ligne (par ordre d'id) l'emporte
            foreach (DB::table('retailconfigs')->orderBy('retailconfigid')->get(['libelle', 'value']) as $row) {
                if ($row->libelle !== null && !array_key_exists($row->libelle, $map)) {
                    $map[$row->libelle] = $row->value;
                }
            }
            app()->instance('retailconfigs.cache', $map);
        }

        return app('retailconfigs.cache');
    }
}
