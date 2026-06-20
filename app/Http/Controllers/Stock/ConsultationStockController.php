<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConsultationStockController extends Controller
{
    public function index(Request $request)
    {
        // Récupération des listes pour les filtres
        $rayons = DB::table('categories')->orderBy('categorylibelle')->get();
        $typesStock = [
            'reel' => 'Réel',
            'virtuel' => 'Virtuel',
            'reserve' => 'Réservé'
        ];

        // Type de stock sélectionné par défaut
        $selectedType = $request->input('type_stock', 'reel');

        // Requête principale
        $query = DB::table('stock2s')
            ->join('produits', 'stock2s.produitid', '=', 'produits.produitid')
            ->join('produit2s', 'stock2s.produit2id', '=', 'produit2s.produit2id')
            ->leftJoin('categories', 'produits.categoryid', '=', 'categories.categoryid') // Rayon
            ->leftJoin('couleurs', 'produit2s.couleurid', '=', 'couleurs.couleurid')
            ->leftJoin('tailles', 'produit2s.tailleid', '=', 'tailles.tailleid')
            ->select(
                'produits.produitcode',
                'produits.reference',
                'produits.produitlibelle',
                'categories.categorylibelle as rayon_nom',
                'couleurs.couleurlibelle',
                'tailles.taillelibelle',
                'stock2s.qtestock',
                'stock2s.stockvirtuel',
                'stock2s.stockreserve'
            );

        // Filtres
        if ($request->filled('reference')) {
            $query->where('produits.reference', 'like', '%' . $request->reference . '%');
        }
        if ($request->filled('rayonid')) {
            $query->where('produits.categoryid', $request->rayonid);
        }
        if ($request->filled('couleur')) {
            $query->where('couleurs.couleurlibelle', 'like', '%' . $request->couleur . '%');
        }
        if ($request->filled('taille')) {
            $query->where('tailles.taillelibelle', 'like', '%' . $request->taille . '%');
        }

        // On ordonne par produit, puis variante
        $stocks = $query->orderBy('produits.produitcode')
                        ->orderBy('produit2s.produit2id')
                        ->paginate(20);

        return view('stock.consultation.index', compact('stocks', 'rayons', 'typesStock', 'selectedType'));
    }
}
