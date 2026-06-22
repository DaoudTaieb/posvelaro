@extends('layouts.app')
@section('title', 'État de la Journée')

@section('content')
<div class="pos-container no-print" style="max-width: 1600px; margin: 0 auto; padding-bottom: 0;">
    
    <!-- En-tête de la Page (no-print) -->
    <div class="page-header" style="margin-bottom: 24px;">
        <div>
            <h1 class="page-title" style="font-size: 26px; font-weight: 800; color: var(--text-main); margin-bottom: 6px;">État de la Journée</h1>
            <p class="page-subtitle" style="font-size: 14px; color: var(--text-muted);">Bilan complet des transactions, chiffre d'affaires et modes de règlement pour la session active.</p>
        </div>
        <div class="header-actions">
            <!-- Bouton Filtrer -->
            <button class="btn btn-outline" onclick="openFilterModal()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                </svg>
                Filtrer
            </button>
            <!-- Bouton Réinitialiser -->
            <button class="btn btn-outline" onclick="window.location.href='{{ route('vente.journee.etat') }}'" title="Réinitialiser">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M23 4v6h-6"></path>
                    <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                </svg>
                Actualiser
            </button>
            <!-- Bouton Imprimer -->
            <button class="btn btn-primary" onclick="window.print()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <rect x="6" y="14" width="12" height="8"></rect>
                </svg>
                Imprimer l'état
            </button>
        </div>
    </div>

    <!-- KPI Grid -->
    <div class="kpi-grid no-print" style="margin-bottom: 24px;">
        <!-- Recette Nette -->
        <div class="kpi-card" style="box-shadow: var(--shadow-sm); border-radius: var(--radius-lg); background: var(--surface);">
            <div class="kpi-icon-wrapper bg-green-light" style="width: 48px; height: 48px; border-radius: var(--radius-md); flex-shrink: 0;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="6" width="20" height="12" rx="2"></rect>
                    <circle cx="12" cy="12" r="2"></circle>
                    <path d="M6 12h.01M18 12h.01"></path>
                </svg>
            </div>
            <div class="kpi-info" style="text-align: left;">
                <span class="kpi-label" style="font-size: 11px; font-weight: 600; color: var(--text-muted);">Recette Nette</span>
                <span class="kpi-value text-success" style="font-size: 22px; font-weight: 800;">
                    {{ number_format($caisseTotaux['recette_nette'], 3, '.', ' ') }} <span style="font-size: 12px; font-weight: 600;">TND</span>
                </span>
            </div>
        </div>

        <!-- Recette Brute -->
        <div class="kpi-card" style="box-shadow: var(--shadow-sm); border-radius: var(--radius-lg); background: var(--surface);">
            <div class="kpi-icon-wrapper bg-blue-light" style="width: 48px; height: 48px; border-radius: var(--radius-md); flex-shrink: 0;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--info)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="1" x2="12" y2="23"></line>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
            </div>
            <div class="kpi-info" style="text-align: left;">
                <span class="kpi-label" style="font-size: 11px; font-weight: 600; color: var(--text-muted);">Recette Brute</span>
                <span class="kpi-value text-info" style="font-size: 22px; font-weight: 800;">
                    {{ number_format($caisseTotaux['recette_brute'], 3, '.', ' ') }} <span style="font-size: 12px; font-weight: 600;">TND</span>
                </span>
            </div>
        </div>

        <!-- Ventes Générales -->
        <div class="kpi-card" style="box-shadow: var(--shadow-sm); border-radius: var(--radius-lg); background: var(--surface);">
            <div class="kpi-icon-wrapper bg-indigo-light" style="width: 48px; height: 48px; border-radius: var(--radius-md); flex-shrink: 0;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
            </div>
            <div class="kpi-info" style="text-align: left;">
                <span class="kpi-label" style="font-size: 11px; font-weight: 600; color: var(--text-muted);">Ventes (Qtés)</span>
                <span class="kpi-value text-primary" style="font-size: 22px; font-weight: 800;">
                    {{ number_format($totalVentesMontant, 3, '.', ' ') }} <span style="font-size: 12px; font-weight: 600;">TND</span>
                    <span style="font-size: 11px; font-weight: 500; color: var(--text-secondary); margin-left: 2px;">({{ round($totalVentesQte) }} p.)</span>
                </span>
            </div>
        </div>

        <!-- Retours -->
        <div class="kpi-card" style="box-shadow: var(--shadow-sm); border-radius: var(--radius-lg); background: var(--surface);">
            <div class="kpi-icon-wrapper bg-red-light" style="width: 48px; height: 48px; border-radius: var(--radius-md); flex-shrink: 0;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--danger)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M2.5 2v6h6M21.5 22v-6h-6"></path>
                    <path d="M22 11.5A10 10 0 0 0 9.5 2.5M2 12.5A10 10 0 0 0 14.5 21.5"></path>
                </svg>
            </div>
            <div class="kpi-info" style="text-align: left;">
                <span class="kpi-label" style="font-size: 11px; font-weight: 600; color: var(--text-muted);">Retours</span>
                <span class="kpi-value text-danger" style="font-size: 22px; font-weight: 800;">
                    {{ number_format(abs($totalRetourMontant), 3, '.', ' ') }} <span style="font-size: 12px; font-weight: 600;">TND</span>
                    <span style="font-size: 11px; font-weight: 500; color: var(--text-secondary); margin-left: 2px;">({{ round(abs($totalRetourQte)) }} p.)</span>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Conteneur principal d'impression et d'affichage -->
<div class="pos-container printable-area" style="max-width: 1600px; margin: 0 auto; padding-top: 0;">
    
    <div class="content-card etat-container" style="padding: 24px; box-shadow: var(--shadow-md); border-radius: var(--radius-lg); background: var(--surface); border: 1px solid var(--border);">
        
        <!-- En-tête uniquement pour l'impression -->
        <div class="print-only" style="display: none; text-align: center; border-bottom: 3px double var(--border); padding-bottom: 16px; margin-bottom: 24px;">
            <h1 style="font-size: 26px; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -0.5px;">VELARO POS</h1>
            <p style="font-size: 12px; color: #475569; margin: 4px 0 0 0; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Rapport de Clôture Caisse (État Journalier)</p>
            <div style="font-size: 11px; color: #94a3b8; margin-top: 4px;">Imprimé le {{ now()->format('d/m/Y H:i') }} par {{ Auth::user()->login }}</div>
        </div>

        <!-- En-tête du Rapport -->
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--border); padding-bottom: 16px; margin-bottom: 24px;">
            <div style="text-align: left;">
                <h2 style="font-size: 15px; font-weight: 800; color: var(--text-main); text-transform: uppercase; margin: 0; letter-spacing: 0.5px; display: flex; align-items: center; gap: 8px;">
                    CLÔTURE DE LA CAISSE : {{ $caisse->libelle ?? 'CAISSE DE VENTE' }}
                </h2>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px; font-weight: 500;">
                    Ouvert le {{ \Carbon\Carbon::parse($journalCaisse->dateouverture)->format('d-m-Y H:i') }} par <span style="color: var(--text-main); font-weight: 600;">{{ $adminName }}</span>
                </div>
            </div>
            <div style="text-align: right;">
                @if($journalCaisse->isclosed)
                    <span class="modern-badge badge-success" style="font-weight: 700; padding: 6px 14px;"><span class="badge-dot"></span>CAISSE CLÔTURÉE</span>
                @else
                    <span class="modern-badge badge-danger" style="font-weight: 700; padding: 6px 14px;"><span class="badge-dot"></span>SESSION EN COURS</span>
                @endif
            </div>
        </div>

        <!-- Section 1: Tables des Ventes et Chiffre d'Affaires -->
        <div class="grid-section" style="display: grid; grid-template-columns: 2.1fr 1.1fr; gap: 24px; margin-bottom: 24px;">
            
            <!-- Ventes & Retours -->
            <div class="content-card" style="border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--border); overflow: hidden;">
                <div style="background: #fdfdfe; padding: 16px 20px; font-weight: 700; text-transform: uppercase; border-bottom: 1px solid var(--border); font-size: 12px; color: var(--text-secondary); letter-spacing: 0.5px; text-align: left; display: flex; align-items: center; gap: 10px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                    </svg>
                    Ventes & Retours effectuées
                </div>
                <div class="table-responsive" style="min-height: auto;">
                    <table class="data-table compact-table">
                        <thead>
                            <tr>
                                <th style="padding: 10px 12px;">Code</th>
                                <th style="padding: 10px 12px;">Référence</th>
                                <th style="padding: 10px 12px;">Désignation</th>
                                <th style="padding: 10px 12px;">Couleur</th>
                                <th style="padding: 10px 12px;" class="text-center">Taille</th>
                                <th style="padding: 10px 12px;" class="text-right">P.U TTC</th>
                                <th style="padding: 10px 12px;" class="text-center">Qtés</th>
                                <th style="padding: 10px 12px;" class="text-right">Remise</th>
                                <th style="padding: 10px 12px;" class="text-right">Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ventesRaw as $v)
                            <tr>
                                <td style="padding: 8px 12px;">{{ $v->code }}</td>
                                <td style="padding: 8px 12px; font-weight: 500;">{{ $v->reference }}</td>
                                <td style="padding: 8px 12px; max-width: 140px; white-space: normal; word-wrap: break-word;">{{ $v->designation }}</td>
                                <td style="padding: 8px 12px;">{{ $v->couleur }}</td>
                                <td class="text-center" style="padding: 8px 12px; font-weight: 600;">{{ $v->taille }}</td>
                                <td class="amount-cell" style="padding: 8px 12px;">{{ number_format($v->pv_u_ttc, 3, '.', '') }}</td>
                                <td class="text-center font-bold" style="padding: 8px 12px;">{{ round($v->qtes) }}</td>
                                <td class="amount-cell text-danger" style="padding: 8px 12px;">{{ number_format($v->remise, 3, '.', '') }}</td>
                                <td class="amount-cell font-bold" style="padding: 8px 12px;">{{ number_format($v->montant, 3, '.', '') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="empty-state text-center" style="padding: 24px; color: var(--text-muted);">Aucune transaction enregistrée.</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr style="background: var(--background);">
                                <td colspan="6" class="totals-label" style="padding: 8px 12px; font-weight: 700;">Ventes</td>
                                <td class="text-center font-bold" style="padding: 8px 12px;">{{ $totalVentesQte }}</td>
                                <td class="amount-cell text-muted" style="padding: 8px 12px;">0.000</td>
                                <td class="amount-cell font-bold" style="padding: 8px 12px; color: var(--primary);">{{ number_format($totalVentesMontant, 3, '.', '') }}</td>
                            </tr>
                            <tr style="background: var(--background);">
                                <td colspan="6" class="totals-label" style="padding: 8px 12px; font-weight: 700; color: var(--danger);">Retour</td>
                                <td class="text-center font-bold text-danger" style="padding: 8px 12px;">{{ $totalRetourQte }}</td>
                                <td class="amount-cell text-muted" style="padding: 8px 12px;">0.000</td>
                                <td class="amount-cell font-bold text-danger" style="padding: 8px 12px;">{{ number_format($totalRetourMontant, 3, '.', '') }}</td>
                            </tr>
                            <tr class="table-totals" style="background: var(--primary-light);">
                                <td colspan="6" class="totals-label" style="padding: 10px 12px; font-weight: 800; color: var(--primary-dark);">Total Général</td>
                                <td class="text-center font-bold" style="padding: 10px 12px; color: var(--primary-dark);">{{ $totalVentesQte + $totalRetourQte }}</td>
                                <td class="amount-cell text-muted" style="padding: 10px 12px;">0.000</td>
                                <td class="amount-cell font-bold" style="padding: 10px 12px; color: var(--primary-dark); font-size: 14px;">{{ number_format($totalVentesMontant + $totalRetourMontant, 3, '.', '') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Chiffre d'Affaires / Familles -->
            <div class="content-card" style="border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--border); overflow: hidden;">
                <div style="background: #fdfdfe; padding: 16px 20px; font-weight: 700; text-transform: uppercase; border-bottom: 1px solid var(--border); font-size: 12px; color: var(--text-secondary); letter-spacing: 0.5px; text-align: left; display: flex; align-items: center; gap: 10px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="9"></rect>
                        <rect x="14" y="3" width="7" height="5"></rect>
                        <rect x="14" y="12" width="7" height="9"></rect>
                        <rect x="3" y="16" width="7" height="5"></rect>
                    </svg>
                    Chiffre d'affaires / Sous Familles
                </div>
                <div class="table-responsive" style="min-height: auto;">
                    <table class="data-table compact-table">
                        <thead>
                            <tr>
                                <th style="padding: 10px 12px;">Code</th>
                                <th style="padding: 10px 12px;">Sous Famille</th>
                                <th style="padding: 10px 12px;" class="text-right">P.U TTC</th>
                                <th style="padding: 10px 12px;" class="text-center">Qtés</th>
                                <th style="padding: 10px 12px;" class="text-right">Remise</th>
                                <th style="padding: 10px 12px;" class="text-right">Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($caSousFamilles as $sf)
                            <tr>
                                <td style="padding: 8px 12px;">{{ $sf->code }}</td>
                                <td style="padding: 8px 12px; font-weight: 600;">{{ $sf->libelle }}</td>
                                <td class="amount-cell" style="padding: 8px 12px;">{{ number_format($sf->pv_u_ttc, 3, '.', '') }}</td>
                                <td class="text-center font-bold" style="padding: 8px 12px;">{{ round($sf->qtes) }}</td>
                                <td class="amount-cell text-danger" style="padding: 8px 12px;">{{ number_format($sf->remise, 3, '.', '') }}</td>
                                <td class="amount-cell font-bold" style="padding: 8px 12px;">{{ number_format($sf->montant, 3, '.', '') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="empty-state text-center" style="padding: 24px; color: var(--text-muted);">Aucune donnée à afficher.</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr style="background: var(--background);">
                                <td colspan="3" class="totals-label" style="padding: 8px 12px; font-weight: 700;">Ventes</td>
                                <td class="text-center font-bold" style="padding: 8px 12px;">{{ $caSousFamilles->sum('qtes') }}</td>
                                <td class="amount-cell text-muted" style="padding: 8px 12px;">0.000</td>
                                <td class="amount-cell font-bold" style="padding: 8px 12px; color: var(--primary);">{{ number_format($caSousFamilles->sum('montant'), 3, '.', '') }}</td>
                            </tr>
                            <tr style="background: var(--background);">
                                <td colspan="3" class="totals-label" style="padding: 8px 12px; font-weight: 700; color: var(--danger);">Retour</td>
                                <td class="text-center font-bold text-danger" style="padding: 8px 12px;">0</td>
                                <td class="amount-cell text-muted" style="padding: 8px 12px;">0.000</td>
                                <td class="amount-cell font-bold text-danger" style="padding: 8px 12px;">0.000</td>
                            </tr>
                            <tr class="table-totals" style="background: var(--primary-light);">
                                <td colspan="3" class="totals-label" style="padding: 10px 12px; font-weight: 800; color: var(--primary-dark);">Total Familles</td>
                                <td class="text-center font-bold" style="padding: 10px 12px; color: var(--primary-dark);">{{ $caSousFamilles->sum('qtes') }}</td>
                                <td class="amount-cell text-muted" style="padding: 10px 12px;">0.000</td>
                                <td class="amount-cell font-bold" style="padding: 10px 12px; color: var(--primary-dark); font-size: 14px;">{{ number_format($caSousFamilles->sum('montant'), 3, '.', '') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Section 2: Blocs financiers de bas de page (4 Colonnes) -->
        <div class="grid-section-4" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; border-top: 1px solid var(--border); padding-top: 24px;">
            
            <!-- Caisse -->
            <div class="content-card" style="border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--border); overflow: hidden;">
                <div style="background: #fdfdfe; padding: 12px 16px; font-weight: 700; text-transform: uppercase; border-bottom: 1px solid var(--border); font-size: 11px; color: var(--text-secondary); letter-spacing: 0.5px; text-align: left; display: flex; align-items: center; gap: 8px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="4" width="20" height="16" rx="2" ry="2"></rect>
                        <line x1="12" y1="4" x2="12" y2="20"></line>
                    </svg>
                    Synthèse Caisse
                </div>
                <table class="data-table compact-table borderless-rows" style="font-size: 11px;">
                    <tbody>
                        <tr>
                            <td style="padding: 8px 12px; font-weight: 500;">VENTES REGLEES</td>
                            <td class="amount-cell font-bold" style="padding: 8px 12px;">{{ number_format($caisseTotaux['ventes_reglees'], 3, '.', '') }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 12px; font-weight: 500;">+ACOMPTES ( N.V )</td>
                            <td class="amount-cell" style="padding: 8px 12px;">{{ number_format($caisseTotaux['acomptes_nv'], 3, '.', '') }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 12px; font-weight: 500;">+ACOMPTES ( A.V )</td>
                            <td class="amount-cell" style="padding: 8px 12px;">{{ number_format($caisseTotaux['acomptes_av'], 3, '.', '') }}</td>
                        </tr>
                        <tr style="background: var(--info-bg); font-weight: 700;">
                            <td style="padding: 10px 12px; color: #0369a1;">RECETTE BRUTE</td>
                            <td class="amount-cell" style="padding: 10px 12px; color: #0369a1; font-size: 12px;">{{ number_format($caisseTotaux['recette_brute'], 3, '.', '') }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 12px; font-weight: 500; color: var(--text-muted);">- DEPENSES DIVERS</td>
                            <td class="amount-cell text-danger" style="padding: 8px 12px;">{{ number_format($caisseTotaux['depenses_divers'], 3, '.', '') }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 12px; font-weight: 500; color: var(--text-muted);">- ACOMPTES PERS.</td>
                            <td class="amount-cell text-danger" style="padding: 8px 12px;">{{ number_format($caisseTotaux['acomptes_personnels'], 3, '.', '') }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 12px; font-weight: 500; color: var(--text-muted);">- COMMISSIONS</td>
                            <td class="amount-cell text-danger" style="padding: 8px 12px;">{{ number_format($caisseTotaux['commissions'], 3, '.', '') }}</td>
                        </tr>
                        <tr style="background: var(--success-bg); font-weight: 800;">
                            <td style="padding: 10px 12px; color: #047857;">RECETTE NETTE</td>
                            <td class="amount-cell" style="padding: 10px 12px; color: #047857; font-size: 12px;">{{ number_format($caisseTotaux['recette_nette'], 3, '.', '') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Détails Recettes -->
            <div class="content-card" style="border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--border); overflow: hidden;">
                <div style="background: #fdfdfe; padding: 12px 16px; font-weight: 700; text-transform: uppercase; border-bottom: 1px solid var(--border); font-size: 11px; color: var(--text-secondary); letter-spacing: 0.5px; text-align: left; display: flex; align-items: center; gap: 8px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                        <line x1="1" y1="10" x2="23" y2="10"></line>
                    </svg>
                    Détails Règlements
                </div>
                <table class="data-table compact-table borderless-rows" style="font-size: 11px;">
                    <tbody>
                        <tr>
                            <td style="padding: 6px 12px; font-weight: 500;">Espèce</td>
                            <td class="amount-cell font-bold" style="padding: 6px 12px;">{{ number_format($recettes['espece'], 3, '.', '') }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 6px 12px; font-weight: 500; color: var(--text-muted);">- Dépense</td>
                            <td class="amount-cell text-danger" style="padding: 6px 12px;">{{ number_format($recettes['depense'], 3, '.', '') }}</td>
                        </tr>
                        <tr style="background: #fafafa;">
                            <td style="padding: 6px 12px; font-weight: 600; padding-left: 20px;">Espèce Net</td>
                            <td class="amount-cell font-bold" style="padding: 6px 12px;">{{ number_format($recettes['espece_net'], 3, '.', '') }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 6px 12px; font-weight: 500;">Chèque</td>
                            <td class="amount-cell" style="padding: 6px 12px;">{{ number_format($recettes['cheque'], 3, '.', '') }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 6px 12px; font-weight: 500;">Carte Crédit</td>
                            <td class="amount-cell" style="padding: 6px 12px;">{{ number_format($recettes['carte_credit'], 3, '.', '') }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 6px 12px; font-weight: 500;">Bon D'Achats</td>
                            <td class="amount-cell" style="padding: 6px 12px;">{{ number_format($recettes['bon_achats'], 3, '.', '') }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 6px 12px; font-weight: 500;">Chèque Cadeau</td>
                            <td class="amount-cell" style="padding: 6px 12px;">{{ number_format($recettes['cheque_cadeau'], 3, '.', '') }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 6px 12px; font-weight: 500;">Autres</td>
                            <td class="amount-cell" style="padding: 6px 12px;">{{ number_format($recettes['autres'], 3, '.', '') }}</td>
                        </tr>
                        <tr class="table-totals" style="background: var(--background); font-weight: 700;">
                            <td style="padding: 8px 12px; border-top: 1px solid var(--border);">Total Recette</td>
                            <td class="amount-cell" style="padding: 8px 12px; border-top: 1px solid var(--border);">{{ number_format($totalRecettesDetails, 3, '.', '') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Autres Informations -->
            <div class="content-card" style="border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--border); overflow: hidden;">
                <div style="background: #fdfdfe; padding: 12px 16px; font-weight: 700; text-transform: uppercase; border-bottom: 1px solid var(--border); font-size: 11px; color: var(--text-secondary); letter-spacing: 0.5px; text-align: left; display: flex; align-items: center; gap: 8px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                    </svg>
                    Autres Informations
                </div>
                <table class="data-table compact-table borderless-rows" style="font-size: 11px;">
                    <tbody>
                        <tr>
                            <td style="padding: 8px 12px; font-weight: 500;">Crédit</td>
                            <td class="amount-cell text-muted" style="padding: 8px 12px;">{{ number_format($journalCaisse->totalcredit ?? 0, 3, '.', '') }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 12px; font-weight: 500;">Crédit Acompte</td>
                            <td class="amount-cell text-muted" style="padding: 8px 12px;">{{ number_format($journalCaisse->totalcreditacompte ?? 0, 3, '.', '') }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 12px; font-weight: 500;">Coupon</td>
                            <td class="amount-cell text-muted" style="padding: 8px 12px;">{{ number_format($journalCaisse->totalcoupon ?? 0, 3, '.', '') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Chiffre d'Affaires / Vendeur -->
            <div class="content-card" style="border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid var(--border); overflow: hidden;">
                <div style="background: #fdfdfe; padding: 12px 16px; font-weight: 700; text-transform: uppercase; border-bottom: 1px solid var(--border); font-size: 11px; color: var(--text-secondary); letter-spacing: 0.5px; text-align: left; display: flex; align-items: center; gap: 8px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    Ventes par Vendeur
                </div>
                <table class="data-table compact-table borderless-rows" style="font-size: 11px;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--border); background: var(--background);">
                            <th style="padding: 6px 12px;">Vendeur</th>
                            <th class="text-center" style="padding: 6px 8px;">Qté</th>
                            <th class="text-right" style="padding: 6px 12px;">Montant</th>
                            <th class="text-right" style="padding: 6px 8px;">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($caVendeur as $vendeur)
                        @php
                            $percentage = $totalVendeurMontant > 0 ? ($vendeur->montant / $totalVendeurMontant) * 100 : 0;
                        @endphp
                        <tr>
                            <td style="padding: 6px 12px; font-weight: 600;">{{ $vendeur->nom }} {{ $vendeur->prenom }}</td>
                            <td class="text-center" style="padding: 6px 8px;">{{ round($vendeur->qte) }}</td>
                            <td class="amount-cell" style="padding: 6px 12px;">{{ number_format($vendeur->montant, 3, '.', '') }}</td>
                            <td class="text-right font-medium" style="padding: 6px 8px; color: var(--primary);">{{ number_format($percentage, 0) }}%</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="empty-state text-center" style="padding: 16px; color: var(--text-muted);">Aucun vendeur.</td>
                        </tr>
                        @endforelse
                        <tr class="table-totals" style="background: var(--background); font-weight: 700;">
                            <td style="padding: 8px 12px; border-top: 1px solid var(--border);">Total</td>
                            <td class="text-center" style="padding: 8px 8px; border-top: 1px solid var(--border);">{{ $caVendeur->sum('qte') }}</td>
                            <td class="amount-cell" style="padding: 8px 12px; border-top: 1px solid var(--border);">{{ number_format($totalVendeurMontant, 3, '.', '') }}</td>
                            <td class="text-right" style="padding: 8px 8px; border-top: 1px solid var(--border);">100%</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>

    </div>
</div>

<!-- MODAL FILTRE JOURNÉES CLÔTURES (no-print) -->
<div id="filterModal" class="modal-backdrop no-print" style="display: none; justify-content: center; align-items: flex-start; padding-top: 60px;">
    <div class="modal-content" style="max-width: 840px; width: 100%; border-radius: var(--radius-lg); transform: scale(1); padding: 0;">
        
        <!-- Header modal -->
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 20px 24px; border-bottom: 1px solid var(--border);">
            <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--text-main); display: flex; align-items: center; gap: 8px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                </svg>
                Filtre des journées de clôture
            </h3>
            <button onclick="document.getElementById('filterModal').style.display='none'" class="modal-close" style="top: 16px; right: 20px;">&times;</button>
        </div>
        
        <!-- Filtre section -->
        <div style="padding: 20px 24px; border-bottom: 1px solid var(--border); background: var(--background);">
            <div style="display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 12px; flex: 1; min-width: 300px;">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label" style="font-weight: 600;">Date Début</label>
                        <input type="date" id="filterDu" class="form-control" style="height: 38px;">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label" style="font-weight: 600;">Date Fin</label>
                        <input type="date" id="filterAu" class="form-control" style="height: 38px;">
                    </div>
                </div>
                <button onclick="filterJournees()" class="btn btn-primary" style="height: 38px; padding: 0 24px; font-weight: 600;">
                    Filtrer
                </button>
            </div>
        </div>

        <!-- Recherche rapide textuelle -->
        <div style="padding: 12px 24px; display: flex; justify-content: flex-end; border-bottom: 1px solid var(--border);">
            <div class="search-wrapper" style="width: 260px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="search-icon">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" id="filterSearch" class="search-input" placeholder="Recherche textuelle..." oninput="filterTableSearch()" style="height: 34px; padding-left: 36px; font-size: 13px;">
            </div>
        </div>

        <!-- Table des résultats -->
        <div style="max-height: 40vh; overflow-y: auto; padding: 0;">
            <table class="data-table" id="filterResultsTable" style="font-size: 13px;">
                <thead>
                    <tr style="position: sticky; top: 0; background: white; z-index: 100;">
                        <th style="padding: 12px 20px;">N° Session</th>
                        <th style="padding: 12px 20px;">Date d'Ouverture</th>
                        <th style="padding: 12px 20px;">Date de Clôture</th>
                        <th class="text-right" style="padding: 12px 20px; width: 150px;">Montant Théorique</th>
                        <th class="text-right" style="padding: 12px 20px; width: 150px;">Montant Clôture</th>
                        <th style="width: 60px;"></th>
                    </tr>
                </thead>
                <tbody id="filterResultsBody">
                    <tr>
                        <td colspan="6" class="empty-state text-center" style="padding: 40px; font-style: italic;">
                            Cliquez sur le bouton "Filtrer" ci-dessus pour lancer la recherche.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Footer Modal -->
        <div style="padding: 14px 24px; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: var(--background);">
            <div id="filterPagination" style="display: flex; gap: 4px;"></div>
            <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--text-muted); font-weight: 500;">
                Taille de page : 
                <select id="filterPageSize" class="form-control" style="width: 60px; height: 28px; padding: 0 6px; font-size: 12px; border-radius: 4px;">
                    <option value="60" selected>60</option>
                </select>
            </div>
        </div>
    </div>
</div>

<style>
    /* Spécificités d'impression */
    @media print {
        body {
            background: white !important;
            color: #0f172a !important;
        }
        .no-print {
            display: none !important;
        }
        .sidebar {
            display: none !important;
        }
        .topbar {
            display: none !important;
        }
        .main-content {
            margin: 0 !important;
            padding: 0 !important;
        }
        .printable-area {
            padding: 0 !important;
            max-width: 100% !important;
        }
        .etat-container {
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
        }
        .print-only {
            display: block !important;
        }
        
        /* Grid resets for optimal A4 paper layout (no columns side-by-side) */
        .grid-section {
            display: block !important;
        }
        .grid-section > div {
            margin-bottom: 30px !important;
            page-break-inside: avoid;
            box-shadow: none !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
        }
        .grid-section-4 {
            display: grid !important;
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 20px !important;
        }
        .grid-section-4 > div {
            page-break-inside: avoid;
            box-shadow: none !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
        }
        .data-table thead th {
            background: #f1f5f9 !important;
            color: #0f172a !important;
            border-bottom: 2px solid #334155 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .table-totals, .table-totals td {
            background: #e2e8f0 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .compact-table th, .compact-table td {
            padding: 6px 8px !important;
            font-size: 10.5px !important;
        }
    }

    /* Utilitaires tableau compact */
    .compact-table th, .compact-table td {
        padding: 8px 12px !important;
        font-size: 11.5px !important;
    }
    
    .borderless-rows td {
        border-bottom: 1px solid rgba(226, 232, 240, 0.5) !important;
    }
</style>
@endsection

@section('scripts')
<script>
    // Initialiser les dates par défaut (10 jours avant à aujourd'hui)
    document.addEventListener('DOMContentLoaded', function() {
        const today = new Date();
        const tenDaysAgo = new Date(today);
        tenDaysAgo.setDate(today.getDate() - 10);
        
        document.getElementById('filterDu').value = tenDaysAgo.toISOString().split('T')[0];
        document.getElementById('filterAu').value = today.toISOString().split('T')[0];
    });

    function openFilterModal() {
        const modal = document.getElementById('filterModal');
        modal.style.display = 'flex';
        // Force reflow
        void modal.offsetHeight;
        modal.classList.add('show');
    }

    function closeFilterModal() {
        const modal = document.getElementById('filterModal');
        modal.classList.remove('show');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }

    function filterJournees() {
        const du = document.getElementById('filterDu').value;
        const au = document.getElementById('filterAu').value;
        const tbody = document.getElementById('filterResultsBody');
        
        tbody.innerHTML = '<tr><td colspan="6" class="text-center" style="padding: 40px; color: var(--text-muted);"><div style="display: inline-block; width: 22px; height: 22px; border: 2.5px solid var(--border); border-top-color: var(--primary); border-radius: 50%; animation: spin 0.6s linear infinite;"></div> Chargement des sessions...</td></tr>';

        fetch(`{{ route('vente.journee.etat.filter') }}?du=${du}&au=${au}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="empty-state text-center" style="padding: 40px; font-style: italic;">Aucune session trouvée pour cette période.</td></tr>';
                return;
            }

            let html = '';
            data.forEach(s => {
                const dateOuv = s.dateouverture ? new Date(s.dateouverture).toLocaleString('fr-FR', {day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit'}) : '';
                const dateClot = s.datecloture ? new Date(s.datecloture).toLocaleString('fr-FR', {day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit'}) : '';
                const montantTheo = parseFloat(s.montanttheorique || 0).toFixed(3);
                const montantClot = parseFloat(s.montantcloture || 0).toFixed(3);
                
                html += `<tr class="filter-row-item" style="cursor: pointer;" onclick="selectSession(${s.journalcaisseid})">
                    <td style="padding: 10px 20px; font-weight: 600;">N° ${s.journalcaissenumero || s.journalcaisseid}</td>
                    <td style="padding: 10px 20px; color: var(--primary); font-weight: 500;">${dateOuv}</td>
                    <td style="padding: 10px 20px;">${dateClot || '<span class="modern-badge badge-danger" style="padding: 2px 8px; font-size: 10px;"><span class="badge-dot"></span>Active</span>'}</td>
                    <td class="amount-cell" style="padding: 10px 20px;">${montantTheo}</td>
                    <td class="amount-cell" style="padding: 10px 20px; font-weight: 700;">${montantClot}</td>
                    <td class="text-center" style="padding: 10px 20px;">
                        <button class="btn btn-outline" style="padding: 4px 8px; font-size: 11px; font-weight: 600;" onclick="event.stopPropagation(); selectSession(${s.journalcaisseid})">
                            Choisir
                        </button>
                    </td>
                </tr>`;
            });
            tbody.innerHTML = html;

            // Mise à jour pagination
            const pag = document.getElementById('filterPagination');
            pag.innerHTML = `<span style="display: inline-flex; align-items: center; justify-content: center; min-width: 28px; height: 28px; padding: 0 8px; background: var(--primary); color: white; border-radius: var(--radius-sm); font-size: 12px; font-weight: 700;">Page 1</span>`;
        })
        .catch(err => {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center" style="padding: 40px; color: var(--danger); font-weight: 600;">Erreur lors du chargement: ${err.message}</td></tr>`;
        });
    }

    function selectSession(journalcaisseid) {
        window.location.href = `{{ route('vente.journee.etat') }}?journalcaisseid=${journalcaisseid}`;
    }

    function filterTableSearch() {
        const search = document.getElementById('filterSearch').value.toLowerCase();
        const rows = document.querySelectorAll('#filterResultsBody tr.filter-row-item');
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(search) ? '' : 'none';
        });
    }

    // Fermer modal en cliquant sur le fond
    document.getElementById('filterModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeFilterModal();
        }
    });
</script>
<style>
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
</style>
@endsection
