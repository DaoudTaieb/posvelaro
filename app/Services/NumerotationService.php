<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Numéros de documents de la caisse.
 *
 * Le logiciel de bureau numérote les tickets par max()+1 sur ctickets.cticketnumero (pas de séquence) :
 * la caisse web suit la même série. Un verrou consultatif PostgreSQL, pris pour la durée de la
 * transaction, empêche deux caisses web d'obtenir le même numéro. À appeler dans une transaction.
 */
class NumerotationService
{
    private const LOCK_TICKET_NUMERO = 7710001;

    public function prochainNumeroTicket(): int
    {
        DB::statement('SELECT pg_advisory_xact_lock(?)', [self::LOCK_TICKET_NUMERO]);
        $max = DB::table('ctickets')->max('cticketnumero');

        // Base vide uniquement : première série de l'année (format AA000001)
        return $max ? ((int) $max + 1) : (int) (date('y') . '000001');
    }
}
