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
        $siteid = \App\Support\SiteCourant::id();

        // Valeurs par défaut pour les dates (Aujourd'hui)
        $dateDu = $request->input('date_du') ?: Carbon::today()->format('Y-m-d');
        $dateAu = $request->input('date_au') ?: Carbon::today()->format('Y-m-d');

        // Données pour le filtre
        // Listes de référence lues en une seule requête (base distante : un aller-retour au lieu de 4)
        ['familles' => $familles, 'sousFamilles' => $sousFamilles, 'rayons' => $rayons, 'saisons' => $saisons] = \App\Support\LectureGroupee::listes([
            'familles' => DB::table('familles')->select('familleid', 'famillelibelle')->orderBy('famillelibelle'),
            'sousFamilles' => DB::table('sousfamilles')->select('sousfamilleid', 'sousfamillelibelle', 'familleid', 'is_loyalty_enabled')->orderBy('sousfamillelibelle'),
            'rayons' => DB::table('categories')->orderBy('categorylibelle'),
            'saisons' => DB::table('categories4')->orderBy('category4libelle'),
        ]);

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

        // Mapping des colonnes textuelles
        $colFilters = [
            'f_code' => 'produits.produitcode',
            'code_search' => 'produits.produitcode',
            'f_couleur' => 'couleurs.couleurlibelle',
            'couleur_search' => 'couleurs.couleurlibelle',
            'f_taille' => 'tailles.taillelibelle',
            'taille_search' => 'tailles.taillelibelle',
            'f_piece' => 'vdetmvtstcs.docid',
            'piece_search' => 'vdetmvtstcs.docid',
            'f_intitule' => 'vdetmvtstcs.doclibelle',
            'intitule_search' => 'vdetmvtstcs.doclibelle',
            'f_site' => 'sites.libelle',
            'site_search' => 'sites.libelle',
        ];

        foreach ($colFilters as $param => $dbCol) {
            if ($request->filled($param)) {
                $query->where($dbCol, 'ilike', '%' . $request->input($param) . '%');
            }
        }

        // Filtres numériques et de calcul
        if ($request->filled('f_achat') || $request->filled('achat_search')) {
            $val = $request->input('f_achat') ?? $request->input('achat_search');
            $query->whereRaw('CAST(vdetmvtstcs.qteachat AS text) LIKE ?', ['%' . $val . '%']);
        }
        if ($request->filled('f_vente') || $request->filled('vente_search')) {
            $val = $request->input('f_vente') ?? $request->input('vente_search');
            $query->whereRaw('CAST(vdetmvtstcs.qtevente AS text) LIKE ?', ['%' . $val . '%']);
        }
        if ($request->filled('f_entrer') || $request->filled('entrer_search')) {
            $val = $request->input('f_entrer') ?? $request->input('entrer_search');
            $query->whereRaw('(vdetmvtstcs.qtetransfert + vdetmvtstcs.qteinout + vdetmvtstcs.qteecart) > 0')
                  ->whereRaw('CAST((vdetmvtstcs.qtetransfert + vdetmvtstcs.qteinout + vdetmvtstcs.qteecart) AS text) LIKE ?', ['%' . $val . '%']);
        }
        if ($request->filled('f_sortie') || $request->filled('sortie_search')) {
            $val = $request->input('f_sortie') ?? $request->input('sortie_search');
            $query->whereRaw('(vdetmvtstcs.qtetransfert + vdetmvtstcs.qteinout + vdetmvtstcs.qteecart) < 0')
                  ->whereRaw('CAST(ABS(vdetmvtstcs.qtetransfert + vdetmvtstcs.qteinout + vdetmvtstcs.qteecart) AS text) LIKE ?', ['%' . $val . '%']);
        }
        if ($request->filled('f_date')) {
            $query->whereRaw("to_char(vdetmvtstcs.dateoperation, 'DD/MM/YYYY') LIKE ?", ['%' . $request->input('f_date') . '%']);
        }
        if ($request->filled('f_heure')) {
            $query->whereRaw("to_char(vdetmvtstcs.dateoperation, 'HH24:MI') LIKE ?", ['%' . $request->input('f_heure') . '%']);
        }

        // Agrégats de toute la sélection, en une seule requête (au lieu de 3 dont une lisant toutes les lignes)
        $ajustement = 'COALESCE(vdetmvtstcs.qtetransfert, 0) + COALESCE(vdetmvtstcs.qteinout, 0) + COALESCE(vdetmvtstcs.qteecart, 0)';
        $t = (clone $query)->reorder()->select(
            DB::raw('COALESCE(SUM(vdetmvtstcs.qteachat), 0) as achat'),
            DB::raw('COALESCE(SUM(vdetmvtstcs.qtevente), 0) as vente'),
            DB::raw("COALESCE(SUM(CASE WHEN ($ajustement) > 0 THEN ($ajustement) ELSE 0 END), 0) as entrer"),
            DB::raw("COALESCE(SUM(CASE WHEN ($ajustement) < 0 THEN -($ajustement) ELSE 0 END), 0) as sortie")
        )->first();
        [$sumAchat, $sumVente, $sumEntrer, $sumSortie] = [(float) $t->achat, (float) $t->vente, (float) $t->entrer, (float) $t->sortie];

        $mouvements = $query->orderBy('vdetmvtstcs.dateoperation', 'desc')->paginerVite(50);

        if ($request->ajax()) {
            $html = view('stock.mouvements.partials.table_body', compact('mouvements'))->render();
            $pagination = $mouvements->appends($request->query())->links('pagination::bootstrap-4')->toHtml();

            return response()->json([
                'html' => $html,
                'pagination' => $pagination,
                'totals' => [
                    'mouvements_count' => $mouvements->total(),
                    'achat' => number_format($sumAchat, 0, ',', ' '),
                    'entrer' => number_format($sumEntrer, 0, ',', ' '),
                    'sortie' => number_format($sumSortie, 0, ',', ' '),
                    'vente' => number_format($sumVente, 0, ',', ' '),
                ],
                'kpis' => [
                    'total_mouvements' => number_format($mouvements->total(), 0, ',', ' '),
                    'total_achats' => number_format($sumAchat, 0, ',', ' '),
                    'total_ventes' => number_format($sumVente, 0, ',', ' '),
                    'ajustements_net' => number_format($sumEntrer - $sumSortie, 0, ',', ' '),
                ]
            ]);
        }

        return view('stock.mouvements.index', compact('mouvements', 'dateDu', 'dateAu', 'familles', 'sousFamilles', 'rayons', 'saisons', 'sumAchat', 'sumVente', 'sumEntrer', 'sumSortie'));
    }
}
