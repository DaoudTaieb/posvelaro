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

        // Global Search
        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function($q) use ($term) {
                $q->where('vproduit2stocks.produitcode', 'ilike', $term)
                  ->orWhere('vproduit2stocks.couleurlibelle', 'ilike', $term)
                  ->orWhere('vproduit2stocks.taillelibelle', 'ilike', $term);
            });
            $hasFilter = true;
        }

        // Text-based column filters
        $colFilters = [
            'f_code' => 'vproduit2stocks.produitcode',
            'f_couleur' => 'vproduit2stocks.couleurlibelle',
            'f_taille' => 'vproduit2stocks.taillelibelle',
        ];

        foreach ($colFilters as $param => $dbCol) {
            if ($request->filled($param)) {
                $query->where($dbCol, 'ilike', '%' . $request->input($param) . '%');
                $hasFilter = true;
            }
        }

        // Numeric cast filters
        $numFilters = [
            'f_entrer' => 'COALESCE(mouvements.total_entrer, 0)',
            'f_sortie' => 'COALESCE(mouvements.total_sortie, 0)',
            'f_achat' => 'COALESCE(mouvements.total_achat, 0)',
            'f_ret_achat' => 'COALESCE(mouvements.total_ret_achat, 0)',
            'f_vente' => 'COALESCE(mouvements.total_vente, 0)',
            'f_ret_vente' => 'COALESCE(mouvements.total_ret_vente, 0)',
            'f_dispo' => 'COALESCE(vproduit2stocks.qtestock, 0)',
            'f_pv_ttc' => 'COALESCE(vproduit2stocks.ttc_vente, 0)',
            'f_val_pv' => '(COALESCE(vproduit2stocks.qtestock, 0) * COALESCE(vproduit2stocks.ttc_vente, 0))',
        ];

        foreach ($numFilters as $param => $rawExpr) {
            if ($request->filled($param)) {
                $query->whereRaw("CAST({$rawExpr} AS text) LIKE ?", ['%' . $request->input($param) . '%']);
                $hasFilter = true;
            }
        }

        // Si aucun filtre n'est appliqué, on force la requête à retourner vide
        if (!$hasFilter) {
            $query->whereRaw('1 = 0');
        }

        // Calcul des agrégats pour la requête filtrée
        if ($hasFilter) {
            $totalsQuery = clone $query;
            $sumEntrer = $totalsQuery->sum('mouvements.total_entrer');
            $sumSortie = $totalsQuery->sum('mouvements.total_sortie');
            $sumAchat = $totalsQuery->sum('mouvements.total_achat');
            $sumRetAchat = $totalsQuery->sum('mouvements.total_ret_achat');
            $sumVente = $totalsQuery->sum('mouvements.total_vente');
            $sumRetVente = $totalsQuery->sum('mouvements.total_ret_vente');
            $sumDispo = $totalsQuery->sum('vproduit2stocks.qtestock');
            $sumPv = $totalsQuery->sum('vproduit2stocks.ttc_vente');
            $sumValPv = $totalsQuery->sum(DB::raw('COALESCE(vproduit2stocks.qtestock, 0) * COALESCE(vproduit2stocks.ttc_vente, 0)'));
            $articlesCount = $totalsQuery->count();
        } else {
            $sumEntrer = $sumSortie = $sumAchat = $sumRetAchat = $sumVente = $sumRetVente = $sumDispo = $sumPv = $sumValPv = $articlesCount = 0;
        }

        // Pagination
        $etatStocks = $query->orderBy('vproduit2stocks.produitcode', 'asc')->paginate(50);

        if ($request->ajax()) {
            $html = view('stock.etat.partials.table_body', compact('etatStocks'))->render();
            $pagination = $etatStocks->appends($request->query())->links('pagination::bootstrap-4')->toHtml();

            return response()->json([
                'html' => $html,
                'pagination' => $pagination,
                'totals' => [
                    'articles_count' => $articlesCount,
                    'entrer' => number_format($sumEntrer, 0, '', ' '),
                    'sortie' => number_format($sumSortie, 0, '', ' '),
                    'achat' => number_format($sumAchat, 0, '', ' '),
                    'ret_achat' => number_format($sumRetAchat, 0, '', ' '),
                    'vente' => number_format($sumVente, 0, '', ' '),
                    'ret_vente' => number_format($sumRetVente, 0, '', ' '),
                    'dispo' => number_format($sumDispo, 0, '', ' '),
                    'pv' => number_format($sumPv, 3, ',', ' '),
                    'val_pv' => number_format($sumValPv, 3, ',', ' '),
                ],
                'kpis' => [
                    'articles_count' => number_format($articlesCount, 0, '', ' '),
                    'dispo_total' => number_format($sumDispo, 0, '', ' '),
                    'vente_total' => number_format($sumVente, 0, '', ' '),
                    'val_pv_total' => number_format($sumValPv, 3, ',', ' '),
                ]
            ]);
        }

        return view('stock.etat.index', compact(
            'etatStocks', 'familles', 'sousFamilles', 'rayons', 'saisons',
            'sumEntrer', 'sumSortie', 'sumAchat', 'sumRetAchat', 'sumVente', 'sumRetVente', 'sumDispo', 'sumPv', 'sumValPv'
        ));
    }
}
