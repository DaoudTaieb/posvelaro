<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        // Chargement des listes de filtres
        $familles = DB::table('familles')->orderBy('famillelibelle')->get();
        $sousFamilles = DB::table('sousfamilles')->orderBy('sousfamillelibelle')->get();
        $rayons = DB::table('categories')->orderBy('categorylibelle')->get();
        $saisons = DB::table('categories4')->orderBy('category4libelle')->get();

        // Récupération des articles avec les jointures (basé sur produit2s)
        $query = DB::table('produit2s')
            ->join('produits', 'produit2s.produitid', '=', 'produits.produitid')
            ->leftJoin('familles', 'produits.familleid', '=', 'familles.familleid')
            ->leftJoin('sousfamilles', 'produits.sousfamilleid', '=', 'sousfamilles.sousfamilleid')
            ->leftJoin('categories', 'produits.categoryid', '=', 'categories.categoryid') // Rayon
            ->leftJoin('categories2', 'produits.category2id', '=', 'categories2.category2id') // Marque
            ->leftJoin('categories4', 'produits.category4id', '=', 'categories4.category4id') // Saison
            ->select(
                'produit2s.produit2id',
                'produit2s.barcode2 as variant_barcode',
                'produit2s.produit2code',
                'produits.produitcode',
                'produits.reference',
                'produits.produitlibelle',
                'produits.ttc_vente',
                'produits.isfidelite',
                'familles.famillelibelle',
                'sousfamilles.sousfamillelibelle',
                'categories.categorylibelle as rayon_nom',
                'categories2.category2libelle as marque_nom',
                'categories4.category4libelle as saison_nom'
            );

        // Application des filtres "en-tête"
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

        // On ordonne par id de produit, puis par variante
        $articles = $query->orderBy('produits.produitcode')->orderBy('produit2s.produit2id')->paginate(20);

        return view('stock.articles.index', compact('articles', 'familles', 'sousFamilles', 'rayons', 'saisons'));
    }
}
