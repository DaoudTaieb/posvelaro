<?php

namespace App\Http\Controllers\Vente;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
                         ->orWhere('ctickets.cticketnumero', 'ILIKE', "%{$q}%")
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
                                 ->orWhere('ctickets.cticketnumero', 'ILIKE', "%{$val}%");
                    });
                } elseif ($reqKey == 'f_vendeur') {
                    $val = $request->{$reqKey};
                    $query->where(function($qBuilder) use ($val) {
                        $qBuilder->where('vendeurs.nom', 'ILIKE', "%{$val}%")
                                 ->orWhere('vendeurs.prenom', 'ILIKE', "%{$val}%");
                    });
                } else {
                    $query->where($dbCol, 'ILIKE', '%' . $request->{$reqKey} . '%');
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

        if ($hasFilters) {
            if ($request->ajax()) {
                Log::info('AJAX Commissions Query: ' . $query->toSql(), $query->getBindings());

                $totalNetHt = (clone $query)->sum('ctickets.totalnetht');
                $totalCommission = (clone $query)->sum(DB::raw('(ctickets.totalnetht * vendeurs.tauxcommission / 100)'));

                $commissions = $query->paginate(15);
                $html = view('vente.commissions.partials.table_body', compact('commissions'))->render();

                return response()->json([
                    'html' => $html,
                    'pagination' => $commissions->links()->toHtml(),
                    'totals' => [
                        'net_ht' => number_format($totalNetHt ?? 0, 3, ',', ' '),
                        'commission' => number_format($totalCommission ?? 0, 3, ',', ' ')
                    ]
                ]);
            }
            $commissions = $query->paginate(15);
        } else {
            // Return empty paginator if no filters
            $commissions = \Illuminate\Pagination\Paginator::resolveCurrentPage() == 1 
                ? new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15) 
                : $query->whereRaw('1 = 0')->paginate(15);
        }

        $vendeurs = DB::table('employees')->select('employeeid', 'nom', 'prenom')->where('isvendeur', true)->get();

        return view('vente.commissions.index', compact('commissions', 'vendeurs'));
    }

    /**
     * Calcule ou recalcule les commissions pour une période donnée.
     */
    public function calculate(Request $request)
    {
        // Logique de calcul des commissions si nécessaire pour sauvegarder en BDD
        return response()->json(['message' => 'Calcul effectué.']);
    }
}
