<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        
        // 1. Chiffre d'affaires total (Aujourd'hui)
        $caToday = DB::table('ctickets')->whereDate('cticketdate', $today)->sum('totalttc') ?? 0;
        $caYesterday = DB::table('ctickets')->whereDate('cticketdate', $yesterday)->sum('totalttc') ?? 0;
        $caTrend = $caYesterday > 0 ? (($caToday - $caYesterday) / $caYesterday) * 100 : 0;

        // 2. Tickets (Aujourd'hui)
        $ticketsToday = DB::table('ctickets')->whereDate('cticketdate', $today)->count();
        $ticketsYesterday = DB::table('ctickets')->whereDate('cticketdate', $yesterday)->count();
        $ticketsTrend = $ticketsYesterday > 0 ? (($ticketsToday - $ticketsYesterday) / $ticketsYesterday) * 100 : 0;

        // 3. Panier Moyen (Aujourd'hui)
        $panierMoyenToday = $ticketsToday > 0 ? $caToday / $ticketsToday : 0;
        $panierMoyenYesterday = $ticketsYesterday > 0 ? $caYesterday / $ticketsYesterday : 0;
        $panierMoyenTrend = $panierMoyenYesterday > 0 ? (($panierMoyenToday - $panierMoyenYesterday) / $panierMoyenYesterday) * 100 : 0;

        // 4. Articles Vendus (Aujourd'hui)
        $articlesToday = DB::table('ctickets')->whereDate('cticketdate', $today)->sum('totalqte') ?? 0;
        $articlesYesterday = DB::table('ctickets')->whereDate('cticketdate', $yesterday)->sum('totalqte') ?? 0;
        $articlesTrend = $articlesYesterday > 0 ? (($articlesToday - $articlesYesterday) / $articlesYesterday) * 100 : 0;

        // Chart Data (Last 7 days)
        $chartData = [];
        $chartLabels = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chartLabels[] = ucfirst($date->translatedFormat('D'));
            $sales = DB::table('ctickets')->whereDate('cticketdate', $date)->sum('totalttc') ?? 0;
            $chartData[] = round($sales, 3);
        }

        // Recent Activity
        $recentTickets = DB::table('ctickets')
            ->whereNotNull('cticketdate')
            ->orderBy('cticketdate', 'desc')
            ->take(5)
            ->get();

        // Top Products (Mois en cours)
        $topProducts = DB::table('detctickets')
            ->join('ctickets', 'detctickets.cticketid', '=', 'ctickets.cticketid')
            ->join('produits', 'detctickets.produitid', '=', 'produits.produitid')
            ->whereMonth('ctickets.cticketdate', $today->month)
            ->whereYear('ctickets.cticketdate', $today->year)
            ->select('produits.produitlibelle', DB::raw('SUM(detctickets.qte) as total_vendus'), DB::raw('SUM(detctickets.totalttc) as total_ca'))
            ->groupBy('produits.produitlibelle')
            ->orderBy('total_vendus', 'desc')
            ->take(5)
            ->get();

        // Flops (Articles les moins vendus)
        $flops = DB::table('detctickets')
            ->join('ctickets', 'detctickets.cticketid', '=', 'ctickets.cticketid')
            ->join('produits', 'detctickets.produitid', '=', 'produits.produitid')
            ->whereMonth('ctickets.cticketdate', $today->month)
            ->whereYear('ctickets.cticketdate', $today->year)
            ->select('produits.produitlibelle', DB::raw('SUM(detctickets.qte) as total_vendus'))
            ->groupBy('produits.produitlibelle')
            ->orderBy('total_vendus', 'asc')
            ->take(5)
            ->get();

        // Répartition par Catégorie/Famille
        $categorySales = DB::table('detctickets')
            ->join('ctickets', 'detctickets.cticketid', '=', 'ctickets.cticketid')
            ->join('produits', 'detctickets.produitid', '=', 'produits.produitid')
            ->leftJoin('familles', 'produits.familleid', '=', 'familles.familleid')
            ->whereMonth('ctickets.cticketdate', $today->month)
            ->whereYear('ctickets.cticketdate', $today->year)
            ->select(DB::raw('COALESCE(familles.famillelibelle, \'Non Catégorisé\') as famille'), DB::raw('SUM(detctickets.totalttc) as total_ca'))
            ->groupBy('familles.famillelibelle')
            ->orderBy('total_ca', 'desc')
            ->take(5)
            ->get();

        // Heures de pointe
        $peakHours = DB::table('ctickets')
            ->select(DB::raw('EXTRACT(HOUR FROM cticketdate) as hour'), DB::raw('SUM(totalttc) as total_ca'))
            ->whereMonth('cticketdate', $today->month)
            ->whereYear('cticketdate', $today->year)
            ->groupBy(DB::raw('EXTRACT(HOUR FROM cticketdate)'))
            ->orderBy('hour')
            ->get();

        // Top Vendeurs
        $topVendeurs = DB::table('ctickets')
            ->leftJoin('employees', 'ctickets.employeeid', '=', 'employees.employeeid')
            ->whereMonth('ctickets.cticketdate', $today->month)
            ->whereYear('ctickets.cticketdate', $today->year)
            ->select(DB::raw('COALESCE(employees.nom, \'Admin/Inconnu\') as nom'), DB::raw('SUM(ctickets.totalttc) as total_ca'), DB::raw('COUNT(ctickets.cticketid) as nb_tickets'))
            ->groupBy('employees.nom')
            ->orderBy('total_ca', 'desc')
            ->take(5)
            ->get();

        // Top Clients (Exclure PASSAGER)
        $topClients = DB::table('ctickets')
            ->leftJoin('clients', 'ctickets.clientid', '=', 'clients.clientid')
            ->whereMonth('ctickets.cticketdate', $today->month)
            ->whereYear('ctickets.cticketdate', $today->year)
            ->whereNotNull('clients.nom')
            ->where('clients.nom', '!=', 'PASSAGER')
            ->select('clients.nom', 'clients.prenom', DB::raw('SUM(ctickets.totalttc) as total_ca'))
            ->groupBy('clients.nom', 'clients.prenom')
            ->orderBy('total_ca', 'desc')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'caToday', 'caTrend',
            'ticketsToday', 'ticketsTrend',
            'panierMoyenToday', 'panierMoyenTrend',
            'articlesToday', 'articlesTrend',
            'chartLabels', 'chartData',
            'recentTickets', 'topProducts',
            'flops', 'categorySales', 'peakHours', 'topVendeurs', 'topClients'
        ));
    }
}
