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

        // Sous-requêtes d'agrégation pour éviter le produit cartésien
        $mvtSub = DB::table('vdetmvtstcs')
            ->select(
                'produitid',
                DB::raw('COALESCE(SUM(qteachat), 0) as total_achat'),
                DB::raw('COALESCE(SUM(qtetransfert), 0) as total_transfert'),
                DB::raw('COALESCE(SUM(qtevente), 0) as total_vente'),
                DB::raw('COALESCE(SUM(qteinout), 0) as total_es'),
                DB::raw('COALESCE(SUM(qteecart), 0) as total_ecart')
            )
            ->where('siteid', $siteid)
            ->groupBy('produitid');

        $stockSub = DB::table('vstockcts')
            ->select(
                'produitid',
                DB::raw('COALESCE(SUM(qtestock), 0) as total_stock')
            )
            ->where('siteid', $siteid)
            ->groupBy('produitid');

        // Requête principale
        $query = DB::table('produits')
            ->leftJoinSub($mvtSub, 'mvt', 'produits.produitid', '=', 'mvt.produitid')
            ->leftJoinSub($stockSub, 'stk', 'produits.produitid', '=', 'stk.produitid')
            ->select(
                'produits.produitid',
                'produits.reference',
                'produits.produitcode',
                'produits.produitlibelle',
                DB::raw('COALESCE(mvt.total_achat, 0) as total_achat'),
                DB::raw('COALESCE(mvt.total_transfert, 0) as total_transfert'),
                DB::raw('COALESCE(mvt.total_vente, 0) as total_vente'),
                DB::raw('COALESCE(mvt.total_es, 0) as total_es'),
                DB::raw('COALESCE(mvt.total_ecart, 0) as total_ecart'),
                DB::raw('COALESCE(stk.total_stock, 0) as total_stock')
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
        if ($request->filled('ref_search')) {
            $query->where('produits.reference', 'like', '%' . $request->ref_search . '%');
            $hasFilter = true;
        }
        if ($request->filled('code_search')) {
            $query->where('produits.produitcode', 'like', '%' . $request->code_search . '%');
            $hasFilter = true;
        }
        if ($request->filled('libelle_search')) {
            $query->where('produits.produitlibelle', 'like', '%' . $request->libelle_search . '%');
            $hasFilter = true;
        }

        // Filtres sur les colonnes numériques calculées
        if ($request->filled('achat_search')) {
            $query->whereRaw('CAST(COALESCE(mvt.total_achat, 0) as text) like ?', ['%' . $request->achat_search . '%']);
            $hasFilter = true;
        }
        if ($request->filled('transfert_search')) {
            $query->whereRaw('CAST(COALESCE(mvt.total_transfert, 0) as text) like ?', ['%' . $request->transfert_search . '%']);
            $hasFilter = true;
        }
        if ($request->filled('vente_search')) {
            $query->whereRaw('CAST(COALESCE(mvt.total_vente, 0) as text) like ?', ['%' . $request->vente_search . '%']);
            $hasFilter = true;
        }
        if ($request->filled('es_search')) {
            $query->whereRaw('CAST(COALESCE(mvt.total_es, 0) as text) like ?', ['%' . $request->es_search . '%']);
            $hasFilter = true;
        }
        if ($request->filled('stock_search')) {
            $query->whereRaw('CAST(COALESCE(stk.total_stock, 0) as text) like ?', ['%' . $request->stock_search . '%']);
            $hasFilter = true;
        }
        if ($request->filled('ecart_search')) {
            $query->whereRaw('CAST(COALESCE(mvt.total_ecart, 0) as text) like ?', ['%' . $request->ecart_search . '%']);
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
