<?php

namespace App\Http\Middleware;

use App\Support\Droits;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Usage : ->middleware('droit:backoffice,cons.stock')
 * Toutes les règles listées doivent être autorisées pour le rôle de l'utilisateur.
 */
class VerifierDroit
{
    public function handle(Request $request, Closure $next, string ...$regles): Response
    {
        foreach ($regles as $regle) {
            if (Droits::autorise($regle)) {
                continue;
            }

            $message = Droits::message($regle);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 403);
            }

            // Pour une action (POST/PUT/DELETE) on revient sur la page précédente avec le message ;
            // pour une page (GET) on affiche l'erreur 403 pour éviter une boucle de redirections.
            if (!$request->isMethod('GET')) {
                return redirect()->back()->with('error', $message);
            }

            abort(403, $message);
        }

        return $next($request);
    }
}
