<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockDetailleController extends Controller
{
    public function index(Request $request)
    {
        // Données pour le filtre
        $familles = DB::table('familles')->orderBy('famillelibelle')->get();
        $sousFamilles = DB::table('sousfamilles')->orderBy('sousfamillelibelle')->get();
        $rayons = DB::table('categories')->orderBy('categorylibelle')->get();
        $saisons = DB::table('categories4')->orderBy('category4libelle')->get();

        $siteid = auth()->user()->siteid ?? 102; // Sécurité si non connecté

        // Requête principale
        $query = DB::table('produits')
            ->leftJoin('vdetmvtstcs', function($join) use ($siteid) {
                $join->on('produits.produitid', '=', 'vdetmvtstcs.produitid')
                     ->where('vdetmvtstcs.siteid', '=', $siteid);
            })
            ->leftJoin('vstockcts', function($join) use ($siteid) {
                $join->on('produits.produitid', '=', 'vstockcts.produitid')
                     ->where('vstockcts.siteid', '=', $siteid);
            })
            ->select(
                'produits.produitid',
                'produits.reference',
                'produits.produitcode',
                'produits.produitlibelle',
                DB::raw('COALESCE(SUM(vdetmvtstcs.qteachat), 0) as total_achat'),
                DB::raw('COALESCE(SUM(vdetmvtstcs.qtetransfert), 0) as total_transfert'),
                DB::raw('COALESCE(SUM(vdetmvtstcs.qtevente), 0) as total_vente'),
                DB::raw('COALESCE(SUM(vdetmvtstcs.qteinout), 0) as total_es'),
                DB::raw('COALESCE(SUM(vdetmvtstcs.qteecart), 0) as total_ecart'),
                DB::raw('MAX(COALESCE(vstockcts.qtestock, 0)) as total_stock')
            )
            ->groupBy(
                'produits.produitid',
                'produits.reference',
                'produits.produitcode',
                'produits.produitlibelle'
            );

        // Application des filtres
        $hasFilter = false;

        if ($request->filled('familleid')) {
            $query->where('produits.familleid', $request->familleid);
            $hasFilter = true;
        }
        if ($request->filled('sousfamilleid')) {
            $query->where('produits.sousfamilleid', $request->sousfamilleid);
            $hasFilter = true;
        }
        if ($request->filled('rayonid')) {
            $query->where('produits.categoryid', $request->rayonid);
            $hasFilter = true;
        }
        if ($request->filled('saisonid')) {
            $query->where('produits.category4id', $request->saisonid);
            $hasFilter = true;
        }

        // Si aucun filtre n'est appliqué, on force la requête à retourner vide
        if (!$hasFilter) {
            $query->whereRaw('1 = 0');
        }

        // Pagination
        $articles = $query->orderBy('produits.produitcode')->paginate(20);

        return view('stock.detaille.index', compact('articles', 'familles', 'sousFamilles', 'rayons', 'saisons'));
    }
}
