<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Recherche de la journée de caisse ouverte, avec une règle unique pour toute l'application
 * (écran caisse, validation de ticket, complément d'acompte, état et clôture de journée).
 *
 * Une journée est ouverte quand journalcaisses.isclosed vaut false ou NULL (le logiciel de
 * bureau peut laisser la colonne à NULL).
 *
 * Ordre de recherche, toujours limité au site de l'utilisateur :
 *  1. la journée que l'utilisateur a lui-même ouverte ;
 *  2. sinon la journée ouverte la plus récente du site (plusieurs caissiers sur une même caisse :
 *     les tickets portent alors le caissierid de chacun).
 */
class JourneeCaisseService
{
    public function journeeOuverte(object $user, bool $verrouiller = false): ?object
    {
        $siteid = (int) ($user->siteid ?? 0);
        if ($siteid <= 0) {
            return null;
        }

        return $this->requete($siteid, $verrouiller)->where('userid', $user->userid)->first()
            ?? $this->requete($siteid, $verrouiller)->first();
    }

    private function requete(int $siteid, bool $verrouiller)
    {
        $q = DB::table('journalcaisses')
            ->where('siteid', $siteid)
            ->where(fn ($w) => $w->where('isclosed', false)->orWhereNull('isclosed'))
            ->orderByDesc('journalcaisseid');

        return $verrouiller ? $q->lockForUpdate() : $q;
    }
}
