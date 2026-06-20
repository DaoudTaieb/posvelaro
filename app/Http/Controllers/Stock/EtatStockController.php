<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EtatStockController extends Controller
{
    public function index(Request $request)
    {
        $siteid = auth()->user()->siteid ?? 102; // Sécurité si non connecté

        // Données pour le filtre modale
        $familles = DB::table('familles')->orderBy('famillelibelle')->get();
        $sousFamilles = DB::table('sousfamilles')->orderBy('sousfamillelibelle')->get();
        $rayons = DB::table('categories')->orderBy('categorylibelle')->get();
        $saisons = DB::table('categories4')->orderBy('category4libelle')->get();

        // Sous-requête pour les agrégations de mouvements
        $mvtSubquery = DB::table('vdetmvtstcs')
            ->where('siteid', $siteid)
            ->select(
                'produit2id',
                DB::raw('SUM(CASE WHEN qteachat > 0 THEN qteachat ELSE 0 END) as total_achat'),
                DB::raw('SUM(CASE WHEN qteachat < 0 THEN ABS(qteachat) ELSE 0 END) as total_ret_achat'),
                DB::raw('SUM(CASE WHEN qtevente > 0 THEN qtevente ELSE 0 END) as total_vente'),
                DB::raw('SUM(CASE WHEN qtevente < 0 THEN ABS(qtevente) ELSE 0 END) as total_ret_vente'),
                DB::raw('SUM(CASE WHEN (qtetransfert + qteinout + qteecart) > 0 THEN (qtetransfert + qteinout + qteecart) ELSE 0 END) as total_entrer'),
                DB::raw('SUM(CASE WHEN (qtetransfert + qteinout + qteecart) < 0 THEN ABS(qtetransfert + qteinout + qteecart) ELSE 0 END) as total_sortie')
            )
            ->groupBy('produit2id');

        // Requête principale
        $query = DB::table('vproduit2stocks')
            ->leftJoinSub($mvtSubquery, 'mouvements', function($join) {
                $join->on('vproduit2stocks.produit2id', '=', 'mouvements.produit2id');
            })
            ->where('vproduit2stocks.siteid', $siteid)
            ->select(
                'vproduit2stocks.produit2id',
                'vproduit2stocks.produitcode',
                'vproduit2stocks.couleurlibelle',
                'vproduit2stocks.taillelibelle',
                'mouvements.total_entrer',
                'mouvements.total_sortie',
                'mouvements.total_achat',
                'mouvements.total_ret_achat',
                'mouvements.total_vente',
                'mouvements.total_ret_vente',
                'vproduit2stocks.qtestock as dispo',
                'vproduit2stocks.ttc_vente as pv_ttc',
                DB::raw('(COALESCE(vproduit2stocks.qtestock, 0) * COALESCE(vproduit2stocks.ttc_vente, 0)) as val_au_pv')
            );

        // Application des filtres modale
        $hasFilter = false;

        if ($request->filled('familleid')) {
            $query->where('vproduit2stocks.familleid', $request->familleid);
            $hasFilter = true;
        }
        if ($request->filled('sousfamilleid')) {
            $query->where('vproduit2stocks.sousfamilleid', $request->sousfamilleid);
            $hasFilter = true;
        }
        if ($request->filled('rayonid')) {
            $query->where('vproduit2stocks.categoryid', $request->rayonid);
            $hasFilter = true;
        }
        if ($request->filled('saisonid')) {
            $query->where('vproduit2stocks.category4id', $request->saisonid);
            $hasFilter = true;
        }

        // Si aucun filtre n'est appliqué, on force la requête à retourner vide
        if (!$hasFilter) {
            $query->whereRaw('1 = 0');
        }

        // Pagination
        $etatStocks = $query->orderBy('vproduit2stocks.produitcode', 'asc')->paginate(50);

        return view('stock.etat.index', compact('etatStocks', 'familles', 'sousFamilles', 'rayons', 'saisons'));
    }
}
