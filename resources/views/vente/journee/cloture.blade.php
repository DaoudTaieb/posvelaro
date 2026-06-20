@extends('layouts.app')
@section('title', 'Clôture Journée')

@section('content')
<div class="main-content-inner" style="max-width: 800px; margin: 40px auto;">
    
    @if(session('success'))
    <div style="background: var(--success-bg); border: 1px solid var(--success-border); color: var(--success); padding: 16px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
        </svg>
        <span style="font-weight: 500;">{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div style="background: var(--danger-bg); border: 1px solid var(--danger-border); color: var(--danger); padding: 16px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
        <span style="font-weight: 500;">{{ session('error') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div style="background: var(--danger-bg); border: 1px solid var(--danger-border); color: var(--danger); padding: 16px; border-radius: 8px; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; gap: 12px; font-weight: 600; margin-bottom: 8px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            Veuillez corriger les erreurs suivantes :
        </div>
        <ul style="margin: 0; padding-left: 36px; color: var(--danger); font-size: 14px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div style="background: white; border: 1px solid var(--border); border-radius: 12px; box-shadow: var(--shadow-sm); overflow: hidden;">
        
        {{-- En-tête --}}
        <div style="padding: 20px 24px; border-bottom: 1px solid var(--border); background: #f8fafc; display: flex; justify-content: space-between; align-items: center;">
            <h2 style="font-size: 18px; font-weight: 700; color: var(--text); display: flex; align-items: center; gap: 10px; margin: 0;">
                <div style="padding: 6px; background: #fef3c7; color: #d97706; border-radius: 6px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                </div>
                Fermeture de la journée de vente
            </h2>
            <div style="font-size: 12px; color: var(--text-secondary); text-align: right;">
                <div style="font-weight: 600;">{{ $caisse->libelle ?? 'Caisse' }}</div>
                <div>N° {{ $journalCaisse->journalcaissenumero }}</div>
                <div>Ouverte le {{ \Carbon\Carbon::parse($journalCaisse->dateouverture)->format('d/m/Y H:i') }}</div>
            </div>
        </div>

        {{-- Formulaire --}}
        <form action="{{ route('vente.journee.cloture.store') }}" method="POST" id="clotureForm">
            @csrf
            <input type="hidden" name="journalcaisseid" value="{{ $journalCaisse->journalcaisseid }}">

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                    <thead>
                        <tr style="background: #f1f5f9; border-bottom: 2px solid var(--border);">
                            <th style="text-align: left; padding: 12px 20px; font-weight: 600; color: var(--text);">Mode de paiement</th>
                            <th style="text-align: right; padding: 12px 20px; font-weight: 600; color: var(--text); width: 140px;">Total Théorique</th>
                            <th style="text-align: right; padding: 12px 20px; font-weight: 600; color: var(--text); width: 200px;">Total Compté</th>
                            <th style="text-align: center; padding: 12px 10px; width: 40px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Fond de Caisse --}}
                        <tr style="border-bottom: 1px solid var(--border); background: #fefce8;">
                            <td style="padding: 12px 20px; font-weight: 600; color: var(--text);">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="12" y1="1" x2="12" y2="23"></line>
                                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                    </svg>
                                    Fond de Caisse
                                </div>
                            </td>
                            <td style="text-align: right; padding: 12px 20px; font-family: 'JetBrains Mono', monospace; font-weight: 600; color: #d97706;">
                                {{ number_format($theoriques['fondcaisse'], 3, '.', ' ') }}
                            </td>
                            <td style="text-align: right; padding: 12px 20px; font-family: 'JetBrains Mono', monospace; color: var(--text-secondary);">
                                {{ number_format($theoriques['fondcaisse'], 3, '.', ' ') }}
                            </td>
                            <td></td>
                        </tr>

                        {{-- Espèce --}}
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 12px 20px; font-weight: 500; color: var(--text);">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    Espèce
                                </div>
                            </td>
                            <td style="text-align: right; padding: 12px 20px; font-family: 'JetBrains Mono', monospace; color: var(--text);">
                                {{ number_format($theoriques['espece'], 3, '.', ' ') }}
                            </td>
                            <td style="padding: 8px 12px;">
                                <input type="number" step="0.001" min="0" name="totalespecephys" id="totalespecephys"
                                       class="cloture-input" value=""
                                       placeholder="0.000"
                                       style="width: 100%; text-align: right; border: 1px solid var(--border); border-radius: 6px; padding: 8px 12px; font-size: 14px; font-family: 'JetBrains Mono', monospace; outline: none; transition: all 0.2s;">
                            </td>
                            <td style="text-align: center; padding: 8px 4px;">
                                <button type="button" class="copy-btn" onclick="copyTheorique(this, 'totalespecephys', {{ $theoriques['espece'] }})" title="Copier la valeur théorique">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="12" y1="5" x2="12" y2="19"></line>
                                        <polyline points="19 12 12 19 5 12"></polyline>
                                    </svg>
                                </button>
                            </td>
                        </tr>

                        {{-- Chèque --}}
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 12px 20px; font-weight: 500; color: var(--text);">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                    </svg>
                                    Chèque
                                </div>
                            </td>
                            <td style="text-align: right; padding: 12px 20px; font-family: 'JetBrains Mono', monospace; color: var(--text);">
                                {{ number_format($theoriques['cheque'], 3, '.', ' ') }}
                            </td>
                            <td style="padding: 8px 12px;">
                                <input type="number" step="0.001" min="0" name="totalchequephys" id="totalchequephys"
                                       class="cloture-input" value=""
                                       placeholder="0.000"
                                       style="width: 100%; text-align: right; border: 1px solid var(--border); border-radius: 6px; padding: 8px 12px; font-size: 14px; font-family: 'JetBrains Mono', monospace; outline: none; transition: all 0.2s;">
                            </td>
                            <td style="text-align: center; padding: 8px 4px;">
                                <button type="button" class="copy-btn" onclick="copyTheorique(this, 'totalchequephys', {{ $theoriques['cheque'] }})" title="Copier la valeur théorique">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="12" y1="5" x2="12" y2="19"></line>
                                        <polyline points="19 12 12 19 5 12"></polyline>
                                    </svg>
                                </button>
                            </td>
                        </tr>

                        {{-- Carte Bancaire --}}
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 12px 20px; font-weight: 500; color: var(--text);">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                                        <line x1="1" y1="10" x2="23" y2="10"></line>
                                    </svg>
                                    Carte Bancaire
                                </div>
                            </td>
                            <td style="text-align: right; padding: 12px 20px; font-family: 'JetBrains Mono', monospace; color: var(--text);">
                                {{ number_format($theoriques['carte_bancaire'], 3, '.', ' ') }}
                            </td>
                            <td style="padding: 8px 12px;">
                                <input type="number" step="0.001" min="0" name="totaltpephys" id="totaltpephys"
                                       class="cloture-input" value=""
                                       placeholder="0.000"
                                       style="width: 100%; text-align: right; border: 1px solid var(--border); border-radius: 6px; padding: 8px 12px; font-size: 14px; font-family: 'JetBrains Mono', monospace; outline: none; transition: all 0.2s;">
                            </td>
                            <td style="text-align: center; padding: 8px 4px;">
                                <button type="button" class="copy-btn" onclick="copyTheorique(this, 'totaltpephys', {{ $theoriques['carte_bancaire'] }})" title="Copier la valeur théorique">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="12" y1="5" x2="12" y2="19"></line>
                                        <polyline points="19 12 12 19 5 12"></polyline>
                                    </svg>
                                </button>
                            </td>
                        </tr>

                        {{-- Chèque Cadeaux --}}
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 12px 20px; font-weight: 500; color: var(--text);">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 12 20 22 4 22 4 12"></polyline>
                                        <rect x="2" y="7" width="20" height="5"></rect>
                                        <line x1="12" y1="22" x2="12" y2="7"></line>
                                        <path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path>
                                        <path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path>
                                    </svg>
                                    Chèque Cadeaux
                                </div>
                            </td>
                            <td style="text-align: right; padding: 12px 20px; font-family: 'JetBrains Mono', monospace; color: var(--text);">
                                {{ number_format($theoriques['cheque_cadeaux'], 3, '.', ' ') }}
                            </td>
                            <td style="padding: 8px 12px;">
                                <input type="number" step="0.001" min="0" name="totalcontrebonphys" id="totalcontrebonphys"
                                       class="cloture-input" value=""
                                       placeholder="0.000"
                                       style="width: 100%; text-align: right; border: 1px solid var(--border); border-radius: 6px; padding: 8px 12px; font-size: 14px; font-family: 'JetBrains Mono', monospace; outline: none; transition: all 0.2s;">
                            </td>
                            <td style="text-align: center; padding: 8px 4px;">
                                <button type="button" class="copy-btn" onclick="copyTheorique(this, 'totalcontrebonphys', {{ $theoriques['cheque_cadeaux'] }})" title="Copier la valeur théorique">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="12" y1="5" x2="12" y2="19"></line>
                                        <polyline points="19 12 12 19 5 12"></polyline>
                                    </svg>
                                </button>
                            </td>
                        </tr>

                        {{-- Bon de convention --}}
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 12px 20px; font-weight: 500; color: var(--text);">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                                        <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                                    </svg>
                                    Bon de convention
                                </div>
                            </td>
                            <td style="text-align: right; padding: 12px 20px; font-family: 'JetBrains Mono', monospace; color: var(--text);">
                                {{ number_format($theoriques['bon_convention'], 3, '.', ' ') }}
                            </td>
                            <td style="padding: 8px 12px;">
                                <input type="number" step="0.001" min="0" name="totalbonconventionphys" id="totalbonconventionphys"
                                       class="cloture-input" value=""
                                       placeholder="0.000"
                                       style="width: 100%; text-align: right; border: 1px solid var(--border); border-radius: 6px; padding: 8px 12px; font-size: 14px; font-family: 'JetBrains Mono', monospace; outline: none; transition: all 0.2s;">
                            </td>
                            <td style="text-align: center; padding: 8px 4px;">
                                <button type="button" class="copy-btn" onclick="copyTheorique(this, 'totalbonconventionphys', {{ $theoriques['bon_convention'] }})" title="Copier la valeur théorique">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="12" y1="5" x2="12" y2="19"></line>
                                        <polyline points="19 12 12 19 5 12"></polyline>
                                    </svg>
                                </button>
                            </td>
                        </tr>

                        {{-- Avoir --}}
                        <tr style="border-bottom: 1px solid var(--border);">
                            <td style="padding: 12px 20px; font-weight: 500; color: var(--text);">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="23 4 23 10 17 10"></polyline>
                                        <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                                    </svg>
                                    Avoir
                                </div>
                            </td>
                            <td style="text-align: right; padding: 12px 20px; font-family: 'JetBrains Mono', monospace; color: var(--text);">
                                {{ number_format($theoriques['avoir'], 3, '.', ' ') }}
                            </td>
                            <td style="padding: 8px 12px;">
                                <input type="number" step="0.001" min="0" name="totalregavoirphys" id="totalregavoirphys"
                                       class="cloture-input" value=""
                                       placeholder="0.000"
                                       style="width: 100%; text-align: right; border: 1px solid var(--border); border-radius: 6px; padding: 8px 12px; font-size: 14px; font-family: 'JetBrains Mono', monospace; outline: none; transition: all 0.2s;">
                            </td>
                            <td style="text-align: center; padding: 8px 4px;">
                                <button type="button" class="copy-btn" onclick="copyTheorique(this, 'totalregavoirphys', {{ $theoriques['avoir'] }})" title="Copier la valeur théorique">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="12" y1="5" x2="12" y2="19"></line>
                                        <polyline points="19 12 12 19 5 12"></polyline>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Résumé en bas --}}
            <div style="padding: 16px 24px; border-top: 2px solid var(--border); background: #f8fafc; display: flex; justify-content: space-between; align-items: center;">
                <div style="font-size: 15px; font-weight: 700; color: var(--text); display: flex; align-items: center; gap: 8px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    Fermeture de la journée de vente
                </div>
                <button type="submit" id="submitCloture" onclick="return confirm('Êtes-vous sûr de vouloir clôturer cette journée de vente ?')" style="background: var(--primary); color: white; border: none; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    Clôturer la journée
                </button>
            </div>
        </form>
    </div>

    {{-- Info résumé de la session --}}
    @if($ticketsTotals && $ticketsTotals->nbreticket > 0)
    <div style="margin-top: 20px; background: white; border: 1px solid var(--border); border-radius: 12px; box-shadow: var(--shadow-sm); overflow: hidden;">
        <div style="padding: 16px 24px; border-bottom: 1px solid var(--border); background: #f0f9ff;">
            <h3 style="font-size: 14px; font-weight: 600; color: #0369a1; margin: 0; display: flex; align-items: center; gap: 8px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="16" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                </svg>
                Résumé de la session
            </h3>
        </div>
        <div style="padding: 16px 24px; display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px;">
            <div>
                <div style="font-size: 11px; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px;">Nombre de tickets</div>
                <div style="font-size: 20px; font-weight: 700; color: var(--text); margin-top: 4px;">{{ (int) $ticketsTotals->nbreticket }}</div>
            </div>
            <div>
                <div style="font-size: 11px; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px;">Recette brute</div>
                <div style="font-size: 20px; font-weight: 700; color: var(--text); margin-top: 4px; font-family: 'JetBrains Mono', monospace;">{{ number_format($ticketsTotals->recettebrut ?? 0, 3, '.', ' ') }}</div>
            </div>
            <div>
                <div style="font-size: 11px; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px;">Quantité vendue</div>
                <div style="font-size: 20px; font-weight: 700; color: var(--text); margin-top: 4px;">{{ number_format($ticketsTotals->totalqtevente ?? 0, 0) }}</div>
            </div>
            <div>
                <div style="font-size: 11px; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px;">Total remises</div>
                <div style="font-size: 20px; font-weight: 700; color: #dc2626; margin-top: 4px; font-family: 'JetBrains Mono', monospace;">{{ number_format($ticketsTotals->vtotalremise ?? 0, 3, '.', ' ') }}</div>
            </div>
        </div>
    </div>
    @endif
</div>

<style>
    .cloture-input:focus {
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 3px var(--primary-light) !important;
    }

    .copy-btn {
        background: none;
        border: 1px solid var(--border);
        border-radius: 6px;
        padding: 6px;
        cursor: pointer;
        color: var(--text-secondary);
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .copy-btn:hover {
        background: var(--primary-light);
        color: var(--primary);
        border-color: var(--primary);
    }

    .copy-btn.copied {
        background: #dcfce7;
        color: #16a34a;
        border-color: #16a34a;
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
        setTimeout(() => btn.classList.remove('copied'), 800);
    }
</script>
@endsection
