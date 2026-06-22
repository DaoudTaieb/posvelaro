@extends('layouts.app')
@section('title', 'Consultation Stock')

@section('content')
<div class="pos-container">
    
    <!-- Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Consultation du Stock</h1>
            <p class="page-subtitle">Visualisez les niveaux de stock réel, virtuel et réservé par rayon, taille et couleur.</p>
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
        $totalLines = $stocks->total();
        $pageQtySum = 0;
        foreach($stocks as $stock) {
            if($selectedType == 'reel') $pageQtySum += $stock->qtestock;
            elseif($selectedType == 'virtuel') $pageQtySum += $stock->stockvirtuel;
            elseif($selectedType == 'reserve') $pageQtySum += $stock->stockreserve;
        }
    @endphp
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon-wrapper bg-indigo-light">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="8" y1="6" x2="21" y2="6"></line>
                    <line x1="8" y1="12" x2="21" y2="12"></line>
                    <line x1="8" y1="18" x2="21" y2="18"></line>
                    <line x1="3" y1="6" x2="3.01" y2="6"></line>
                    <line x1="3" y1="12" x2="3.01" y2="12"></line>
                    <line x1="3" y1="18" x2="3.01" y2="18"></line>
                </svg>
            </div>
            <div class="kpi-info">
                <span class="kpi-label">Lignes en Stock</span>
                <span class="kpi-value">{{ $totalLines }}</span>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon-wrapper bg-green-light">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                    <line x1="12" y1="22.08" x2="12" y2="12"></line>
                </svg>
            </div>
            <div class="kpi-info">
                <span class="kpi-label">Quantité (Cette Page)</span>
                <span class="kpi-value text-success">{{ number_format($pageQtySum, 0, ',', ' ') }}</span>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon-wrapper bg-blue-light">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            </div>
            <div class="kpi-info">
                <span class="kpi-label">Type Sélectionné</span>
                <span class="kpi-value text-primary">{{ $typesStock[$selectedType] }}</span>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon-wrapper bg-red-light">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--danger)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                </svg>
            </div>
            <div class="kpi-info">
                <span class="kpi-label">Rayons Actifs</span>
                <span class="kpi-value text-danger">{{ count($rayons) }}</span>
            </div>
        </div>
    </div>

    <!-- Main Content Card -->
    <div class="content-card">
        
        <!-- Toolbar / Filters -->
        <div class="toolbar">
            <form method="GET" action="{{ route('stock.consultation.index') }}" style="display: flex; gap: 16px; align-items: flex-end; flex-wrap: wrap; width: 100%;">
                
                <div class="form-group" style="width: 150px; margin: 0;">
                    <label class="form-label" style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: var(--text-secondary);">Référence</label>
                    <input type="text" name="reference" class="form-control" value="{{ request('reference') }}" style="height: 38px; padding: 6px 12px;" placeholder="Référence...">
                </div>

                <div class="form-group" style="width: 200px; margin: 0;">
                    <label class="form-label" style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: var(--text-secondary);">Rayon</label>
                    <select name="rayonid" class="form-control" style="height: 38px; padding: 6px 12px;">
                        <option value="">Tous les rayons...</option>
                        @foreach($rayons as $rayon)
                            <option value="{{ $rayon->categoryid }}" {{ request('rayonid') == $rayon->categoryid ? 'selected' : '' }}>
                                {{ $rayon->categorylibelle }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="width: 150px; margin: 0;">
                    <label class="form-label" style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: var(--text-secondary);">Couleur</label>
                    <input type="text" name="couleur" class="form-control" value="{{ request('couleur') }}" style="height: 38px; padding: 6px 12px;" placeholder="Couleur...">
                </div>

                <div class="form-group" style="width: 120px; margin: 0;">
                    <label class="form-label" style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: var(--text-secondary);">Taille</label>
                    <input type="text" name="taille" class="form-control" value="{{ request('taille') }}" style="height: 38px; padding: 6px 12px;" placeholder="Taille...">
                </div>

                <div style="display: flex; gap: 8px;">
                    <button type="submit" class="btn btn-primary" style="height: 38px; padding: 0 16px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                        </svg>
                        Filtrer
                    </button>
                    <a href="{{ route('stock.consultation.index') }}" class="btn btn-outline" style="height: 38px; padding: 0 12px; display: flex; align-items: center; justify-content: center;" title="Réinitialiser les filtres">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                            <path d="M3 3v5h5"/>
                        </svg>
                    </a>
                </div>

                <!-- Type de Stock Dropdown aligned to the right -->
                <div class="form-group" style="margin-left: auto; width: 180px; margin-bottom: 0;">
                    <label class="form-label" style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: var(--text-secondary);">Type de Stock</label>
                    <select name="type_stock" class="form-control" style="background-color: var(--background); font-weight: 600; border-color: var(--primary); height: 38px; padding: 6px 12px;" onchange="this.form.submit()">
                        @foreach($typesStock as $key => $label)
                            <option value="{{ $key }}" {{ $selectedType == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        <!-- Table Grid -->
        <div class="table-responsive" style="min-height: auto;">
            <table class="data-table" id="consultationStockTable" style="font-size: 13px;">
                <thead>
                    <tr>
                        <th style="width: 120px;">Code</th>
                        <th>Référence</th>
                        <th>Désignation</th>
                        <th>Rayon</th>
                        <th>Couleur</th>
                        <th style="width: 100px;">Taille</th>
                        <th class="text-right" style="width: 180px; background: rgba(99, 102, 241, 0.05); color: var(--primary); font-weight: 700;">Quantité ({{ $typesStock[$selectedType] }})</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stocks as $stock)
                    <tr>
                        <td class="font-medium" style="color: var(--text);">{{ $stock->produitcode }}</td>
                        <td>{{ $stock->reference }}</td>
                        <td style="font-weight: 500; color: var(--text-main);">{{ $stock->produitlibelle }}</td>
                        <td>{{ $stock->rayon_nom }}</td>
                        <td>{{ $stock->couleurlibelle }}</td>
                        <td>{{ $stock->taillelibelle }}</td>
                        
                        @php
                            $qty = 0;
                            if($selectedType == 'reel') $qty = $stock->qtestock;
                            elseif($selectedType == 'virtuel') $qty = $stock->stockvirtuel;
                            elseif($selectedType == 'reserve') $qty = $stock->stockreserve;
                        @endphp
                        <td class="amount-cell font-bold" style="color: {{ $qty < 0 ? 'var(--danger)' : ($qty > 0 ? 'var(--success)' : 'var(--text-muted)') }}; background: rgba(99, 102, 241, 0.02); font-size: 14px;">
                            {{ number_format($qty, 0, ',', ' ') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="8" y1="12" x2="16" y2="12"></line>
                                </svg>
                                <p>Aucun stock enregistré</p>
                                <span style="font-size: 13px; color: var(--text-muted);">Essayez de modifier vos critères de filtrage.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Wrapper -->
        @if($stocks->hasPages())
        <div class="pagination-wrapper">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                <div>
                    {{ $stocks->appends(request()->query())->links('pagination::bootstrap-4') }}
                </div>
                <div style="font-size: 13px; color: var(--text-muted); font-weight: 500;">
                    Affichage de {{ $stocks->firstItem() }} à {{ $stocks->lastItem() }} sur {{ $stocks->total() }} résultats
                </div>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection
