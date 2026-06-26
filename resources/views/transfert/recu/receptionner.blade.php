@extends('layouts.app')

@section('title', 'Réception du Bon de Transfert - Golden Pos')

@section('content')
<div class="pos-container">

    <!-- Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Réception Bon de Transfert : {{ $bon->numero }}</h1>
            <p class="page-subtitle">Vérifiez et réceptionnez les articles envoyés par {{ $bon->emetteur }}.</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('transfert.recu.index') }}" class="btn btn-outline">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                Retour
            </a>
            <button type="submit" form="formReception" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                    <polyline points="7 3 7 8 15 8"></polyline>
                </svg>
                Enregistrer la réception
            </button>
        </div>
    </div>

    <!-- Info Cards -->
    <div class="kpi-grid" style="margin-bottom: 20px;">
        <div class="kpi-card" style="padding: 16px;">
            <div class="kpi-info">
                <span class="kpi-label" style="font-size: 12px;">Emetteur</span>
                <span class="kpi-value" style="font-size: 16px;">{{ $bon->emetteur }}</span>
            </div>
        </div>
        <div class="kpi-card" style="padding: 16px;">
            <div class="kpi-info">
                <span class="kpi-label" style="font-size: 12px;">Date Envoi</span>
                <span class="kpi-value" style="font-size: 16px;">{{ \Carbon\Carbon::parse($bon->date)->format('d/m/Y') }}</span>
            </div>
        </div>
        <div class="kpi-card" style="padding: 16px;">
            <div class="kpi-info">
                <span class="kpi-label" style="font-size: 12px;">Véhicule & Trajet</span>
                <span class="kpi-value" style="font-size: 16px;">{{ $bon->vehicule }} {{ $bon->matricule ? '('.$bon->matricule.')' : '' }} | {{ $bon->trajet }}</span>
            </div>
        </div>
        <div class="kpi-card" style="padding: 16px;">
            <div class="kpi-info">
                <span class="kpi-label" style="font-size: 12px;">Quantité Totale</span>
                <span class="kpi-value text-primary" style="font-size: 16px;">{{ $bon->qte }} articles</span>
            </div>
        </div>
    </div>

    <!-- Formulaire de réception -->
    <div class="content-card">
        <form id="formReception" method="POST" action="{{ route('transfert.recu.store_reception', $bon->bontransfertid) }}">
            @csrf

            <div class="table-responsive">
                <table class="data-table" id="dataTable">
                    <thead>
                        <tr>
                            <th style="width: 15%;">Réf</th>
                            <th style="width: 20%;">Désignation</th>
                            <th style="width: 10%;">Taille</th>
                            <th style="width: 15%;">Couleur</th>
                            <th style="width: 10%; text-align: center;">Qté Envoyée</th>
                            <th style="width: 12%; text-align: center;">Qté Reçue</th>
                            <th style="width: 18%;">Observation</th>
                        </tr>
                        <tr class="filter-row">
                            <th><input type="text" class="local-filter" data-col="0" placeholder="Filtrer Réf..." style="width:100%; border:1px solid var(--border); border-radius:4px; padding:4px;"></th>
                            <th><input type="text" class="local-filter" data-col="1" placeholder="Filtrer Désignation..." style="width:100%; border:1px solid var(--border); border-radius:4px; padding:4px;"></th>
                            <th><input type="text" class="local-filter" data-col="2" placeholder="Filtrer Taille..." style="width:100%; border:1px solid var(--border); border-radius:4px; padding:4px;"></th>
                            <th><input type="text" class="local-filter" data-col="3" placeholder="Filtrer Couleur..." style="width:100%; border:1px solid var(--border); border-radius:4px; padding:4px;"></th>
                            <th><input type="text" class="local-filter" data-col="4" placeholder="Filtrer Qté Env..." style="width:100%; border:1px solid var(--border); border-radius:4px; padding:4px; text-align:center;"></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        @forelse($lignes as $index => $ligne)
                            <tr class="hover-row">
                                <td class="font-medium text-primary">{{ $ligne->reference }}</td>
                                <td>{{ $ligne->libelle }}</td>
                                <td>{{ $ligne->taille }}</td>
                                <td>
                                    <span class="status-badge" style="background: var(--background); color: var(--text-main); border: 1px solid var(--border);">
                                        {{ $ligne->couleur }}
                                    </span>
                                </td>
                                <td style="text-align: center; font-weight: 600;">{{ $ligne->qte }}</td>
                                <td style="text-align: center;">
                                    <input type="number" 
                                           name="reception[{{ $ligne->detbontransfertid }}][qte_recue]" 
                                           class="form-control" 
                                           style="width: 80px; text-align: center; display: inline-block; padding: 4px;"
                                           value="{{ $ligne->qterecu > 0 ? $ligne->qterecu : $ligne->qte }}" 
                                           min="0"
                                           max="{{ $ligne->qte }}">
                                </td>
                                <td>
                                    <input type="text" 
                                           name="reception[{{ $ligne->detbontransfertid }}][observation]" 
                                           class="form-control" 
                                           style="padding: 4px 8px;"
                                           placeholder="Si écart, préciser..."
                                           value="{{ $ligne->description }}">
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="padding: 40px; text-align: center; color: var(--text-muted);">
                                    Aucun article dans ce bon de transfert.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if(count($lignes) > 0)
            <div style="padding: 20px; border-top: 1px solid var(--border); background-color: var(--background); text-align: right; border-radius: 0 0 12px 12px;">
                <button type="submit" class="btn btn-primary">
                    Valider la réception
                </button>
            </div>
            @endif
        </form>
    </div>
</div>
@endsection

@section('styles')
<style>
    .kpi-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
</style>
@endsection

@section('scripts')
<script>
    document.getElementById('formReception').addEventListener('submit', function(e) {
        // Confirmation optionnelle avant la validation
        if(!confirm('Êtes-vous sûr de vouloir valider cette réception ? Toute validation est définitive.')) {
            e.preventDefault();
        }
    });

    // Local filtering
    document.addEventListener('DOMContentLoaded', function() {
        const filters = document.querySelectorAll('.local-filter');
        filters.forEach(filter => {
            filter.addEventListener('input', function() {
                const tbody = document.getElementById('tableBody');
                const rows = tbody.querySelectorAll('tr.hover-row');
                
                rows.forEach(row => {
                    let showRow = true;
                    filters.forEach(f => {
                        const colIdx = f.getAttribute('data-col');
                        const filterVal = f.value.toLowerCase();
                        if(filterVal) {
                            const cell = row.cells[colIdx];
                            if(cell) {
                                const cellText = cell.textContent.toLowerCase();
                                if(!cellText.includes(filterVal)) {
                                    showRow = false;
                                }
                            }
                        }
                    });
                    row.style.display = showRow ? '' : 'none';
                });
            });
        });
    });
</script>
@endsection
