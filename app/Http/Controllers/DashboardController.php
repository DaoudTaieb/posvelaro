<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Rôle sans accès aux statistiques : on l'envoie directement à la caisse
        if (\App\Support\Droits::bloque('cons.stat')) {
            if (\App\Support\Droits::autorise('caisse')) {
                return redirect()->route('vente.caisse.pos');
            }
            abort(403, \App\Support\Droits::message('cons.stat'));
        }

        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $siteid = \App\Support\SiteCourant::id();
        
        // Indicateurs d'aujourd'hui et d'hier en une seule requête (CA, nombre de tickets, articles)
        $jours = DB::table('ctickets')
            ->where('siteid', $siteid)
            ->where('enattente', false)
            ->whereDate('cticketdate', '>=', $yesterday)
            ->whereDate('cticketdate', '<=', $today)
            ->groupBy(DB::raw('DATE(cticketdate)'))
            ->select(DB::raw('DATE(cticketdate) as jour'), DB::raw('SUM(totalttc) as ca'), DB::raw('COUNT(*) as nb'), DB::raw('SUM(totalqte) as qte'))
            ->get()
            ->keyBy(fn ($r) => (string) $r->jour);
        $jourAujourdhui = $jours[$today->toDateString()] ?? null;
        $jourHier = $jours[$yesterday->toDateString()] ?? null;

        // 1. Chiffre d'affaires total (Aujourd'hui)
        $caToday = (float) ($jourAujourdhui->ca ?? 0);
        $caYesterday = (float) ($jourHier->ca ?? 0);
        $caTrend = $caYesterday > 0 ? (($caToday - $caYesterday) / $caYesterday) * 100 : 0;

        // 2. Tickets (Aujourd'hui)
        $ticketsToday = (int) ($jourAujourdhui->nb ?? 0);
        $ticketsYesterday = (int) ($jourHier->nb ?? 0);
        $ticketsTrend = $ticketsYesterday > 0 ? (($ticketsToday - $ticketsYesterday) / $ticketsYesterday) * 100 : 0;

        // 3. Panier Moyen (Aujourd'hui)
        $panierMoyenToday = $ticketsToday > 0 ? $caToday / $ticketsToday : 0;
        $panierMoyenYesterday = $ticketsYesterday > 0 ? $caYesterday / $ticketsYesterday : 0;
        $panierMoyenTrend = $panierMoyenYesterday > 0 ? (($panierMoyenToday - $panierMoyenYesterday) / $panierMoyenYesterday) * 100 : 0;

        // 4. Articles Vendus (Aujourd'hui)
        $articlesToday = (float) ($jourAujourdhui->qte ?? 0);
        $articlesYesterday = (float) ($jourHier->qte ?? 0);
        $articlesTrend = $articlesYesterday > 0 ? (($articlesToday - $articlesYesterday) / $articlesYesterday) * 100 : 0;

        // Courbe des 7 derniers jours : une seule requête groupée par jour (au lieu d'une par jour)
        $ventesParJour = DB::table('ctickets')
            ->where('siteid', $siteid)
            ->where('enattente', false)
            ->whereDate('cticketdate', '>=', Carbon::today()->subDays(6))
            ->whereDate('cticketdate', '<=', $today)
            ->groupBy(DB::raw('DATE(cticketdate)'))
            ->select(DB::raw('DATE(cticketdate) as jour'), DB::raw('SUM(totalttc) as ca'))
            ->pluck('ca', 'jour');

        $chartData = [];
        $chartLabels = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chartLabels[] = ucfirst($date->translatedFormat('D'));
            $chartData[] = round((float) ($ventesParJour[$date->toDateString()] ?? 0), 3);
        }

        // Recent Activity
        $recentTickets = DB::table('ctickets')
            ->where('siteid', $siteid)
            ->where('enattente', false)
            ->whereNotNull('cticketdate')
            ->orderBy('cticketdate', 'desc')
            ->take(5)
            ->get();

        // Top Products (Mois en cours)
        $topProducts = DB::table('detctickets')
            ->join('ctickets', 'detctickets.cticketid', '=', 'ctickets.cticketid')
            ->join('produits', 'detctickets.produitid', '=', 'produits.produitid')
            ->where('ctickets.siteid', $siteid)
            ->where('ctickets.enattente', false)
            ->whereMonth('ctickets.cticketdate', $today->month)
            ->whereYear('ctickets.cticketdate', $today->year)
            ->select('produits.produitlibelle', DB::raw('SUM(detctickets.qte) as total_vendus'), DB::raw('SUM(detctickets.totalttcnet) as total_ca'))
            ->groupBy('produits.produitlibelle')
            ->orderBy('total_vendus', 'desc')
            ->take(5)
            ->get();

        // Flops (Articles les moins vendus)
        $flops = DB::table('detctickets')
            ->join('ctickets', 'detctickets.cticketid', '=', 'ctickets.cticketid')
            ->join('produits', 'detctickets.produitid', '=', 'produits.produitid')
            ->where('ctickets.siteid', $siteid)
            ->where('ctickets.enattente', false)
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
            ->where('ctickets.siteid', $siteid)
            ->where('ctickets.enattente', false)
            ->whereMonth('ctickets.cticketdate', $today->month)
            ->whereYear('ctickets.cticketdate', $today->year)
            ->select(DB::raw('COALESCE(familles.famillelibelle, \'Non Catégorisé\') as famille'), DB::raw('SUM(detctickets.totalttcnet) as total_ca'))
            ->groupBy('familles.famillelibelle')
            ->orderBy('total_ca', 'desc')
            ->take(5)
            ->get();

        // Heures de pointe
        $peakHours = DB::table('ctickets')
            ->select(DB::raw('EXTRACT(HOUR FROM cticketdate) as hour'), DB::raw('SUM(totalttc) as total_ca'))
            ->where('siteid', $siteid)
            ->where('enattente', false)
            ->whereMonth('cticketdate', $today->month)
            ->whereYear('cticketdate', $today->year)
            ->groupBy(DB::raw('EXTRACT(HOUR FROM cticketdate)'))
            ->orderBy('hour')
            ->get();

        // Top Vendeurs
        $topVendeurs = DB::table('ctickets')
            ->leftJoin('employees', 'ctickets.employeeid', '=', 'employees.employeeid')
            ->where('ctickets.siteid', $siteid)
            ->where('ctickets.enattente', false)
            ->whereMonth('ctickets.cticketdate', $today->month)
            ->whereYear('ctickets.cticketdate', $today->year)
            ->select(DB::raw('COALESCE(employees.nom, \'Admin/Inconnu\') as nom'), DB::raw('SUM(ctickets.totalttc) as total_ca'), DB::raw('COUNT(ctickets.cticketid) as nb_tickets'))
            ->groupBy('employees.employeeid', 'employees.nom')
            ->orderBy('total_ca', 'desc')
            ->take(5)
            ->get();

        // Top Clients (Exclure PASSAGER)
        $topClients = DB::table('ctickets')
            ->leftJoin('clients', 'ctickets.clientid', '=', 'clients.clientid')
            ->where('ctickets.siteid', $siteid)
            ->where('ctickets.enattente', false)
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
