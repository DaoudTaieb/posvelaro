<?php

namespace App\Http\Controllers\Parametre;

use Illuminate\Support\Facades\DB;

/**
 * Helpers communs aux contrôleurs du module Paramètres.
 */
trait ParametreHelpers
{
    /*
     * Identifiants des nouvelles lignes : App\Support\Identifiants::prochain() (max()+1 et séquence
     * du logiciel de bureau, séquence réalignée), qui prend lui-même son verrou consultatif.
     */

    /**
     * Valide un identifiant de route (entier positif tenant dans un integer Postgres), sinon 404.
     */
    protected function routeId($id): int
    {
        $id = (string) $id;
        abort_unless(ctype_digit($id) && strlen($id) <= 10 && (int) $id <= 2147483647, 404);

        return (int) $id;
    }

    /**
     * Site de l'utilisateur connecté.
     */
    protected function userSiteId(): int
    {
        return \App\Support\SiteCourant::id();
    }
}
