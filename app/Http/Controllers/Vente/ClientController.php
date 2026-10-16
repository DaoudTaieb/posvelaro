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
        // Le libellé du tarif provient de la table tarifs (clients.tarifid)
        $query = \Illuminate\Support\Facades\DB::table('clients')
            ->leftJoin('tarifs', 'clients.tarifid', '=', 'tarifs.tarifid')
            ->select('clients.*', 'tarifs.libelle as tarif');

        // Global search (code est un entier : cast en texte pour ILIKE)
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($w) use ($q) {
                $w->where('clients.nom', 'ilike', '%' . $q . '%')
                  ->orWhere('clients.clientcode', 'ilike', '%' . $q . '%')
                  ->orWhereRaw('CAST(clients.code AS TEXT) ILIKE ?', ['%' . $q . '%'])
                  ->orWhere('clients.tel', 'ilike', '%' . $q . '%')
                  ->orWhere('clients.ville', 'ilike', '%' . $q . '%')
                  ->orWhere('clients.mf', 'ilike', '%' . $q . '%');
            });
        }

        // Column searches mapping (colonnes numériques castées en texte pour ILIKE)
        $colMap = [
            'f_code' => 'clients.clientcode',
            'f_nom' => 'clients.nom',
            'f_tarif' => 'tarifs.libelle',
            'f_remise' => 'clients.remise',
            'f_tva' => 'clients.mf',
            'f_solde' => 'clients.solde',
            'f_soldedep' => 'clients.soldeinitial',
            'f_ville' => 'clients.ville',
            'f_adrfact' => 'clients.adressefacturation',
            'f_adrliv' => 'clients.adresselivraison',
            'f_tel' => 'clients.tel',
            'f_rc' => 'clients.rc',
            'f_fax' => 'clients.fax',
            'f_email' => 'clients.email',
            'f_id' => 'clients.clientid',
            'f_soldefid' => 'clients.soldefidelite',
            'f_cumulfid' => 'clients.cumulfidelite',
            'f_pointfid' => 'clients.pointfidelite'
        ];

        foreach ($colMap as $reqKey => $dbCol) {
            if ($request->filled($reqKey)) {
                $query->whereRaw('CAST(' . $dbCol . ' AS TEXT) ILIKE ?', ['%' . $request->input($reqKey) . '%']);
            }
        }

        // Filtre date solde de départ
        if ($request->filled('f_datesd')) {
            try {
                $query->whereDate('clients.datesoldeinitial', \Carbon\Carbon::parse($request->input('f_datesd'))->format('Y-m-d'));
            } catch (\Exception $e) {
                // Date invalide : filtre ignoré
            }
        }

        // Specialized boolean filters
        if ($request->filled('f_credit')) {
            $val = strtolower($request->f_credit);
            if ($val == 'bloqué' || $val == 'bloque') $query->where('clients.credit', false);
            elseif ($val == 'actif') $query->where('clients.credit', true);
        }
        if ($request->filled('f_fidelite')) {
            $val = strtolower($request->f_fidelite);
            if ($val == 'oui') $query->where('clients.fidelite', true);
            elseif ($val == 'non') $query->where('clients.fidelite', false);
        }

        if ($request->ajax()) {
            $totalClients = (clone $query)->count();
            $totalSolde = (clone $query)->sum('clients.solde');
            
            $clients = $query->orderBy('clients.clientcode', 'desc')->paginate(15);
            $html = view('vente.clients.partials.table_body', compact('clients'))->render();

            return response()->json([
                'html' => $html,
                'pagination' => $clients->links()->toHtml(),
                'kpis' => [
                    'nb_clients' => number_format((float)($totalClients ?? 0), 0, ',', ' '),
                    'total_solde' => number_format((float)($totalSolde ?? 0), 3, ',', ' ')
                ]
            ]);
        }
        
        $totalClients = (clone $query)->count();
        $totalSolde = (clone $query)->sum('clients.solde');

        $kpis = [
            'nb_clients' => number_format((float)($totalClients ?? 0), 0, ',', ' '),
            'total_solde' => number_format((float)($totalSolde ?? 0), 3, ',', ' ')
        ];

        $clients = $query->orderBy('clients.clientcode', 'desc')->paginate(15);
        return view('vente.clients.index', compact('clients', 'kpis'));
    }

    /**
     * Enregistre un nouveau client.
     */
    public function store(Request $request)
    {
        // Simple validation
        $request->validate([
            'raison' => 'required|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'carte_fid' => 'nullable|string|max:50',
        ]);

        try {
            // N° de carte fidélité = colonne barcode (partagée avec le logiciel de bureau), unique
            $carte = trim((string) $request->input('carte_fid', ''));
            if ($carte !== '' && \Illuminate\Support\Facades\DB::table('clients')->where('barcode', $carte)->exists()) {
                return redirect()->route('vente.clients.index')->with('error', "Le n° de carte fidélité {$carte} est déjà attribué à un autre client.");
            }

            // Generate a random clientcode if not provided
            $nextId = \Illuminate\Support\Facades\DB::table('clients')->max('clientid') + 1;
            $clientCode = '411' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            \Illuminate\Support\Facades\DB::table('clients')->insert([
                'nom' => $request->raison,
                'prenom' => $request->prenom,
                'barcode' => $carte !== '' ? $carte : null,
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
                // Colonnes NOT NULL sans valeur par défaut (mêmes valeurs que PosController::storeClient)
                'bloque' => 0,
                'clientfamilleid' => \Illuminate\Support\Facades\DB::table('clientfamilles')->value('clientfamilleid') ?? \Illuminate\Support\Facades\DB::table('clientfamilles')->insertGetId(['libelle' => 'Général', 'bloque' => 0], 'clientfamilleid'),
                'echeance' => 0,
                'impaye' => 0,
                'plafonecheance' => 0,
                'plafonsolde' => 0,
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
            'prenom' => 'nullable|string|max:255',
            'carte_fid' => 'nullable|string|max:50',
        ]);

        try {
            $carte = trim((string) $request->input('carte_fid', ''));
            if ($carte !== '' && \Illuminate\Support\Facades\DB::table('clients')->where('barcode', $carte)->where('clientid', '!=', $id)->exists()) {
                return redirect()->route('vente.clients.index')->with('error', "Le n° de carte fidélité {$carte} est déjà attribué à un autre client.");
            }

            \Illuminate\Support\Facades\DB::table('clients')->where('clientid', $id)->update([
                'nom' => $request->raison,
                'prenom' => $request->prenom,
                'barcode' => $carte !== '' ? $carte : null,
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
        if (!ctype_digit((string) $id)) {
            return response()->json(['success' => false, 'message' => 'Client introuvable.'], 404);
        }

        // Le client PASSAGER (clientid 1) ne peut jamais être supprimé
        if ((int) $id === 1) {
            return response()->json(['success' => false, 'message' => 'Le client PASSAGER ne peut pas être supprimé.'], 422);
        }

        // Les FK ctickets/creglements -> clients sont en ON DELETE CASCADE :
        // refuser la suppression d'un client ayant des tickets ou des règlements
        $hasTickets = \Illuminate\Support\Facades\DB::table('ctickets')->where('clientid', $id)->exists();
        $hasReglements = \Illuminate\Support\Facades\DB::table('creglements')->where('clientid', $id)->exists();
        if ($hasTickets || $hasReglements) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer ce client : il possède des tickets ou des règlements.',
            ], 422);
        }

        try {
            \Illuminate\Support\Facades\DB::table('clients')->where('clientid', $id)->delete();
            return response()->json(['success' => true, 'message' => 'Client supprimé avec succès.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur lors de la suppression : ' . $e->getMessage()], 500);
        }
    }
}
