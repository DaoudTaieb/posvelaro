@extends('layouts.app')
@section('title', 'Clôture Journée')

@section('content')
<div class="pos-container" style="max-width: 960px; margin: 0 auto; padding-top: 10px;">
    
    <!-- En-tête de la Page -->
    <div class="page-header" style="margin-bottom: 32px; display: flex; align-items: center; justify-content: space-between;">
        <div>
            <h1 class="page-title" style="font-size: 26px; font-weight: 800; color: var(--text-main); margin-bottom: 6px;">Clôture de Journée</h1>
            <p class="page-subtitle" style="font-size: 14px; color: var(--text-muted);">
                Saisissez les montants réels comptés en caisse pour finaliser votre session de vente.
            </p>
        </div>
        <div style="text-align: right; display: flex; flex-direction: column; gap: 6px;">
            <div class="modern-badge badge-info" style="font-weight: 600; padding: 6px 14px; font-size: 12px; display: inline-flex; align-items: center; gap: 8px;">
                <span class="badge-dot" style="background: var(--info);"></span>
                {{ $caisse->libelle ?? 'Caisse de vente' }} (N° {{ $journalCaisse->journalcaissenumero }})
            </div>
            <div style="color: var(--text-muted); font-size: 11px; font-weight: 500;">
                Session ouverte le {{ \Carbon\Carbon::parse($journalCaisse->dateouverture)->format('d/m/Y H:i') }}
            </div>
        </div>
    </div>

    <!-- KPI Cards (Résumé de la Session - en haut) -->
    <div class="kpi-grid" style="margin-bottom: 32px;">
        <!-- Nombre de tickets -->
        <div class="kpi-card" style="box-shadow: var(--shadow-sm); border-radius: var(--radius-lg); background: var(--surface);">
            <div class="kpi-icon-wrapper bg-indigo-light" style="width: 48px; height: 48px; border-radius: var(--radius-md); flex-shrink: 0;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                </svg>
            </div>
            <div class="kpi-info" style="text-align: left;">
                <span class="kpi-label" style="font-size: 11px; font-weight: 600; color: var(--text-muted);">Tickets Vendus</span>
                <span class="kpi-value" style="font-size: 24px; font-weight: 800; color: var(--text-main);">
                    {{ $ticketsTotals ? (int) $ticketsTotals->nbreticket : 0 }}
                </span>
            </div>
        </div>

        <!-- Recette brute -->
        <div class="kpi-card" style="box-shadow: var(--shadow-sm); border-radius: var(--radius-lg); background: var(--surface);">
            <div class="kpi-icon-wrapper bg-blue-light" style="width: 48px; height: 48px; border-radius: var(--radius-md); flex-shrink: 0;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--info)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="1" x2="12" y2="23"></line>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
            </div>
            <div class="kpi-info" style="text-align: left;">
                <span class="kpi-label" style="font-size: 11px; font-weight: 600; color: var(--text-muted);">Recette Brute</span>
                <span class="kpi-value text-info" style="font-size: 24px; font-weight: 800; color: var(--info);">
                    {{ $ticketsTotals ? number_format($ticketsTotals->recettebrut ?? 0, 3, '.', ' ') : '0.000' }} <span style="font-size: 13px; font-weight: 600;">TND</span>
                </span>
            </div>
        </div>

        <!-- Quantité vendue -->
        <div class="kpi-card" style="box-shadow: var(--shadow-sm); border-radius: var(--radius-lg); background: var(--surface);">
            <div class="kpi-icon-wrapper bg-green-light" style="width: 48px; height: 48px; border-radius: var(--radius-md); flex-shrink: 0;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <div class="kpi-info" style="text-align: left;">
                <span class="kpi-label" style="font-size: 11px; font-weight: 600; color: var(--text-muted);">Quantité Vendue</span>
                <span class="kpi-value text-success" style="font-size: 24px; font-weight: 800; color: var(--success);">
                    {{ $ticketsTotals ? number_format($ticketsTotals->totalqtevente ?? 0, 0) : 0 }}
                </span>
            </div>
        </div>

        <!-- Total remises -->
        <div class="kpi-card" style="box-shadow: var(--shadow-sm); border-radius: var(--radius-lg); background: var(--surface);">
            <div class="kpi-icon-wrapper bg-red-light" style="width: 48px; height: 48px; border-radius: var(--radius-md); flex-shrink: 0;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--danger)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            </div>
            <div class="kpi-info" style="text-align: left;">
                <span class="kpi-label" style="font-size: 11px; font-weight: 600; color: var(--text-muted);">Total Remises</span>
                <span class="kpi-value text-danger" style="font-size: 24px; font-weight: 800; color: var(--danger);">
                    {{ $ticketsTotals ? number_format($ticketsTotals->vtotalremise ?? 0, 3, '.', ' ') : '0.000' }} <span style="font-size: 13px; font-weight: 600;">TND</span>
                </span>
            </div>
        </div>
    </div>

    <!-- Alertes -->
    @if(session('success'))
    <div class="modern-badge badge-success" style="width: 100%; padding: 14px 18px; border-radius: var(--radius-md); font-size: 14px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px; line-height: 1.5; box-shadow: var(--shadow-sm);">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
        </svg>
        <span style="font-weight: 600;">{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="modern-badge badge-danger" style="width: 100%; padding: 14px 18px; border-radius: var(--radius-md); font-size: 14px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px; line-height: 1.5; box-shadow: var(--shadow-sm);">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
        <span style="font-weight: 600;">{{ session('error') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div style="background: var(--danger-bg); border: 1px solid var(--border); color: #991b1b; padding: 20px; border-radius: var(--radius-lg); margin-bottom: 24px; box-shadow: var(--shadow-sm);">
        <div style="display: flex; align-items: center; gap: 12px; font-weight: 700; margin-bottom: 10px; font-size: 14px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            Veuillez corriger les erreurs suivantes :
        </div>
        <ul style="margin: 0; padding-left: 28px; font-size: 13px; font-weight: 500; display: flex; flex-direction: column; gap: 4px; text-align: left;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Carte principale (Fermeture des Comptes) -->
    <div class="content-card" style="box-shadow: var(--shadow-premium); border-radius: var(--radius-lg); overflow: hidden; background: var(--surface); border: 1px solid var(--border);">
        
        <!-- En-tête interne de la carte -->
        <div style="padding: 24px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; background: #fdfdfe;">
            <div style="display: flex; align-items: center; gap: 16px;">
                <div class="kpi-icon-wrapper bg-indigo-light" style="width: 44px; height: 44px; border-radius: var(--radius-md); flex-shrink: 0; background: var(--primary-light);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                </div>
                <div style="text-align: left;">
                    <h2 style="font-size: 18px; font-weight: 700; color: var(--text-main); margin: 0;">Fermeture des Comptes</h2>
                    <p style="font-size: 12px; color: var(--text-muted); margin: 0; padding-top: 2px;">Vérifiez et saisissez le solde physique de chaque mode de paiement.</p>
                </div>
            </div>
        </div>

        <!-- Formulaire de clôture -->
        <form action="{{ route('vente.journee.cloture.store') }}" method="POST" id="clotureForm">
            @csrf
            <input type="hidden" name="journalcaisseid" value="{{ $journalCaisse->journalcaisseid }}">

            <div class="table-responsive" style="min-height: auto;">
                <table class="data-table" style="width: 100%; border-collapse: separate; border-spacing: 0;">
                    <thead>
                        <tr style="background: var(--background);">
                            <th style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; border-bottom: 2px solid var(--border);">Mode de paiement</th>
                            <th class="text-right" style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; width: 200px; border-bottom: 2px solid var(--border);">Solde Théorique</th>
                            <th class="text-right" style="padding: 16px 24px; font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; width: 260px; border-bottom: 2px solid var(--border);">Solde Compté (Physique)</th>
                            <th style="width: 80px; border-bottom: 2px solid var(--border);"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Fond de Caisse (Highlighted Row) -->
                        <tr style="background: #fefce8; transition: background-color 0.2s;">
                            <td style="padding: 18px 24px; font-weight: 700; border-bottom: 1px solid var(--border);">
                                <div style="display: flex; align-items: center; gap: 12px; color: #b45309;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="12" y1="1" x2="12" y2="23"></line>
                                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                    </svg>
                                    Fond de Caisse
                                </div>
                            </td>
                            <td class="amount-cell font-bold" style="padding: 18px 24px; color: #b45309; font-size: 15px; font-weight: 700; border-bottom: 1px solid var(--border);">
                                {{ number_format($theoriques['fondcaisse'], 3, '.', ' ') }} <span style="font-size: 11px; font-weight: 600;">TND</span>
                            </td>
                            <td class="amount-cell text-muted" style="padding: 18px 24px; font-size: 15px; font-weight: 700; border-bottom: 1px solid var(--border);">
                                {{ number_format($theoriques['fondcaisse'], 3, '.', ' ') }} <span style="font-size: 11px; font-weight: 500;">TND</span>
                            </td>
                            <td style="border-bottom: 1px solid var(--border);"></td>
                        </tr>

                        <!-- Espèce -->
                        <tr style="transition: background-color 0.15s;">
                            <td style="padding: 14px 24px; font-weight: 600; border-bottom: 1px solid var(--border);">
                                <div style="display: flex; align-items: center; gap: 12px; color: var(--text-main);">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="2" y="6" width="20" height="12" rx="2"></rect>
                                        <circle cx="12" cy="12" r="2"></circle>
                                        <path d="M6 12h.01M18 12h.01"></path>
                                    </svg>
                                    Espèce
                                </div>
                            </td>
                            <td class="amount-cell font-bold" style="padding: 14px 24px; font-size: 14px; color: var(--text-main); border-bottom: 1px solid var(--border);">
                                {{ number_format($theoriques['espece'], 3, '.', ' ') }} <span style="font-size: 11px; font-weight: 500; color: var(--text-muted);">TND</span>
                            </td>
                            <td style="padding: 10px 24px 10px 12px; border-bottom: 1px solid var(--border);">
                                <div style="position: relative; display: flex; align-items: center;">
                                    <input type="number" step="0.001" min="0" name="totalespecephys" id="totalespecephys"
                                           class="form-control cloture-input amount-cell" value=""
                                           placeholder="0.000"
                                           style="height: 40px; font-weight: 700; font-size: 15px; padding-right: 52px; border-radius: var(--radius-md); background: #fafafa;">
                                    <span style="position: absolute; right: 14px; font-size: 11px; font-weight: 700; color: var(--text-muted);">TND</span>
                                </div>
                            </td>
                            <td class="text-center" style="padding-right: 16px; border-bottom: 1px solid var(--border);">
                                <button type="button" class="btn btn-outline copy-btn" onclick="copyTheorique(this, 'totalespecephys', {{ $theoriques['espece'] }})" title="Copier la valeur théorique">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="12" y1="5" x2="12" y2="19"></line>
                                        <polyline points="19 12 12 19 5 12"></polyline>
                                    </svg>
                                </button>
                            </td>
                        </tr>

                        <!-- Chèque -->
                        <tr style="transition: background-color 0.15s;">
                            <td style="padding: 14px 24px; font-weight: 600; border-bottom: 1px solid var(--border);">
                                <div style="display: flex; align-items: center; gap: 12px; color: var(--text-main);">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                        <line x1="16" y1="13" x2="8" y2="13"></line>
                                        <line x1="16" y1="17" x2="8" y2="17"></line>
                                    </svg>
                                    Chèque
                                </div>
                            </td>
                            <td class="amount-cell font-bold" style="padding: 14px 24px; font-size: 14px; color: var(--text-main); border-bottom: 1px solid var(--border);">
                                {{ number_format($theoriques['cheque'], 3, '.', ' ') }} <span style="font-size: 11px; font-weight: 500; color: var(--text-muted);">TND</span>
                            </td>
                            <td style="padding: 10px 24px 10px 12px; border-bottom: 1px solid var(--border);">
                                <div style="position: relative; display: flex; align-items: center;">
                                    <input type="number" step="0.001" min="0" name="totalchequephys" id="totalchequephys"
                                           class="form-control cloture-input amount-cell" value=""
                                           placeholder="0.000"
                                           style="height: 40px; font-weight: 700; font-size: 15px; padding-right: 52px; border-radius: var(--radius-md); background: #fafafa;">
                                    <span style="position: absolute; right: 14px; font-size: 11px; font-weight: 700; color: var(--text-muted);">TND</span>
                                </div>
                            </td>
                            <td class="text-center" style="padding-right: 16px; border-bottom: 1px solid var(--border);">
                                <button type="button" class="btn btn-outline copy-btn" onclick="copyTheorique(this, 'totalchequephys', {{ $theoriques['cheque'] }})" title="Copier la valeur théorique">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="12" y1="5" x2="12" y2="19"></line>
                                        <polyline points="19 12 12 19 5 12"></polyline>
                                    </svg>
                                </button>
                            </td>
                        </tr>

                        <!-- Carte Bancaire -->
                        <tr style="transition: background-color 0.15s;">
                            <td style="padding: 14px 24px; font-weight: 600; border-bottom: 1px solid var(--border);">
                                <div style="display: flex; align-items: center; gap: 12px; color: var(--text-main);">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                                        <line x1="1" y1="10" x2="23" y2="10"></line>
                                    </svg>
                                    Carte Bancaire
                                </div>
                            </td>
                            <td class="amount-cell font-bold" style="padding: 14px 24px; font-size: 14px; color: var(--text-main); border-bottom: 1px solid var(--border);">
                                {{ number_format($theoriques['carte_bancaire'], 3, '.', ' ') }} <span style="font-size: 11px; font-weight: 500; color: var(--text-muted);">TND</span>
                            </td>
                            <td style="padding: 10px 24px 10px 12px; border-bottom: 1px solid var(--border);">
                                <div style="position: relative; display: flex; align-items: center;">
                                    <input type="number" step="0.001" min="0" name="totaltpephys" id="totaltpephys"
                                           class="form-control cloture-input amount-cell" value=""
                                           placeholder="0.000"
                                           style="height: 40px; font-weight: 700; font-size: 15px; padding-right: 52px; border-radius: var(--radius-md); background: #fafafa;">
                                    <span style="position: absolute; right: 14px; font-size: 11px; font-weight: 700; color: var(--text-muted);">TND</span>
                                </div>
                            </td>
                            <td class="text-center" style="padding-right: 16px; border-bottom: 1px solid var(--border);">
                                <button type="button" class="btn btn-outline copy-btn" onclick="copyTheorique(this, 'totaltpephys', {{ $theoriques['carte_bancaire'] }})" title="Copier la valeur théorique">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="12" y1="5" x2="12" y2="19"></line>
                                        <polyline points="19 12 12 19 5 12"></polyline>
                                    </svg>
                                </button>
                            </td>
                        </tr>

                        <!-- Chèque Cadeaux -->
                        <tr style="transition: background-color 0.15s;">
                            <td style="padding: 14px 24px; font-weight: 600; border-bottom: 1px solid var(--border);">
                                <div style="display: flex; align-items: center; gap: 12px; color: var(--text-main);">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 12 20 22 4 22 4 12"></polyline>
                                        <rect x="2" y="7" width="20" height="5"></rect>
                                        <line x1="12" y1="22" x2="12" y2="7"></line>
                                        <path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path>
                                        <path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path>
                                    </svg>
                                    Chèque Cadeaux
                                </div>
                            </td>
                            <td class="amount-cell font-bold" style="padding: 14px 24px; font-size: 14px; color: var(--text-main); border-bottom: 1px solid var(--border);">
                                {{ number_format($theoriques['cheque_cadeaux'], 3, '.', ' ') }} <span style="font-size: 11px; font-weight: 500; color: var(--text-muted);">TND</span>
                            </td>
                            <td style="padding: 10px 24px 10px 12px; border-bottom: 1px solid var(--border);">
                                <div style="position: relative; display: flex; align-items: center;">
                                    <input type="number" step="0.001" min="0" name="totalcontrebonphys" id="totalcontrebonphys"
                                           class="form-control cloture-input amount-cell" value=""
                                           placeholder="0.000"
                                           style="height: 40px; font-weight: 700; font-size: 15px; padding-right: 52px; border-radius: var(--radius-md); background: #fafafa;">
                                    <span style="position: absolute; right: 14px; font-size: 11px; font-weight: 700; color: var(--text-muted);">TND</span>
                                </div>
                            </td>
                            <td class="text-center" style="padding-right: 16px; border-bottom: 1px solid var(--border);">
                                <button type="button" class="btn btn-outline copy-btn" onclick="copyTheorique(this, 'totalcontrebonphys', {{ $theoriques['cheque_cadeaux'] }})" title="Copier la valeur théorique">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="12" y1="5" x2="12" y2="19"></line>
                                        <polyline points="19 12 12 19 5 12"></polyline>
                                    </svg>
                                </button>
                            </td>
                        </tr>

                        <!-- Bon de convention -->
                        <tr style="transition: background-color 0.15s;">
                            <td style="padding: 14px 24px; font-weight: 600; border-bottom: 1px solid var(--border);">
                                <div style="display: flex; align-items: center; gap: 12px; color: var(--text-main);">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                                        <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                                        <path d="M9 12h6M9 16h6"></path>
                                    </svg>
                                    Bon de convention
                                </div>
                            </td>
                            <td class="amount-cell font-bold" style="padding: 14px 24px; font-size: 14px; color: var(--text-main); border-bottom: 1px solid var(--border);">
                                {{ number_format($theoriques['bon_convention'], 3, '.', ' ') }} <span style="font-size: 11px; font-weight: 500; color: var(--text-muted);">TND</span>
                            </td>
                            <td style="padding: 10px 24px 10px 12px; border-bottom: 1px solid var(--border);">
                                <div style="position: relative; display: flex; align-items: center;">
                                    <input type="number" step="0.001" min="0" name="totalbonconventionphys" id="totalbonconventionphys"
                                           class="form-control cloture-input amount-cell" value=""
                                           placeholder="0.000"
                                           style="height: 40px; font-weight: 700; font-size: 15px; padding-right: 52px; border-radius: var(--radius-md); background: #fafafa;">
                                    <span style="position: absolute; right: 14px; font-size: 11px; font-weight: 700; color: var(--text-muted);">TND</span>
                                </div>
                            </td>
                            <td class="text-center" style="padding-right: 16px; border-bottom: 1px solid var(--border);">
                                <button type="button" class="btn btn-outline copy-btn" onclick="copyTheorique(this, 'totalbonconventionphys', {{ $theoriques['bon_convention'] }})" title="Copier la valeur théorique">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="12" y1="5" x2="12" y2="19"></line>
                                        <polyline points="19 12 12 19 5 12"></polyline>
                                    </svg>
                                </button>
                            </td>
                        </tr>

                        <!-- Avoir -->
                        <tr style="transition: background-color 0.15s;">
                            <td style="padding: 14px 24px; font-weight: 600; border-bottom: 1px solid var(--border);">
                                <div style="display: flex; align-items: center; gap: 12px; color: var(--text-main);">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2.5 2v6h6M21.5 22v-6h-6"/><path d="M22 11.5A10 10 0 0 0 3.2 7.2L2.5 8M2 12.5a10 10 0 0 0 18.8 4.3l.7-.8"/>
                                    </svg>
                                    Avoir
                                </div>
                            </td>
                            <td class="amount-cell font-bold" style="padding: 14px 24px; font-size: 14px; color: var(--text-main); border-bottom: 1px solid var(--border);">
                                {{ number_format($theoriques['avoir'], 3, '.', ' ') }} <span style="font-size: 11px; font-weight: 500; color: var(--text-muted);">TND</span>
                            </td>
                            <td style="padding: 10px 24px 10px 12px; border-bottom: 1px solid var(--border);">
                                <div style="position: relative; display: flex; align-items: center;">
                                    <input type="number" step="0.001" min="0" name="totalregavoirphys" id="totalregavoirphys"
                                           class="form-control cloture-input amount-cell" value=""
                                           placeholder="0.000"
                                           style="height: 40px; font-weight: 700; font-size: 15px; padding-right: 52px; border-radius: var(--radius-md); background: #fafafa;">
                                    <span style="position: absolute; right: 14px; font-size: 11px; font-weight: 700; color: var(--text-muted);">TND</span>
                                </div>
                            </td>
                            <td class="text-center" style="padding-right: 16px; border-bottom: 1px solid var(--border);">
                                <button type="button" class="btn btn-outline copy-btn" onclick="copyTheorique(this, 'totalregavoirphys', {{ $theoriques['avoir'] }})" title="Copier la valeur théorique">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="12" y1="5" x2="12" y2="19"></line>
                                        <polyline points="19 12 12 19 5 12"></polyline>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pied de page avec bouton clôturer -->
            <div style="padding: 24px 32px; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; align-items: center; background: #fdfdfe; gap: 16px;">
                <button type="submit" id="submitCloture" class="btn btn-primary" onclick="return confirm('Êtes-vous sûr de vouloir clôturer cette journée de vente ?')" style="height: 46px; padding: 0 36px; font-size: 15px; font-weight: 700; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); display: inline-flex; align-items: center; gap: 10px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    Clôturer la journée de vente
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .cloture-input:focus {
        border-color: var(--primary) !important;
        background: #ffffff !important;
        box-shadow: 0 0 0 3px var(--primary-light) !important;
    }

    .copy-btn {
        width: 32px !important;
        height: 32px !important;
        padding: 0 !important;
        border-radius: var(--radius-sm) !important;
        border-color: var(--border) !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        cursor: pointer;
    }

    .copy-btn:hover {
        background: var(--primary-light) !important;
        color: var(--primary) !important;
        border-color: var(--primary) !important;
    }

    .copy-btn.copied {
        background: var(--success-bg) !important;
        color: var(--success) !important;
        border-color: var(--success) !important;
    }
    
    .data-table tbody tr:hover {
        background-color: rgba(248, 250, 252, 0.6) !important;
    }
</style>
@endsection

@section('scripts')
<script>
    function copyTheorique(btn, inputId, value) {
        const input = document.getElementById(inputId);
        input.value = value.toFixed(3);
        input.focus();

        // Feedback visuel
        btn.classList.add('copied');
        const originalHTML = btn.innerHTML;
        btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>`;
        
        setTimeout(() => {
            btn.classList.remove('copied');
            btn.innerHTML = originalHTML;
        }, 1000);
    }
</script>
@endsection
