<?php

namespace App\Http\Controllers\Parametre;

use Illuminate\Support\Facades\DB;

/**
 * Helpers communs aux contrôleurs du module Paramètres.
 */
trait ParametreHelpers
{
    /**
     * Clés des verrous consultatifs (pg_advisory_xact_lock) utilisés pour
     * sérialiser la génération des identifiants par max()+1.
     */
    protected const LOCK_USERS = 910001;
    protected const LOCK_EMPLOYEES = 910002;
    protected const LOCK_USERDROITS = 910003;
    protected const LOCK_USERDROITDETS = 910004;
    protected const LOCK_CAISSES = 910005;

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
        return (int) (auth()->user()->siteid ?? 102);
    }

    /**
     * Prend un verrou consultatif pour la durée de la transaction courante.
     * Doit être appelé à l'intérieur d'un DB::transaction().
     */
    protected function lockIds(int $key): void
    {
        DB::statement('SELECT pg_advisory_xact_lock(?)', [$key]);
    }
}
