<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;

/**
 * Site (magasin) de l'utilisateur connecté.
 *
 * Remplace l'ancien repli « siteid ?? 102 » : un utilisateur sans site ne doit jamais
 * travailler silencieusement sur le magasin Velaro. La connexion refuse déjà ce cas
 * (AuthController) ; ici on arrête proprement la requête si cela arrive malgré tout.
 */
class SiteCourant
{
    public static function id(): int
    {
        $siteid = (int) (Auth::user()->siteid ?? 0);
        abort_if($siteid <= 0, 403, 'Aucun site n\'est associé à votre compte utilisateur. Contactez l\'administrateur.');

        return $siteid;
    }

    /** Variante sans exception, pour les contextes où l'absence de site est gérée par l'appelant. */
    public static function idOuNull(): ?int
    {
        $siteid = (int) (Auth::user()->siteid ?? 0);

        return $siteid > 0 ? $siteid : null;
    }
}
