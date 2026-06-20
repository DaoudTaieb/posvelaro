<?php

namespace App\Http\Controllers\Vente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Liste des clients.
     */
    public function index(Request $request)
    {
        $query = \Illuminate\Support\Facades\DB::table('clients');

        // Global search
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($w) use ($q) {
                $w->where('nom', 'ilike', '%' . $q . '%')
                  ->orWhere('clientcode', 'ilike', '%' . $q . '%')
                  ->orWhere('code', 'ilike', '%' . $q . '%')
                  ->orWhere('tel', 'ilike', '%' . $q . '%')
                  ->orWhere('ville', 'ilike', '%' . $q . '%')
                  ->orWhere('mf', 'ilike', '%' . $q . '%');
            });
        }

        // Column searches mapping based on data-col attributes
        $colMap = [
            '1' => 'clientcode', // Code
            '2' => 'nom', // Raison Social
            '7' => 'mf', // Code TVA
            '11' => 'ville',
            '12' => 'adressefacturation',
            '13' => 'adresselivraison',
            '14' => 'tel',
            '15' => 'rc',
            '16' => 'fax',
            '17' => 'email',
            '18' => 'clientid'
        ];

        foreach ($colMap as $colIndex => $dbCol) {
            if ($request->filled('col_' . $colIndex)) {
                $query->where($dbCol, 'ilike', '%' . $request->input('col_' . $colIndex) . '%');
            }
        }

        $clients = $query->orderBy('clientcode', 'desc')->paginate(15)->withQueryString();
        
        if ($request->ajax()) {
            return response()->json([
                'html' => view('vente.clients.partials.table_body', compact('clients'))->render(),
                'pagination' => $clients->links()->toHtml()
            ]);
        }

        return view('vente.clients.index', compact('clients'));
    }

    /**
     * Enregistre un nouveau client.
     */
    public function store(Request $request)
    {
        // Simple validation
        $request->validate([
            'raison' => 'required|string|max:255',
        ]);

        try {
            // Generate a random clientcode if not provided
            $nextId = \Illuminate\Support\Facades\DB::table('clients')->max('clientid') + 1;
            $clientCode = '411' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            \Illuminate\Support\Facades\DB::table('clients')->insert([
                'nom' => $request->raison,
                'clientcode' => $clientCode,
                'code' => $clientCode, // often both are used
                'credit' => $request->has('bloque_credit') ? false : true, // Assuming bloque means no credit
                'mf' => $request->matricule_fiscal,
                'fidelite' => $request->has('g_fidelite'),
                'tel' => $request->telephone,
                'email' => $request->email,
                'ville' => $request->ville,
                'adressefacturation' => $request->adresse,
                'adresselivraison' => $request->adresse,
                // defaults for required numeric fields
                'solde' => 0,
                'soldeinitial' => 0,
                'remise' => 0,
                'soldefidelite' => 0,
                'cumulfidelite' => 0,
                'pointfidelite' => 0,
            ]);

            return redirect()->route('vente.clients.index')->with('success', 'Client ajouté avec succès !');
        } catch (\Exception $e) {
            return redirect()->route('vente.clients.index')->with('error', 'Erreur lors de l\'ajout du client : ' . $e->getMessage());
        }
    }

    /**
     * Affiche les détails d'un client.
     */
    public function show($id)
    {
        // Détails du client
    }

    /**
     * Met à jour les informations du client.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'raison' => 'required|string|max:255',
        ]);

        try {
            \Illuminate\Support\Facades\DB::table('clients')->where('clientid', $id)->update([
                'nom' => $request->raison,
                'credit' => $request->has('bloque_credit') ? false : true,
                'mf' => $request->matricule_fiscal,
                'fidelite' => $request->has('g_fidelite'),
                'tel' => $request->telephone,
                'email' => $request->email,
                'ville' => $request->ville,
                'adressefacturation' => $request->adresse,
                'adresselivraison' => $request->adresse,
            ]);

            return redirect()->route('vente.clients.index')->with('success', 'Client mis à jour avec succès !');
        } catch (\Exception $e) {
            return redirect()->route('vente.clients.index')->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
        }
    }

    /**
     * Supprime un client.
     */
    public function destroy($id)
    {
        try {
            \Illuminate\Support\Facades\DB::table('clients')->where('clientid', $id)->delete();
            return response()->json(['success' => true, 'message' => 'Client supprimé avec succès.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur lors de la suppression : ' . $e->getMessage()], 500);
        }
    }
}
