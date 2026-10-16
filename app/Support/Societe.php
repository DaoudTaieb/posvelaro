<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Coordonnées de la société (table societes du logiciel de bureau) pour les tickets et les SMS.
 *
 * Seules les colonnes texte utiles sont lues : la ligne contient aussi des images (logo, en-tête,
 * cachet…) et des mots de passe qu'il est inutile de transférer depuis la base à chaque ticket.
 * Mise en cache 10 minutes : ces informations ne changent pratiquement jamais.
 */
class Societe
{
    private const COLONNES = ['societeid', 'raison', 'libelle', 'adresse', 'adresse2', 'ville', 'codepostal', 'tel', 'fax', 'email', 'mf', 'rc'];

    public static function courante(): ?object
    {
        $ligne = Cache::remember('goldenpos.societe', 600, function () {
            $societe = DB::table('societes')->orderBy('societeid')->first(self::COLONNES);

            return $societe ? (array) $societe : [];
        });

        return $ligne ? (object) $ligne : null;
    }

    public static function vider(): void
    {
        Cache::forget('goldenpos.societe');
    }
}
