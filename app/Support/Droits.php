<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Lecture des droits de l'utilisateur connecté (tables userdroits / userdroitdets / typedroits).
 *
 * Deux sortes de règles :
 *  - les indicateurs du rôle : 'caisse' (userdroits.notcaisse) et 'backoffice' (userdroits.notbackoffice) ;
 *  - les abréviations de typedroits (ex. 'cons.stock', 'supp.client') : bloquées quand userdroitdets.bloque = true.
 */
class Droits
{
    /** Règles portées par une colonne booléenne "not..." de userdroits. */
    private const INDICATEURS = [
        'caisse' => 'notcaisse',
        'backoffice' => 'notbackoffice',
    ];

    private const LIBELLES_INDICATEURS = [
        'caisse' => 'Accès à la caisse',
        'backoffice' => 'Accès au back-office',
    ];

    /**
     * Cache par requête : [userid => ['role' => object|null, 'bloques' => [abreviation => libelle]]].
     * Rattaché au conteneur (et non statique) pour être remis à zéro à chaque démarrage de l'application.
     */
    private static function cache(): \ArrayObject
    {
        if (!app()->bound('droits.cache')) {
            app()->instance('droits.cache', new \ArrayObject());
        }
        return app('droits.cache');
    }

    public static function autorise(string $regle): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        $infos = self::charger($user);

        if (isset(self::INDICATEURS[$regle])) {
            $colonne = self::INDICATEURS[$regle];
            return !($infos['role']->{$colonne} ?? false);
        }

        return !isset($infos['bloques'][$regle]);
    }

    public static function bloque(string $regle): bool
    {
        return !self::autorise($regle);
    }

    /** Message affiché à l'utilisateur quand une règle le bloque. */
    public static function message(string $regle): string
    {
        $libelle = self::LIBELLES_INDICATEURS[$regle] ?? null;
        if ($libelle === null) {
            $libelle = DB::table('typedroits')->where('abreviation', $regle)->value('libelle') ?? $regle;
        }
        return 'Opération non autorisée : ' . $libelle . '.';
    }

    public static function viderCache(): void
    {
        self::cache()->exchangeArray([]);
    }

    private static function charger($user): array
    {
        $cache = self::cache();
        $userid = $user->userid;
        if (isset($cache[$userid])) {
            return $cache[$userid];
        }

        $userdroitid = $user->userdroitid
            ?? DB::table('users')->where('userid', $userid)->value('userdroitid');

        $role = $userdroitid ? DB::table('userdroits')->where('userdroitid', $userdroitid)->first() : null;

        $bloques = $userdroitid
            ? DB::table('userdroitdets as d')
                ->join('typedroits as t', 't.typedroitid', '=', 'd.typedroitid')
                ->where('d.userdroitid', $userdroitid)
                ->where('d.bloque', true)
                ->pluck('t.libelle', 't.abreviation')
                ->all()
            : [];

        return $cache[$userid] = ['role' => $role, 'bloques' => $bloques];
    }
}
