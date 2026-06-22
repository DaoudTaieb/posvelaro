@extends('layouts.app')
@section('title', 'Consultation Journées')

@section('content')
<div class="pos-container">
    
    <!-- Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Consultation des Journées</h1>
            <p class="page-subtitle">Suivi des sessions de caisse, fonds de caisse, recettes physiques et électroniques.</p>
        </div>
        <div class="header-actions">
            <button class="btn btn-outline" onclick="window.location.reload()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="23 4 23 10 17 10"></polyline>
                    <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                </svg>
                Actualiser
            </button>
        </div>
    </div>

    <!-- KPI Summary Grid -->
    @php
        $totalSessions = count($journees);
        $totalRecetteNette = $journees->sum('recettenet');
        $totalEspecesNet = $journees->sum('totalespecenet');
        $totalTPE = $journees->sum('totaltpe');
    @endphp
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon-wrapper bg-indigo-light">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
            </div>
            <div class="kpi-info">
                <span class="kpi-label">Total Sessions</span>
                <span class="kpi-value">{{ $totalSessions }}</span>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon-wrapper bg-green-light">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="1" x2="12" y2="23"></line>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
            </div>
            <div class="kpi-info">
                <span class="kpi-label">Recette Nette</span>
                <span class="kpi-value text-success">{{ number_format($totalRecetteNette, 3, '.', ' ') }} <span style="font-size: 14px; font-weight: 500;">TND</span></span>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon-wrapper bg-blue-light">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="6" width="20" height="12" rx="2" ry="2"></rect>
                    <circle cx="12" cy="12" r="2"></circle>
                    <line x1="6" y1="12" x2="6.01" y2="12"></line>
                    <line x1="18" y1="12" x2="18.01" y2="12"></line>
                </svg>
            </div>
            <div class="kpi-info">
                <span class="kpi-label">Espèces Net</span>
                <span class="kpi-value text-primary">{{ number_format($totalEspecesNet, 3, '.', ' ') }} <span style="font-size: 14px; font-weight: 500;">TND</span></span>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon-wrapper bg-red-light">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--danger)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                    <line x1="1" y1="10" x2="23" y2="10"></line>
                </svg>
            </div>
            <div class="kpi-info">
                <span class="kpi-label">Recettes TPE</span>
                <span class="kpi-value text-danger">{{ number_format($totalTPE, 3, '.', ' ') }} <span style="font-size: 14px; font-weight: 500;">TND</span></span>
            </div>
        </div>
    </div>

    <!-- Main Content Card -->
    <div class="content-card">
        
        <!-- Toolbar / Filters -->
        <div class="toolbar">
            <form method="GET" action="{{ route('vente.journee.index') }}" style="display: flex; gap: 16px; align-items: center; flex-wrap: wrap; width: 100%;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <label class="form-label" style="margin: 0; white-space: nowrap; text-transform: uppercase; font-size: 11px; font-weight: 600; color: var(--text-secondary);">Du</label>
                    <input type="date" name="date_du" class="form-control" value="{{ $dateDu }}" style="width: auto; padding: 6px 12px; height: 38px;">
                </div>
                
                <div style="display: flex; align-items: center; gap: 8px;">
                    <label class="form-label" style="margin: 0; white-space: nowrap; text-transform: uppercase; font-size: 11px; font-weight: 600; color: var(--text-secondary);">Au</label>
                    <input type="date" name="date_au" class="form-control" value="{{ $dateAu }}" style="width: auto; padding: 6px 12px; height: 38px;">
                </div>

                <button type="submit" class="btn btn-primary" style="height: 38px; padding: 0 16px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                    </svg>
                    Filtrer
                </button>
            </form>
        </div>

        <!-- Table Grid -->
        <div class="table-responsive" style="min-height: auto;">
            <table class="data-table" id="journeesTable" style="font-size: 12px;">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;"></th>
                        <th style="width: 80px;">ID</th>
                        <th>Agence</th>
                        <th>Caissier</th>
                        <th style="text-align: center;">Date Ouv.</th>
                        <th style="text-align: center;">Clôture</th>
                        <th class="text-right">Fond Caisse</th>
                        <th class="text-right">Total Ventes</th>
                        <th class="text-right">Recette Brut</th>
                        <th class="text-right">Recette Net</th>
                        <th class="text-right">Recette Phys</th>
                        <th class="text-right">Espèces Brut</th>
                        <th class="text-right">Tot Dépenses</th>
                        <th class="text-right">Espèces Net</th>
                        <th class="text-right">TPE</th>
                        <th class="text-right">Chèque</th>
                        <th class="text-right">Bon Conv.</th>
                        <th class="text-right">Bon Achat</th>
                        <th class="text-right">Chèques Cad.</th>
                        <th class="text-right">Autres</th>
                        <th class="text-right">Crédit</th>
                        <th class="text-right">Acompte Pers.</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($journees as $j)
                    <tr style="cursor: pointer;" 
                        class="journee-row"
                        ondblclick="openTicketModal('{{ route('vente.journee.show', $j->journalcaisseid) }}')">
                        <td style="text-align: center; padding: 6px;">
                            <button onclick="openTicketModal('{{ route('vente.journee.details', $j->journalcaisseid) }}')" title="Voir les détails de la journée" class="btn btn-outline" style="padding: 6px; border-radius: 6px; color: var(--primary);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <circle cx="11.5" cy="14.5" r="2.5"></circle>
                                    <line x1="13.27" y1="16.27" x2="16" y2="19"></line>
                                </svg>
                            </button>
                        </td>
                        <td>
                            <span class="modern-badge badge-purple" style="font-weight: 600; font-family: monospace; font-size: 11px;">#{{ $j->journalcaisseid }}</span>
                        </td>
                        <td class="font-medium">{{ $j->agence_nom }}</td>
                        <td>{{ $j->caissier_nom }}</td>
                        <td style="text-align: center; color: var(--text-secondary);">
                            {{ $j->dateouverture ? \Carbon\Carbon::parse($j->dateouverture)->format('d/m/Y H:i') : '' }}
                        </td>
                        <td style="text-align: center;">
                            @if($j->datecloture)
                                <span style="color: var(--text-secondary);">{{ \Carbon\Carbon::parse($j->datecloture)->format('d/m/Y H:i') }}</span>
                            @else
                                <span class="modern-badge badge-danger" style="padding: 2px 8px; font-size: 11px;"><span class="badge-dot"></span>En cours</span>
                            @endif
                        </td>
                        <td class="amount-cell">{{ number_format((float)$j->fondcaisse, 3, '.', ' ') }}</td>
                        <td class="amount-cell" style="font-weight: 500;">{{ number_format((float)($j->ventereglee ?? 0), 3, '.', ' ') }}</td>
                        <td class="amount-cell">{{ number_format((float)($j->recettebrut ?? 0), 3, '.', ' ') }}</td>
                        <td class="amount-cell" style="font-weight: 600; color: var(--primary);">{{ number_format((float)($j->recettenet ?? 0), 3, '.', ' ') }}</td>
                        <td class="amount-cell" style="font-weight: 600; color: #0369a1; background: rgba(3, 105, 161, 0.02);">{{ number_format((float)($j->recettephysique ?? 0), 3, '.', ' ') }}</td>
                        <td class="amount-cell">{{ number_format((float)($j->totalespece ?? 0), 3, '.', ' ') }}</td>
                        <td class="amount-cell text-danger">{{ number_format((float)($j->montantdepense ?? 0), 3, '.', ' ') }}</td>
                        <td class="amount-cell">{{ number_format((float)($j->totalespecenet ?? 0), 3, '.', ' ') }}</td>
                        <td class="amount-cell">{{ number_format((float)($j->totaltpe ?? 0), 3, '.', ' ') }}</td>
                        <td class="amount-cell">{{ number_format((float)($j->totalcheque ?? 0), 3, '.', ' ') }}</td>
                        <td class="amount-cell">{{ number_format((float)($j->totalbonconvention ?? 0), 3, '.', ' ') }}</td>
                        <td class="amount-cell">{{ number_format((float)($j->totalcontrebon ?? 0), 3, '.', ' ') }}</td>
                        <td class="amount-cell">0.000</td>
                        <td class="amount-cell">{{ number_format((float)($j->totalregautre ?? 0), 3, '.', ' ') }}</td>
                        <td class="amount-cell">{{ number_format((float)($j->totalcredit ?? 0), 3, '.', ' ') }}</td>
                        <td class="amount-cell">{{ number_format((float)($j->acomptepersonnel ?? 0), 3, '.', ' ') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="22">
                            <div class="empty-state">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="8" y1="12" x2="16" y2="12"></line>
                                </svg>
                                <p>Aucune session de caisse trouvée</p>
                                <span style="font-size: 13px; color: var(--text-muted);">Essayez de modifier les dates du filtre.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Backdrop -->
<div id="ticketModal" class="modal-backdrop" style="display: none;">
    <!-- Large ticket modal wrapper -->
    <div class="modal-content" style="max-width: 480px; height: 90vh; padding: 0; overflow: hidden; display: flex; flex-direction: column; background: transparent; box-shadow: none; border: none;">
        
        <!-- Animated close button -->
        <button onclick="closeTicketModal()" class="modal-close" style="right: 12px; top: 12px; background: rgba(15, 23, 42, 0.7); color: white; border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; z-index: 10; font-size: 18px; border: 1px solid rgba(255,255,255,0.1); box-shadow: var(--shadow-lg); transition: all 0.2s;" onmouseover="this.style.background='rgba(239, 68, 68, 0.9)'" onmouseout="this.style.background='rgba(15, 23, 42, 0.7)'">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
        
        <!-- Details iframe -->
        <iframe id="ticketIframe" src="" style="width: 100%; height: 100%; border: none; background: transparent; border-radius: var(--radius-lg);"></iframe>
    </div>
</div>

<script>
    function openTicketModal(url) {
        const modal = document.getElementById('ticketModal');
        document.getElementById('ticketIframe').src = url;
        modal.style.display = 'flex';
        // Force layout engine reflow to trigger scale animation
        void modal.offsetWidth;
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeTicketModal() {
        const modal = document.getElementById('ticketModal');
        modal.classList.remove('show');
        setTimeout(() => {
            modal.style.display = 'none';
            document.getElementById('ticketIframe').src = '';
        }, 300);
        document.body.style.overflow = '';
    }

    // Modal click out to close
    document.getElementById('ticketModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeTicketModal();
        }
    });
</script>
@endsection
