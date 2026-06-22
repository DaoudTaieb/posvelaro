@extends('layouts.app')
@section('title', 'Consultation des Articles')

@section('content')
<div class="pos-container">
    
    <!-- Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Consultation des Articles</h1>
            <p class="page-subtitle">Consultez, recherchez et gérez les articles, tarifs et caractéristiques de votre stock.</p>
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
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon-wrapper bg-indigo-light">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                    <line x1="12" y1="22.08" x2="12" y2="12"></line>
                </svg>
            </div>
            <div class="kpi-info">
                <span class="kpi-label">Articles Filtrés</span>
                <span class="kpi-value">{{ $articles->total() }}</span>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon-wrapper bg-blue-light">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                    <polyline points="2 17 12 22 22 17"></polyline>
                    <polyline points="2 12 12 17 22 12"></polyline>
                </svg>
            </div>
            <div class="kpi-info">
                <span class="kpi-label">Affichés / Page</span>
                <span class="kpi-value text-primary">{{ count($articles) }}</span>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon-wrapper bg-green-light">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                    <line x1="7" y1="7" x2="7.01" y2="7"></line>
                </svg>
            </div>
            <div class="kpi-info">
                <span class="kpi-label">Familles</span>
                <span class="kpi-value text-success">{{ count($familles) }}</span>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon-wrapper bg-red-light">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--danger)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
            </div>
            <div class="kpi-info">
                <span class="kpi-label">Saisons</span>
                <span class="kpi-value text-danger">{{ count($saisons) }}</span>
            </div>
        </div>
    </div>

    <!-- Main Content Card -->
    <div class="content-card">
        
        <!-- Toolbar / Filters -->
        <div class="toolbar">
            <form method="GET" action="{{ route('stock.articles.index') }}" style="display: flex; gap: 16px; align-items: flex-end; flex-wrap: wrap; width: 100%;">
                
                <div class="form-group" style="flex: 1; min-width: 200px; margin: 0;">
                    <label class="form-label" style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: var(--text-secondary);">Famille</label>
                    <select name="familleid" class="form-control" style="height: 38px; padding: 6px 12px;">
                        <option value="">Toutes les familles...</option>
                        @foreach($familles as $f)
                            <option value="{{ $f->familleid }}" {{ request('familleid') == $f->familleid ? 'selected' : '' }}>{{ $f->famillelibelle }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="flex: 1; min-width: 200px; margin: 0;">
                    <label class="form-label" style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: var(--text-secondary);">Sous-Famille</label>
                    <select name="sousfamilleid" class="form-control" style="height: 38px; padding: 6px 12px;">
                        <option value="">Toutes les sous-familles...</option>
                        @foreach($sousFamilles as $sf)
                            <option value="{{ $sf->sousfamilleid }}" {{ request('sousfamilleid') == $sf->sousfamilleid ? 'selected' : '' }}>{{ $sf->sousfamillelibelle }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="flex: 1; min-width: 200px; margin: 0;">
                    <label class="form-label" style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: var(--text-secondary);">Saison</label>
                    <select name="saisonid" class="form-control" style="height: 38px; padding: 6px 12px;">
                        <option value="">Toutes les saisons...</option>
                        @foreach($saisons as $s)
                            <option value="{{ $s->category4id }}" {{ request('saisonid') == $s->category4id ? 'selected' : '' }}>{{ $s->category4libelle }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="flex: 1; min-width: 200px; margin: 0;">
                    <label class="form-label" style="font-size: 11px; font-weight: 600; text-transform: uppercase; color: var(--text-secondary);">Rayon</label>
                    <select name="rayonid" class="form-control" style="height: 38px; padding: 6px 12px;">
                        <option value="">Tous les rayons...</option>
                        @foreach($rayons as $r)
                            <option value="{{ $r->categoryid }}" {{ request('rayonid') == $r->categoryid ? 'selected' : '' }}>{{ $r->categorylibelle }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="display: flex; gap: 8px;">
                    <button type="submit" class="btn btn-primary" style="height: 38px; padding: 0 16px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                        </svg>
                        Filtrer
                    </button>
                    <a href="{{ route('stock.articles.index') }}" class="btn btn-outline" style="height: 38px; padding: 0 12px; display: flex; align-items: center; justify-content: center;" title="Réinitialiser les filtres">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                            <path d="M3 3v5h5"/>
                        </svg>
                    </a>
                </div>
            </form>
        </div>

        <!-- Table Grid -->
        <div class="table-responsive" style="min-height: auto;">
            <table class="data-table" id="articlesTable" style="font-size: 12px;">
                <thead>
                    <tr>
                        <th style="width: 120px;">Code</th>
                        <th>Référence</th>
                        <th>Code à Barre</th>
                        <th>Désignation</th>
                        <th>Famille</th>
                        <th>Sous Famille</th>
                        <th class="text-right">Vente TTC</th>
                        <th class="text-center" style="width: 100px;">Fidélité</th>
                        <th>Saison</th>
                        <th>Marque</th>
                    </tr>
                    <tr class="filter-row">
                        <th><input type="text" class="filter-col" data-col="0" placeholder="Filtrer..."></th>
                        <th><input type="text" class="filter-col" data-col="1" placeholder="Filtrer..."></th>
                        <th><input type="text" class="filter-col" data-col="2" placeholder="Filtrer..."></th>
                        <th><input type="text" class="filter-col" data-col="3" placeholder="Filtrer..."></th>
                        <th><input type="text" class="filter-col" data-col="4" placeholder="Filtrer..."></th>
                        <th><input type="text" class="filter-col" data-col="5" placeholder="Filtrer..."></th>
                        <th><input type="text" class="filter-col text-right" data-col="6" placeholder="Filtrer..."></th>
                        <th><input type="text" class="filter-col text-center" data-col="7" placeholder="Filtrer..."></th>
                        <th><input type="text" class="filter-col" data-col="8" placeholder="Filtrer..."></th>
                        <th><input type="text" class="filter-col" data-col="9" placeholder="Filtrer..."></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($articles as $article)
                    <tr class="data-row">
                        <td class="font-medium" style="color: var(--text);">{{ $article->produitcode }}</td>
                        <td>{{ $article->reference }}</td>
                        <td style="font-family: monospace; font-size: 11px; color: var(--text-secondary);">{{ $article->variant_barcode ?: $article->produit2id }}</td>
                        <td style="font-weight: 500; color: var(--text-main);">{{ $article->produitlibelle }}</td>
                        <td>{{ $article->famillelibelle }}</td>
                        <td>{{ $article->sousfamillelibelle }}</td>
                        <td class="amount-cell font-bold text-primary">{{ number_format((float)($article->ttc_vente ?? 0), 3, '.', ' ') }}</td>
                        <td style="text-align: center;">
                            @if($article->isfidelite)
                                <span class="modern-badge badge-success" style="padding: 2px 8px; font-size: 11px;"><span class="badge-dot"></span>Oui</span>
                            @else
                                <span class="modern-badge badge-default" style="padding: 2px 8px; font-size: 11px;"><span class="badge-dot"></span>Non</span>
                            @endif
                        </td>
                        <td>{{ $article->saison_nom }}</td>
                        <td>{{ $article->marque_nom }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10">
                            <div class="empty-state">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="8" y1="12" x2="16" y2="12"></line>
                                </svg>
                                <p>Aucun article trouvé</p>
                                <span style="font-size: 13px; color: var(--text-muted);">Essayez de modifier les options de filtrage.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Wrapper -->
        @if($articles->hasPages())
        <div class="pagination-wrapper">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                <div>
                    {{ $articles->appends(request()->query())->links('pagination::bootstrap-4') }}
                </div>
                <div style="font-size: 13px; color: var(--text-muted); font-weight: 500;">
                    Affichage de {{ $articles->firstItem() }} à {{ $articles->lastItem() }} sur {{ $articles->total() }} articles
                </div>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('.filter-row input');
        
        inputs.forEach(input => {
            input.addEventListener('keyup', function() {
                filterTable();
            });
        });

        function filterTable() {
            const rows = document.querySelectorAll('#articlesTable tbody tr.data-row');
            let visibleCount = 0;

            rows.forEach(row => {
                let showRow = true;
                const cells = row.querySelectorAll('td');

                inputs.forEach(input => {
                    const filterValue = input.value.toLowerCase().trim();
                    if (filterValue !== '') {
                        const colIndex = parseInt(input.getAttribute('data-col'));
                        const cellText = cells[colIndex].textContent.toLowerCase();
                        if (!cellText.includes(filterValue)) {
                            showRow = false;
                        }
                    }
                });

                if (showRow) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Gérer le message d'absence de données dynamiquement
            let hiddenNoData = document.getElementById('hiddenNoData');
            if (visibleCount === 0 && rows.length > 0) {
                if (!hiddenNoData) {
                    const tbody = document.querySelector('#articlesTable tbody');
                    hiddenNoData = document.createElement('tr');
                    hiddenNoData.id = 'hiddenNoData';
                    hiddenNoData.innerHTML = '<td colspan="10"><div class="empty-state" style="padding: 24px;"><p>Aucune correspondance trouvée</p><span style="font-size: 13px; color: var(--text-muted);">Essayez de modifier vos filtres par colonne.</span></div></td>';
                    tbody.appendChild(hiddenNoData);
                }
                hiddenNoData.style.display = '';
            } else if (hiddenNoData) {
                hiddenNoData.style.display = 'none';
            }
        }
    });
</script>
@endsection
