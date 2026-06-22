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
            $request->session()->regenerate();
            
            if ($request->filled('siteid')) {
                $user = Auth::user();
                $user->siteid = $request->input('siteid');
                $user->save();
            }

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'login' => 'Identifiant ou mot de passe incorrect.',
        ])->onlyInput('login');
    }

    /**
     * Get sites for a specific login
     */
    public function getSites(Request $request)
    {
        $login = $request->input('login');
        if (!$login) {
            return response()->json([]);
        }

        $user = \App\Models\User::where('login', $login)->first();
        if (!$user || !$user->clientid) {
            return response()->json([]);
        }

        $sites = \Illuminate\Support\Facades\DB::table('sites')
            ->where('clientid', $user->clientid)
            ->where('bloque', false)
            ->select('siteid', 'libelle')
            ->orderBy('libelle')
            ->get();

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
