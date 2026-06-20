<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MouvementsArticlesController extends Controller
{
    public function index(Request $request)
    {
        $siteid = auth()->user()->siteid ?? 102; // Sécurité si non connecté

        // Valeurs par défaut pour les dates (Aujourd'hui)
        $dateDu = $request->input('date_du', Carbon::today()->format('Y-m-d'));
        $dateAu = $request->input('date_au', Carbon::today()->format('Y-m-d'));

        // Données pour le filtre
        $familles = DB::table('familles')->orderBy('famillelibelle')->get();
        $sousFamilles = DB::table('sousfamilles')->orderBy('sousfamillelibelle')->get();
        $rayons = DB::table('categories')->orderBy('categorylibelle')->get();
        $saisons = DB::table('categories4')->orderBy('category4libelle')->get();

        $query = DB::table('vdetmvtstcs')
            ->join('produits', 'vdetmvtstcs.produitid', '=', 'produits.produitid')
            ->leftJoin('produit2s', 'vdetmvtstcs.produit2id', '=', 'produit2s.produit2id')
            ->leftJoin('couleurs', 'produit2s.couleurid', '=', 'couleurs.couleurid')
            ->leftJoin('tailles', 'produit2s.tailleid', '=', 'tailles.tailleid')
            ->leftJoin('sites', 'vdetmvtstcs.siteid', '=', 'sites.siteid')
            ->select(
                'produits.reference',
                'produits.produitcode',
                'couleurs.couleurlibelle',
                'tailles.taillelibelle',
                'vdetmvtstcs.qteachat',
                'vdetmvtstcs.qtevente',
                'vdetmvtstcs.qtetransfert',
                'vdetmvtstcs.qteinout',
                'vdetmvtstcs.qteecart',
                'vdetmvtstcs.docid',
                'vdetmvtstcs.doclibelle',
                'vdetmvtstcs.dateoperation',
                'sites.libelle as sitelibelle'
            )
            ->where('vdetmvtstcs.siteid', $siteid)
            ->whereDate('vdetmvtstcs.dateoperation', '>=', $dateDu)
            ->whereDate('vdetmvtstcs.dateoperation', '<=', $dateAu);

        // Application des filtres additionnels (modale)
        if ($request->filled('familleid')) {
            $query->where('produits.familleid', $request->familleid);
        }
        if ($request->filled('sousfamilleid')) {
            $query->where('produits.sousfamilleid', $request->sousfamilleid);
        }
        if ($request->filled('rayonid')) {
            $query->where('produits.categoryid', $request->rayonid);
        }
        if ($request->filled('saisonid')) {
            $query->where('produits.category4id', $request->saisonid);
        }

        $mouvements = $query->orderBy('vdetmvtstcs.dateoperation', 'desc')->paginate(50);

        return view('stock.mouvements.index', compact('mouvements', 'dateDu', 'dateAu', 'familles', 'sousFamilles', 'rayons', 'saisons'));
    }
}
