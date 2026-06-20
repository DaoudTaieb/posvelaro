<?php

namespace App\Http\Controllers\Vente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class JourneeController extends Controller
{
    /**
     * Affiche l'interface d'ouverture de journée.
     */
    public function ouverture()
    {
        // Récupérer les caisses du site de l'utilisateur ou toutes les caisses
        $siteId = Auth::user()->siteid;
        
        $caissesQuery = DB::table('caisses')->select('caisseid', 'libelle', 'numero');
        
        if ($siteId) {
            $caissesQuery->where('siteid', $siteId);
        }
        
        $caisses = $caissesQuery->get();

        return view('vente.journee.ouverture', compact('caisses'));
    }

    /**
     * Enregistre l'ouverture de la journée (création du journalcaisse).
     */
    public function storeOuverture(Request $request)
    {
        $request->validate([
            'caisseid' => 'required|integer',
            'fondcaisse' => 'required|numeric|min:0',
        ]);

        $user = Auth::user();
        
        // Vérifier si une journée est déjà ouverte pour cette caisse
        $isAlreadyOpen = DB::table('journalcaisses')
            ->where('caisseid', $request->caisseid)
            ->where('isclosed', false)
            ->exists();

        if ($isAlreadyOpen) {
            return back()->with('error', 'Une journée est déjà ouverte pour cette caisse. Veuillez la clôturer d\'abord.');
        }

        $caisse = DB::table('caisses')->where('caisseid', $request->caisseid)->first();

        // Créer l'entrée dans journalcaisses avec tous les champs requis (NOT NULL)
        DB::table('journalcaisses')->insert([
            'caisseid' => $request->caisseid,
            'fondcaisse' => $request->fondcaisse,
            'dateouverture' => now(),
            'userid' => $user->userid,
            'employeeid' => $user->employeeid ?? 0,
            'caissierclotureid' => 0,
            'siteid' => $user->siteid ?? ($caisse->siteid ?? 0),
            'isclosed' => false,
            'montantcloture' => 0,
            'montanttheorique' => 0,
            'envoyee' => false,
            'agencebid' => $user->agencebid ?? ($caisse->agencebid ?? 0),
        ]);

        return back()->with('success', 'La journée a été ouverte avec succès pour cette caisse !');
    }
}
