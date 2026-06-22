@extends('layouts.app')

@section('title', 'Velaro POS — Dashboard')

@section('styles')
<style>
    /* Animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in-up {
        animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        opacity: 0;
    }

    .delay-100 { animation-delay: 100ms; }
    .delay-200 { animation-delay: 200ms; }
    .delay-300 { animation-delay: 300ms; }
    .delay-400 { animation-delay: 400ms; }

    /* Dashboard Layout */
    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 32px;
    }

    .welcome-title {
        font-size: 32px;
        font-weight: 800;
        color: var(--text);
        letter-spacing: -1px;
        margin-bottom: 8px;
    }

    .welcome-title span {
        background: linear-gradient(135deg, var(--primary), var(--accent));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .welcome-subtitle {
        font-size: 16px;
        color: var(--text-secondary);
        font-weight: 500;
    }

    .date-badge {
        display: flex;
        align-items: center;
        gap: 8px;
        background: var(--bg-card);
        padding: 10px 20px;
        border-radius: 100px;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border-light);
        font-weight: 600;
        color: var(--text-secondary);
        backdrop-filter: var(--glass-blur);
    }

    /* KPI Grid */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 24px;
        margin-bottom: 32px;
    }

    .kpi-card {
        background: var(--bg-card);
        border-radius: 20px;
        padding: 24px;
        box-shadow: var(--shadow-md);
        border: 1px solid var(--border-light);
        backdrop-filter: var(--glass-blur);
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
        overflow: hidden;
    }

    .kpi-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-premium);
        border-color: var(--primary-light);
    }

    .kpi-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, var(--primary), var(--accent));
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .kpi-card:hover::before {
        opacity: 1;
    }

    .kpi-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 16px;
    }

    .kpi-title {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .kpi-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .kpi-icon.primary { background: var(--primary-light); color: var(--primary); }
    .kpi-icon.success { background: var(--success-bg); color: var(--success); }
    .kpi-icon.warning { background: var(--warning-bg); color: var(--warning); }
    .kpi-icon.accent { background: var(--accent-light); color: var(--accent); }

    .kpi-value {
        font-size: 32px;
        font-weight: 800;
        color: var(--text);
        margin-bottom: 8px;
        letter-spacing: -0.5px;
    }

    .kpi-trend {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 14px;
        font-weight: 600;
    }

    .kpi-trend.positive { color: var(--success); }
    .kpi-trend.negative { color: var(--danger); }

    /* Main Content Area */
    .dashboard-content {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
    }

    .card {
        background: var(--bg-card);
        border-radius: 20px;
        padding: 24px;
        box-shadow: var(--shadow-md);
        border: 1px solid var(--border-light);
        backdrop-filter: var(--glass-blur);
    }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }

    .card-title {
        font-size: 18px;
        font-weight: 700;
        color: var(--text);
    }

    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }

    /* Activity List */
    .activity-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .activity-item {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 12px;
        border-radius: 12px;
        transition: background 0.2s;
    }

    .activity-item:hover {
        background: var(--bg);
    }

    .activity-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--primary-light);
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .activity-details {
        flex: 1;
    }

    .activity-title {
        font-size: 14px;
        font-weight: 600;
        color: var(--text);
        margin-bottom: 2px;
    }

    .activity-time {
        font-size: 12px;
        color: var(--text-muted);
    }

    .activity-amount {
        font-weight: 700;
        font-size: 15px;
        color: var(--success);
    }
    
    /* Responsive */
    @media (max-width: 1024px) {
        .dashboard-content {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
<div class="main-content-inner full-width">
    
    <div class="dashboard-header animate-fade-in-up">
        <div>
            <h1 class="welcome-title">Bonjour, <span>{{ Auth::user()->login ?? 'Utilisateur' }}</span> ✨</h1>
            <p class="welcome-subtitle">Voici l'aperçu de votre activité aujourd'hui</p>
        </div>
        <div class="date-badge">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            {{ \Carbon\Carbon::now()->translatedFormat('l j F Y') }}
        </div>
    </div>

    <!-- KPIs -->
    <div class="kpi-grid">
        <div class="kpi-card animate-fade-in-up delay-100">
            <div class="kpi-header">
                <h3 class="kpi-title">Chiffre d'Affaires</h3>
                <div class="kpi-icon primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                </div>
            </div>
            <div class="kpi-value">{{ number_format($caToday, 3, ',', ' ') }} DT</div>
            <div class="kpi-trend {{ $caTrend >= 0 ? 'positive' : 'negative' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    @if($caTrend >= 0)
                        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline>
                    @else
                        <polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline><polyline points="17 18 23 18 23 12"></polyline>
                    @endif
                </svg>
                {{ $caTrend > 0 ? '+' : '' }}{{ number_format($caTrend, 1, ',', '') }}% vs hier
            </div>
        </div>

        <div class="kpi-card animate-fade-in-up delay-200">
            <div class="kpi-header">
                <h3 class="kpi-title">Tickets</h3>
                <div class="kpi-icon success">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                </div>
            </div>
            <div class="kpi-value">{{ $ticketsToday }}</div>
            <div class="kpi-trend {{ $ticketsTrend >= 0 ? 'positive' : 'negative' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    @if($ticketsTrend >= 0)
                        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline>
                    @else
                        <polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline><polyline points="17 18 23 18 23 12"></polyline>
                    @endif
                </svg>
                {{ $ticketsTrend > 0 ? '+' : '' }}{{ number_format($ticketsTrend, 1, ',', '') }}% vs hier
            </div>
        </div>

        <div class="kpi-card animate-fade-in-up delay-300">
            <div class="kpi-header">
                <h3 class="kpi-title">Panier Moyen</h3>
                <div class="kpi-icon warning">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                </div>
            </div>
            <div class="kpi-value">{{ number_format($panierMoyenToday, 3, ',', ' ') }} DT</div>
            <div class="kpi-trend {{ $panierMoyenTrend >= 0 ? 'positive' : 'negative' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    @if($panierMoyenTrend >= 0)
                        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline>
                    @else
                        <polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline><polyline points="17 18 23 18 23 12"></polyline>
                    @endif
                </svg>
                {{ $panierMoyenTrend > 0 ? '+' : '' }}{{ number_format($panierMoyenTrend, 1, ',', '') }}% vs hier
            </div>
        </div>

        <div class="kpi-card animate-fade-in-up delay-400">
            <div class="kpi-header">
                <h3 class="kpi-title">Articles Vendus</h3>
                <div class="kpi-icon accent">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                </div>
            </div>
            <div class="kpi-value">{{ number_format($articlesToday, 0, ',', ' ') }}</div>
            <div class="kpi-trend {{ $articlesTrend >= 0 ? 'positive' : 'negative' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    @if($articlesTrend >= 0)
                        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline>
                    @else
                        <polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline><polyline points="17 18 23 18 23 12"></polyline>
                    @endif
                </svg>
                {{ $articlesTrend > 0 ? '+' : '' }}{{ number_format($articlesTrend, 1, ',', '') }}% vs hier
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="dashboard-grid-advanced">
        
        <!-- Row 1: Main Chart (2 cols) + Category Pie (1 col) -->
        <div class="card animate-fade-in-up delay-300 col-span-2">
            <div class="card-header">
                <h2 class="card-title">Évolution des ventes (7 derniers jours)</h2>
            </div>
            <div class="chart-container">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <div class="card animate-fade-in-up delay-300 col-span-1">
            <div class="card-header">
                <h2 class="card-title">Ventes par Rayon (Ce Mois)</h2>
            </div>
            <div class="chart-container">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>

        <!-- Row 2: Peak Hours (2 cols) + Recent Activity (1 col) -->
        <div class="card animate-fade-in-up delay-400 col-span-2">
            <div class="card-header">
                <h2 class="card-title">Heures de pointe (CA par Heure)</h2>
            </div>
            <div class="chart-container">
                <canvas id="peakHoursChart"></canvas>
            </div>
        </div>

        <div class="card animate-fade-in-up delay-400 col-span-1">
            <div class="card-header">
                <h2 class="card-title">Dernières Transactions</h2>
            </div>
            <div class="activity-list">
                @forelse($recentTickets as $ticket)
                <div class="activity-item">
                    <div class="activity-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                    </div>
                    <div class="activity-details">
                        <div class="activity-title">Ticket #{{ $ticket->cticketnumero }}</div>
                        <div class="activity-time">Il y a {{ \Carbon\Carbon::parse($ticket->cticketdate)->diffForHumans(null, true) }}</div>
                    </div>
                    <div class="activity-amount">{{ number_format($ticket->totalttc, 3, ',', ' ') }} DT</div>
                </div>
                @empty
                <div class="activity-item"><div class="activity-details"><div class="activity-title text-muted">Aucune transaction récente</div></div></div>
                @endforelse
            </div>
        </div>

        <!-- Row 3: Top Products, Flops, Top Vendeurs -->
        <div class="card animate-fade-in-up delay-500 col-span-1">
            <div class="card-header">
                <h2 class="card-title">Top Articles (Ce Mois)</h2>
            </div>
            <div class="activity-list">
                @php $maxTop = $topProducts->max('total_ca') ?: 1; @endphp
                @forelse($topProducts as $product)
                <div class="activity-item" style="flex-wrap: wrap;">
                    <div style="display: flex; width: 100%; align-items: center; gap: 1rem;">
                        <div class="activity-icon" style="background: var(--accent-bg); color: var(--accent);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                        </div>
                        <div class="activity-details">
                            <div class="activity-title">{{ \Illuminate\Support\Str::limit($product->produitlibelle, 25) }}</div>
                            <div class="activity-time">{{ number_format($product->total_vendus, 0) }} vendus</div>
                        </div>
                        <div class="activity-amount">{{ number_format($product->total_ca, 3, ',', ' ') }} DT</div>
                    </div>
                    <div style="width: 100%; height: 4px; background: #f1f5f9; border-radius: 2px; margin-top: 10px; margin-left: 3.5rem;">
                        <div style="height: 100%; background: var(--accent); border-radius: 2px; width: {{ ($product->total_ca / $maxTop) * 100 }}%;"></div>
                    </div>
                </div>
                @empty
                <div class="activity-item"><div class="activity-details"><div class="activity-title text-muted">Aucune donnée</div></div></div>
                @endforelse
            </div>
        </div>

        <div class="card animate-fade-in-up delay-500 col-span-1">
            <div class="card-header">
                <h2 class="card-title">Pires Articles (Flops)</h2>
            </div>
            <div class="activity-list">
                @forelse($flops as $product)
                <div class="activity-item">
                    <div class="activity-icon" style="background: var(--danger-bg); color: var(--danger);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                    </div>
                    <div class="activity-details">
                        <div class="activity-title">{{ \Illuminate\Support\Str::limit($product->produitlibelle, 25) }}</div>
                        <div class="activity-time">{{ number_format($product->total_vendus, 0) }} vendus</div>
                    </div>
                </div>
                @empty
                <div class="activity-item"><div class="activity-details"><div class="activity-title text-muted">Aucune donnée</div></div></div>
                @endforelse
            </div>
        </div>

        <div class="card animate-fade-in-up delay-500 col-span-1">
            <div class="card-header">
                <h2 class="card-title">Top Vendeurs</h2>
            </div>
            <div class="activity-list">
                @php $maxVendeur = $topVendeurs->max('total_ca') ?: 1; @endphp
                @forelse($topVendeurs as $vendeur)
                <div class="activity-item" style="flex-wrap: wrap;">
                    <div style="display: flex; width: 100%; align-items: center; gap: 1rem;">
                        <div class="activity-icon" style="background: var(--success-bg); color: var(--success);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        </div>
                        <div class="activity-details">
                            <div class="activity-title">{{ $vendeur->nom }}</div>
                            <div class="activity-time">{{ $vendeur->nb_tickets }} tickets</div>
                        </div>
                        <div class="activity-amount">{{ number_format($vendeur->total_ca, 3, ',', ' ') }} DT</div>
                    </div>
                    <div style="width: 100%; height: 4px; background: #f1f5f9; border-radius: 2px; margin-top: 10px; margin-left: 3.5rem;">
                        <div style="height: 100%; background: var(--success); border-radius: 2px; width: {{ ($vendeur->total_ca / $maxVendeur) * 100 }}%;"></div>
                    </div>
                </div>
                @empty
                <div class="activity-item"><div class="activity-details"><div class="activity-title text-muted">Aucune donnée</div></div></div>
                @endforelse
            </div>
        </div>
        
        <!-- Row 4: Top Clients -->
        <div class="card animate-fade-in-up delay-600 col-span-3">
            <div class="card-header">
                <h2 class="card-title">Meilleurs Clients</h2>
            </div>
            <div class="activity-list" style="display: flex; gap: 1rem; justify-content: space-between; overflow-x: auto; padding-bottom: 0.5rem;">
                @php $maxClient = $topClients->max('total_ca') ?: 1; @endphp
                @forelse($topClients as $client)
                <div class="activity-item" style="flex: 1; min-width: 200px; border: 1px solid var(--border-color); padding: 1.25rem; border-radius: 12px; justify-content: center; flex-direction: column; text-align: center; position: relative; overflow: hidden;">
                    <div style="position: absolute; bottom: 0; left: 0; height: 4px; background: var(--warning); width: {{ ($client->total_ca / $maxClient) * 100 }}%;"></div>
                    <div class="activity-icon" style="margin: 0 auto 0.75rem auto; background: var(--warning-bg); color: var(--warning);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    </div>
                    <div class="activity-title" style="font-size: 1.1rem; margin-bottom: 0.25rem;">{{ $client->prenom }} {{ $client->nom }}</div>
                    <div class="activity-amount" style="font-size: 1.25rem; color: var(--text-color);">{{ number_format($client->total_ca, 3, ',', ' ') }} DT</div>
                </div>
                @empty
                <div class="text-muted" style="padding: 1rem; width: 100%; text-align: center;">Aucune donnée client disponible</div>
                @endforelse
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- 1. Main Sales Chart ---
        const ctxSales = document.getElementById('salesChart').getContext('2d');
        const gradientSales = ctxSales.createLinearGradient(0, 0, 0, 400);
        gradientSales.addColorStop(0, 'rgba(99, 102, 241, 0.4)');
        gradientSales.addColorStop(1, 'rgba(99, 102, 241, 0.05)');

        new Chart(ctxSales, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [{
                    label: 'Chiffre d\'Affaires (DT)',
                    data: {!! json_encode($chartData) !!},
                    borderColor: '#6366f1',
                    backgroundColor: gradientSales,
                    borderWidth: 3,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#6366f1',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { backgroundColor: 'rgba(15, 23, 42, 0.9)', titleFont: { size: 13, family: 'Inter' }, bodyFont: { size: 14, family: 'Inter', weight: 'bold' }, padding: 12, cornerRadius: 8, displayColors: false, callbacks: { label: function(context) { return context.parsed.y + ' DT'; } } } },
                scales: { x: { grid: { display: false, drawBorder: false }, ticks: { font: { family: 'Inter', size: 12 }, color: '#64748b' } }, y: { grid: { color: '#f1f5f9', drawBorder: false }, ticks: { font: { family: 'Inter', size: 12 }, color: '#64748b', callback: function(value) { return value + ' DT'; } } } },
                interaction: { intersect: false, mode: 'index' },
            }
        });

        // --- 2. Category Pie Chart ---
        const ctxCat = document.getElementById('categoryChart').getContext('2d');
        new Chart(ctxCat, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($categorySales->pluck('famille')) !!},
                datasets: [{
                    data: {!! json_encode($categorySales->pluck('total_ca')) !!},
                    backgroundColor: ['#6366f1', '#0ea5e9', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { position: 'bottom', labels: { font: { family: 'Inter', size: 12 }, usePointStyle: true, boxWidth: 8 } },
                    tooltip: { backgroundColor: 'rgba(15, 23, 42, 0.9)', titleFont: { size: 13, family: 'Inter' }, bodyFont: { size: 14, family: 'Inter', weight: 'bold' }, padding: 12, cornerRadius: 8, callbacks: { label: function(context) { return context.label + ': ' + context.parsed + ' DT'; } } }
                }
            }
        });

        // --- 3. Peak Hours Bar Chart ---
        const ctxPeak = document.getElementById('peakHoursChart').getContext('2d');
        new Chart(ctxPeak, {
            type: 'bar',
            data: {
                labels: {!! json_encode($peakHours->map(function($h){ return str_pad($h->hour, 2, '0', STR_PAD_LEFT).'h'; })) !!},
                datasets: [{
                    label: 'CA par heure',
                    data: {!! json_encode($peakHours->pluck('total_ca')) !!},
                    backgroundColor: '#0ea5e9',
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { backgroundColor: 'rgba(15, 23, 42, 0.9)', titleFont: { size: 13, family: 'Inter' }, bodyFont: { size: 14, family: 'Inter', weight: 'bold' }, padding: 12, cornerRadius: 8, displayColors: false, callbacks: { label: function(context) { return context.parsed.y + ' DT'; } } } },
                scales: { x: { grid: { display: false, drawBorder: false }, ticks: { font: { family: 'Inter', size: 12 }, color: '#64748b' } }, y: { grid: { color: '#f1f5f9', drawBorder: false }, ticks: { font: { family: 'Inter', size: 12 }, color: '#64748b', callback: function(value) { return value + ' DT'; } } } }
            }
        });
    });
</script>
@endsection
