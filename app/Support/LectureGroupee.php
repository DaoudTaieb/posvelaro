<?php

namespace App\Support;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Plusieurs lectures en UN seul aller-retour avec la base.
 *
 *   ['familles' => $f, 'sites' => $s] = LectureGroupee::listes([
 *       'familles' => DB::table('familles')->orderBy('famillelibelle'),
 *       'sites'    => DB::table('sites')->orderBy('libelle'),
 *   ]);
 *
 * Chaque requête devient une sous-requête agrégée en JSON. Avec une base distante, cinq listes de
 * filtres coûtent ainsi un aller-retour au lieu de cinq. Les données sont lues à chaque appel (pas de cache).
 *
 * Réservé aux listes de référence et aux totaux : les nombres décimaux reviennent en nombres PHP
 * (et non en chaînes « 10.000 » comme avec PDO), les dates gardent le format « AAAA-MM-JJ HH:MM:SS ».
 */
class LectureGroupee
{
    /**
     * @param  array<string, Builder> $requetes  nom => requête (l'ordre ORDER BY de chaque requête est conservé)
     * @return array<string, Collection<int, object>>
     */
    public static function listes(array $requetes): array
    {
        if (!$requetes) {
            return [];
        }

        $colonnes = [];
        $bindings = [];
        foreach ($requetes as $nom => $requete) {
            if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', (string) $nom)) {
                throw new \InvalidArgumentException("Nom de lecture invalide : {$nom}");
            }
            $colonnes[] = "(SELECT COALESCE(json_agg(t), '[]')::text FROM ({$requete->toSql()}) t) AS \"{$nom}\"";
            array_push($bindings, ...$requete->getBindings());
        }

        $ligne = (array) DB::selectOne('SELECT ' . implode(",\n", $colonnes), $bindings);

        $resultat = [];
        foreach (array_keys($requetes) as $nom) {
            $lignes = json_decode($ligne[$nom] ?? '[]') ?: [];
            foreach ($lignes as $objet) {
                self::normaliser($objet);
            }
            $resultat[$nom] = collect($lignes);
        }

        return $resultat;
    }

    /** Dates JSON « 2026-10-16T12:30:00 » remises au format renvoyé par PDO « 2026-10-16 12:30:00 ». */
    private static function normaliser(object $objet): void
    {
        foreach ($objet as $cle => $valeur) {
            if (is_string($valeur) && strlen($valeur) >= 19 && $valeur[10] === 'T'
                && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $valeur)) {
                $objet->{$cle} = substr_replace($valeur, ' ', 10, 1);
            }
        }
    }
}
