<?php

namespace App\Http\Controllers\Vente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommissionController extends Controller
{
    /**
     * Affiche l'interface de calcul et consultation des commissions.
     */
    public function index(Request $request)
    {
        $query = DB::table('ctickets')
            ->join('employees as vendeurs', 'ctickets.employeeid', '=', 'vendeurs.employeeid')
            ->where('vendeurs.isvendeur', true)
            ->where('ctickets.siteid', \App\Support\SiteCourant::id())
            ->where('ctickets.enattente', false)
            ->select(
                'ctickets.cticketdate',
                'ctickets.numerointerne',
                'ctickets.cticketnumero',
                'ctickets.totalnetht',
                'vendeurs.nom as vendeur_nom',
                'vendeurs.prenom as vendeur_prenom',
                'vendeurs.tauxcommission'
            )
            ->addSelect(DB::raw('(ctickets.totalnetht * vendeurs.tauxcommission / 100) as montant_commission'))
            ->orderBy('ctickets.cticketdate', 'desc');

        // Apply global search
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($qBuilder) use ($q) {
                $qBuilder->where('ctickets.numerointerne', 'ILIKE', "%{$q}%")
                         ->orWhereRaw('CAST(ctickets.cticketnumero AS TEXT) ILIKE ?', ["%{$q}%"])
                         ->orWhere('vendeurs.nom', 'ILIKE', "%{$q}%")
                         ->orWhere('vendeurs.prenom', 'ILIKE', "%{$q}%");
            });
        }

        // Apply filters
        $hasFilters = $request->filled('q') || $request->filled('date_du') || $request->filled('date_au') || $request->filled('vendeur');

        // Column filters map
        $colMap = [
            'f_date' => 'ctickets.cticketdate',
            'f_numero' => 'ctickets.numerointerne', // fallback to cticketnumero handled below
            'f_vendeur' => 'vendeurs.nom',
            'f_net_ht' => 'ctickets.totalnetht',
            'f_taux' => 'vendeurs.tauxcommission',
            'f_montant' => 'montant_commission', // Handled custom
        ];

        foreach ($colMap as $reqKey => $dbCol) {
            if ($request->filled($reqKey)) {
                $hasFilters = true;
                if ($reqKey == 'f_date') {
                    $query->whereDate($dbCol, $request->{$reqKey});
                } elseif ($reqKey == 'f_numero') {
                    $val = $request->{$reqKey};
                    $query->where(function($qBuilder) use ($val) {
                        $qBuilder->where('ctickets.numerointerne', 'ILIKE', "%{$val}%")
                                 ->orWhereRaw('CAST(ctickets.cticketnumero AS TEXT) ILIKE ?', ["%{$val}%"]);
                    });
                } elseif ($reqKey == 'f_vendeur') {
                    $val = $request->{$reqKey};
                    $query->where(function($qBuilder) use ($val) {
                        $qBuilder->where('vendeurs.nom', 'ILIKE', "%{$val}%")
                                 ->orWhere('vendeurs.prenom', 'ILIKE', "%{$val}%");
                    });
                } elseif ($reqKey == 'f_montant') {
                    $val = $request->{$reqKey};
                    $query->whereRaw('CAST((ctickets.totalnetht * vendeurs.tauxcommission / 100) AS TEXT) ILIKE ?', ['%'.$val.'%']);
                } else {
                    $query->whereRaw('CAST(' . $dbCol . ' AS TEXT) ILIKE ?', ['%' . $request->{$reqKey} . '%']);
                }
            }
        }

        if ($request->filled('date_du')) {
            $query->whereDate('ctickets.cticketdate', '>=', $request->date_du);
        }
        if ($request->filled('date_au')) {
            $query->whereDate('ctickets.cticketdate', '<=', $request->date_au);
        }
        if ($request->filled('vendeur')) {
            $query->where('ctickets.employeeid', $request->vendeur);
        }

        if ($request->ajax()) {

            // Totaux en une seule requête d'agrégat
            $t = (clone $query)->reorder()->select(DB::raw('COALESCE(SUM(ctickets.totalnetht), 0) as netht'), DB::raw('COALESCE(SUM(ctickets.totalnetht * vendeurs.tauxcommission / 100), 0) as commission'), DB::raw('COUNT(*) as nb'))->first();
            [$totalNetHt, $totalCommission, $nbVentes] = [(float) $t->netht, (float) $t->commission, (int) $t->nb];

            $commissions = $query->paginerVite(15);
            $html = view('vente.commissions.partials.table_body', compact('commissions'))->render();

            return response()->json([
                'html' => $html,
                'pagination' => $commissions->links()->toHtml(),
                'totals' => [
                    'net_ht' => number_format((float)($totalNetHt ?? 0), 3, ',', ' '),
                    'commission' => number_format((float)($totalCommission ?? 0), 3, ',', ' ')
                ],
                'kpis' => [
                    'nb_ventes' => number_format((float)($nbVentes ?? 0), 0, ',', ' '),
                    'net_ht' => number_format((float)($totalNetHt ?? 0), 3, ',', ' '),
                    'commission' => number_format((float)($totalCommission ?? 0), 3, ',', ' ')
                ]
            ]);
        }

        // Initial Load (Non-AJAX) -> Just load commissions, no empty paginator logic.
        $commissions = $query->paginerVite(15);
        
        // Calculate initial totals to pass to the view to avoid layout flickers
        // Totaux en une seule requête d'agrégat
        // Totaux et liste des vendeurs (filtre) dans la même requête
        ['totaux' => $t, 'vendeurs' => $vendeurs] = \App\Support\LectureGroupee::listes([
            'totaux' => (clone $query)->reorder()->select(DB::raw('COALESCE(SUM(ctickets.totalnetht), 0) as netht'), DB::raw('COALESCE(SUM(ctickets.totalnetht * vendeurs.tauxcommission / 100), 0) as commission'), DB::raw('COUNT(*) as nb')),
            'vendeurs' => DB::table('employees')->select('employeeid', 'nom', 'prenom')->where('isvendeur', true),
        ]);
        $t = $t->first();
        [$totalNetHt, $totalCommission, $nbVentes] = [(float) $t->netht, (float) $t->commission, (int) $t->nb];

        $totals = [
            'net_ht' => number_format((float)($totalNetHt ?? 0), 3, ',', ' '),
            'commission' => number_format((float)($totalCommission ?? 0), 3, ',', ' ')
        ];

        $kpis = [
            'nb_ventes' => number_format((float)($nbVentes ?? 0), 0, ',', ' '),
            'net_ht' => number_format((float)($totalNetHt ?? 0), 3, ',', ' '),
            'commission' => number_format((float)($totalCommission ?? 0), 3, ',', ' ')
        ];


        return view('vente.commissions.index', compact('commissions', 'vendeurs', 'totals', 'kpis'));
    }
}
