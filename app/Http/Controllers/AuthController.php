<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle login attempt.
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
            'siteid' => 'nullable|integer',
        ]);

        $credentials = [
            'login' => $request->input('login'),
            'password' => $request->input('password'),
        ];

        if (Auth::attempt($credentials)) {
            if ($request->filled('siteid') && (int) $request->input('siteid') !== (int) Auth::user()->siteid) {
                $user = Auth::user();

                // Changer de site demande le droit « Modifier Site doc vente » (updatesitevente) du rôle ;
                // le site doit exister, ne pas être bloqué et appartenir au même client que l'utilisateur.
                $siteAutorise = self::peutChoisirSite($user)
                    && $user->clientid
                    && \Illuminate\Support\Facades\DB::table('sites')
                        ->where('siteid', (int) $request->input('siteid'))
                        ->where('clientid', $user->clientid)
                        ->where('bloque', false)
                        ->exists();

                if (!$siteAutorise) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return back()->withErrors([
                        'siteid' => 'Site invalide ou non autorisé pour cet utilisateur.',
                    ])->onlyInput('login');
                }

                $user->siteid = (int) $request->input('siteid');
                $user->save();
            }

            // Toutes les opérations (ventes, stock, transferts) sont rattachées au site de l'utilisateur
            if (empty(Auth::user()->siteid)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'login' => 'Aucun site n\'est associé à ce compte. Contactez l\'administrateur.',
                ])->onlyInput('login');
            }

            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'login' => 'Identifiant ou mot de passe incorrect.',
        ])->onlyInput('login');
    }

    /**
     * Un utilisateur peut choisir son site à la connexion si son rôle n'a pas le droit
     * « Modifier Site doc vente » (updatesitevente) bloqué. Réglable dans Gestion des Droits.
     */
    private static function peutChoisirSite($user): bool
    {
        return \App\Support\Droits::roleAutorise($user->userdroitid ? (int) $user->userdroitid : null, 'updatesitevente');
    }

    /**
     * Sites proposés à la connexion (uniquement pour un rôle autorisé à changer de site)
     */
    public function getSites(Request $request)
    {
        $login = $request->input('login');
        if (!$login) {
            return response()->json([]);
        }

        $user = \App\Models\User::where('login', $login)->first();
        if (!$user || !$user->clientid || !self::peutChoisirSite($user)) {
            return response()->json([]);
        }

        $sites = \Illuminate\Support\Facades\DB::table('sites')
            ->where('clientid', $user->clientid)
            ->where('bloque', false)
            ->select('siteid', 'libelle')
            ->orderBy('libelle')
            ->get()
            // Site habituel de l'utilisateur présélectionné dans la liste
            ->map(fn ($site) => (object) ((array) $site + ['actuel' => (int) $site->siteid === (int) $user->siteid]));

        return response()->json($sites);
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
