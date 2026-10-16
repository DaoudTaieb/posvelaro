<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Changement de son propre mot de passe.
 *
 * Le mot de passe reste stocké tel quel dans users.password : le logiciel de bureau le lit en clair
 * (voir App\Auth\VelaroUserProvider). Un nouveau mot de passe doit faire au moins 6 caractères.
 */
class MonCompteController extends Controller
{
    public const LONGUEUR_MIN = 6;

    public function motDePasse()
    {
        return view('compte.mot-de-passe', ['longueurMin' => self::LONGUEUR_MIN]);
    }

    public function updateMotDePasse(Request $request)
    {
        $request->validate([
            'actuel' => 'required|string',
            'nouveau' => 'required|string|min:' . self::LONGUEUR_MIN . '|max:100|confirmed|different:actuel',
        ], [
            'actuel.required' => 'Saisissez votre mot de passe actuel.',
            'nouveau.min' => 'Le nouveau mot de passe doit contenir au moins ' . self::LONGUEUR_MIN . ' caractères.',
            'nouveau.confirmed' => 'La confirmation ne correspond pas au nouveau mot de passe.',
            'nouveau.different' => 'Le nouveau mot de passe doit être différent de l\'actuel.',
        ]);

        $user = Auth::user();
        if (!Auth::getProvider()->validateCredentials($user, ['password' => $request->actuel])) {
            return back()->withErrors(['actuel' => 'Mot de passe actuel incorrect.']);
        }

        DB::table('users')->where('userid', $user->userid)->update(['password' => $request->nouveau]);

        return redirect()->route('compte.mot_de_passe')->with('success', 'Votre mot de passe a été modifié.');
    }
}
