<?php

namespace App\Http\Controllers\Parametre;

use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CaisseController extends Controller
{
    use ParametreHelpers;

    public function index()
    {
        $siteid = $this->userSiteId(); // Site de l'utilisateur connecté

        $caisses = DB::table('caisses as c')
            ->leftJoin('sites as s', 'c.siteid', '=', 's.siteid')
            ->leftJoin('agencebs as a', 'c.agencebid', '=', 'a.agencebid')
            ->leftJoin('clients as cl', 'c.clientid', '=', 'cl.clientid')
            ->leftJoin('stations as st', 'c.machineid', '=', 'st.stationid')
            ->where('c.siteid', $siteid)
            ->select(
                'c.*',
                's.libelle as site_libelle',
                'a.libelle as agence_libelle',
                'cl.nom as client_nom',
                'st.libelle as station_libelle'
            )
            ->orderBy('c.caisseid')
            ->get();

        $sites = DB::table('sites')->where('siteid', $siteid)->orderBy('siteid')->get();
        $agences = DB::table('agencebs')->orderBy('agencebid')->get();
        $clients = DB::table('clients')->orderBy('clientid')->get();
        $stations = DB::table('stations')->orderBy('stationid')->get();

        return view('parametre.caisse.index', compact('caisses', 'sites', 'agences', 'clients', 'stations'));
    }

    /**
     * Règles de validation communes (le site doit être celui de l'utilisateur).
     */
    private function validateCaisse(Request $request, ?int $caisseid = null): void
    {
        $request->validate([
            'libelle' => 'required|string|max:100',
            'compteur' => 'required|integer|min:0',
            'numero' => 'required|integer|min:0',
            'siteid' => 'required|integer|in:' . $this->userSiteId(),
            'agencebid' => 'nullable|integer|exists:agencebs,agencebid',
            'clientid' => 'nullable|integer|exists:clients,clientid',
            // Un poste (station) n'est lié qu'à une seule caisse : le bureau identifie la caisse par le poste
            'machineid' => ['nullable', 'integer', 'exists:stations,stationid',
                \Illuminate\Validation\Rule::unique('caisses', 'machineid')->ignore($caisseid, 'caisseid')],
        ], [
            'siteid.in' => 'Le site doit être celui de l\'utilisateur connecté.',
            'machineid.unique' => 'Ce poste est déjà lié à une autre caisse : libérez-la d\'abord (Libération de caisse).',
            'machineid.exists' => 'Poste introuvable.',
            'agencebid.exists' => 'Agence introuvable.',
            'clientid.exists' => 'Client introuvable.',
        ]);
    }

    public function store(Request $request)
    {
        $this->validateCaisse($request);

        $agencebid = $request->agencebid ?? auth()->user()->agencebid ?? DB::table('agencebs')->value('agencebid') ?? 1;

        DB::transaction(function () use ($request, $agencebid) {
            // Identifiant sûr pour le logiciel de bureau (max()+1 et séquence, séquence réalignée)
            $nextId = \App\Support\Identifiants::prochain('caisses', 'caisseid');

            DB::table('caisses')->insert([
                'caisseid' => $nextId,
                'libelle' => $request->libelle,
                'compteur' => $request->compteur,
                'numero' => $request->numero,
                'siteid' => $request->siteid,
                'agencebid' => $agencebid,
                'clientid' => $request->clientid,
                'machineid' => $request->machineid,
                'bloque' => $request->has('bloque'),
                'facturation' => true
            ]);
        });

        return redirect()->route('parametre.caisse.index')->with('success', 'Caisse ajoutée avec succès.');
    }

    public function update(Request $request, $id)
    {
        $id = $this->routeId($id);
        $this->validateCaisse($request, $id);

        $agencebid = $request->agencebid ?? auth()->user()->agencebid ?? DB::table('agencebs')->value('agencebid') ?? 1;

        $updated = DB::table('caisses')
            ->where('caisseid', $id)
            ->where('siteid', $this->userSiteId())
            ->update([
                'libelle' => $request->libelle,
                'compteur' => $request->compteur,
                'numero' => $request->numero,
                'siteid' => $request->siteid,
                'agencebid' => $agencebid,
                'clientid' => $request->clientid,
                'machineid' => $request->machineid,
                'bloque' => $request->has('bloque'),
                'facturation' => true
            ]);

        if (!$updated) {
            return redirect()->route('parametre.caisse.index')->with('error', 'Caisse introuvable.');
        }

        return redirect()->route('parametre.caisse.index')->with('success', 'Caisse modifiée avec succès.');
    }

    public function destroy($id)
    {
        $id = $this->routeId($id);

        try {
            $deleted = DB::transaction(function () use ($id) {
                return DB::table('caisses')
                    ->where('caisseid', $id)
                    ->where('siteid', $this->userSiteId())
                    ->delete();
            });
        } catch (QueryException $e) {
            return redirect()->route('parametre.caisse.index')->with('error', 'Impossible de supprimer cette caisse : elle est utilisée (stations, tickets, journaux de caisse ou dépenses).');
        }

        if (!$deleted) {
            return redirect()->route('parametre.caisse.index')->with('error', 'Caisse introuvable.');
        }

        return redirect()->route('parametre.caisse.index')->with('success', 'Caisse supprimée avec succès.');
    }

    public function liberation()
    {
        $siteid = $this->userSiteId(); // Site de l'utilisateur connecté

        $caisses = DB::table('caisses as c')
            ->leftJoin('sites as s', 's.siteid', '=', 'c.siteid')
            ->select('c.caisseid', 'c.libelle', 's.libelle as site_libelle', 'c.machineid')
            ->whereNotNull('c.machineid')
            ->where('c.siteid', $siteid)
            ->orderBy('c.caisseid')
            ->get();

        return view('parametre.caisse.liberation', compact('caisses'));
    }

    public function liberer($id)
    {
        $id = $this->routeId($id);

        $updated = DB::table('caisses')
            ->where('caisseid', $id)
            ->where('siteid', $this->userSiteId())
            ->update(['machineid' => null]);

        if (!$updated) {
            return redirect()->route('parametre.caisse.liberation')->with('error', 'Caisse introuvable.');
        }

        return redirect()->route('parametre.caisse.liberation')->with('success', 'La caisse a été libérée avec succès.');
    }
}
