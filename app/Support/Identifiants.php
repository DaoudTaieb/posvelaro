<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Génération d'identifiants compatible avec le logiciel de bureau.
 *
 * Les tables du bureau ont une séquence PostgreSQL (DEFAULT nextval(...)) que le bureau utilise
 * pour ses insertions, mais certaines lignes ont été écrites en max()+1. Pour ne jamais provoquer
 * de doublon, ni côté web ni côté bureau, le prochain identifiant est le plus grand de :
 *   - max(colonne) + 1 ;
 *   - nextval(séquence) ;
 * puis la séquence est réalignée sur l'identifiant choisi (setval).
 *
 * À appeler dans une transaction : un verrou consultatif par table sérialise les créations web.
 */
class Identifiants
{
    public static function prochain(string $table, string $colonne): int
    {
        DB::statement('SELECT pg_advisory_xact_lock(?)', [crc32('goldenpos-id-' . $table) & 0x7FFFFFFF]);

        $id = (int) (DB::table($table)->max($colonne) ?? 0) + 1;
        $sequence = DB::selectOne('SELECT pg_get_serial_sequence(?, ?) AS s', [$table, $colonne])->s ?? null;
        if ($sequence) {
            $id = max($id, (int) DB::selectOne('SELECT nextval(?::regclass) AS v', [$sequence])->v);
            DB::statement('SELECT setval(?::regclass, ?, true)', [$sequence, $id]);
        }

        return $id;
    }

    /**
     * Réserve $nombre identifiants consécutifs et renvoie le premier (insertion de plusieurs lignes).
     */
    public static function plage(string $table, string $colonne, int $nombre): int
    {
        $premier = self::prochain($table, $colonne);
        if ($nombre > 1) {
            $sequence = DB::selectOne('SELECT pg_get_serial_sequence(?, ?) AS s', [$table, $colonne])->s ?? null;
            if ($sequence) {
                DB::statement('SELECT setval(?::regclass, ?, true)', [$sequence, $premier + $nombre - 1]);
            }
        }

        return $premier;
    }

    /** Réaligne la séquence d'une table sur max(colonne), si elle est en retard. */
    public static function synchroniserSequence(string $table, string $colonne): void
    {
        $sequence = DB::selectOne('SELECT pg_get_serial_sequence(?, ?) AS s', [$table, $colonne])->s ?? null;
        if (!$sequence) {
            return;
        }
        $max = (int) (DB::table($table)->max($colonne) ?? 0);
        $etat = DB::selectOne("SELECT last_value, is_called FROM {$sequence}");
        $dernier = $etat->is_called ? (int) $etat->last_value : (int) $etat->last_value - 1;
        if ($max > 0 && $dernier < $max) {
            DB::statement('SELECT setval(?::regclass, ?, true)', [$sequence, $max]);
        }
    }
}
