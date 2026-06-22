@extends('layouts.app')
@section('title', 'Etat de Stock')

@section('content')
<form method="GET" action="{{ route('stock.etat.index') }}" id="filterForm">
<div class="pos-container">
    
    <!-- En-tête de la page -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Etat de Stock</h1>
            <p class="page-subtitle">Suivi global et comptable de l'état des stocks réels de votre POS.</p>
        </div>
        <div class="header-actions">
            <button type="button" class="btn btn-outline" onclick="openFilterModal()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                </svg>
                Filtres
            </button>
            <a href="{{ route('stock.etat.index') }}" class="btn btn-outline" title="Réinitialiser tous les filtres">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                    <path d="M3 3v5h5"/>
                </svg>
                Réinitialiser
            </a>
            <button type="button" class="btn btn-outline" onclick="window.print()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <rect x="6" y="14" width="12" height="8"></rect>
                </svg>
                Imprimer
            </button>
        </div>
    </div>

    <!-- Grille d'indicateurs (KPI) -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon-wrapper bg-indigo-light">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                </svg>
            </div>
            <div class="kpi-info">
                <span class="kpi-label">Articles en Stock</span>
                <span class="kpi-value" id="kpi_articles_count">{{ number_format($sumEntrer > 0 || $sumDispo > 0 ? $etatStocks->total() : 0, 0, '', ' ') }}</span>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon-wrapper bg-blue-light">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--info)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="9" y1="3" x2="9" y2="21"></line>
                    <line x1="15" y1="3" x2="15" y2="21"></line>
                </svg>
            </div>
            <div class="kpi-info">
                <span class="kpi-label">Quantité Dispo (Totale)</span>
                <span class="kpi-value text-info" id="kpi_dispo_total">{{ number_format($sumDispo, 0, '', ' ') }}</span>
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
                <span class="kpi-label">Total Ventes (Vente)</span>
                <span class="kpi-value text-success" id="kpi_vente_total">{{ number_format($sumVente, 0, '', ' ') }}</span>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon-wrapper bg-red-light">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--danger)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            </div>
            <div class="kpi-info">
                <span class="kpi-label">Valeur du Stock (PV)</span>
                <span class="kpi-value text-danger" id="kpi_val_pv_total">{{ number_format($sumValPv, 3, ',', ' ') }}</span>
            </div>
        </div>
    </div>

    <!-- Carte principale de contenu -->
    <div class="content-card" style="min-height: 450px; display: flex; flex-direction: column;">
        
        <!-- Barre d'outils / Recherche -->
        <div class="toolbar">
            <div class="search-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="search-icon">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" name="search" id="globalSearch" class="search-input" value="{{ request('search') }}" placeholder="Recherche rapide (Code, Couleur, Taille...)">
            </div>
        </div>

        <!-- Tableau de Données -->
        <div class="table-responsive" style="flex: 1;">
            <table class="data-table" id="dataTable" style="font-size: 12px;">
                <thead>
                    <tr>
                        <th style="border-right: 1px solid var(--border); width: 10%;">Code</th>
                        <th style="border-right: 1px solid var(--border); width: 10%;">Couleur</th>
                        <th style="border-right: 1px solid var(--border); width: 8%;">Taille</th>
                        <th class="text-right" style="border-right: 1px solid var(--border); width: 7%;">Entrer</th>
                        <th class="text-right" style="border-right: 1px solid var(--border); width: 7%;">Sortie</th>
                        <th class="text-right" style="border-right: 1px solid var(--border); width: 7%;">Achat</th>
                        <th class="text-right" style="border-right: 1px solid var(--border); width: 7%;">Ret.Achat</th>
                        <th class="text-right" style="border-right: 1px solid var(--border); width: 7%;">Vente</th>
                        <th class="text-right" style="border-right: 1px solid var(--border); width: 7%;">Ret.Vente</th>
                        <th class="text-right" style="border-right: 1px solid var(--border); width: 8%; font-weight: 700;">Dispo</th>
                        <th class="text-right" style="border-right: 1px solid var(--border); width: 10%;">PV.TTC</th>
                        <th class="text-right" style="width: 12%; font-weight: 700;">Val Au (PV)</th>
                    </tr>
                    <tr class="filter-row">
                        <th style="border-right: 1px solid var(--border);"><input type="text" class="filter-col" data-col="f_code" value="{{ request('f_code') }}" placeholder="Filtrer..."></th>
                        <th style="border-right: 1px solid var(--border);"><input type="text" class="filter-col" data-col="f_couleur" value="{{ request('f_couleur') }}" placeholder="Filtrer..."></th>
                        <th style="border-right: 1px solid var(--border);"><input type="text" class="filter-col" data-col="f_taille" value="{{ request('f_taille') }}" placeholder="Filtrer..."></th>
                        <th style="border-right: 1px solid var(--border);"><input type="text" class="filter-col text-right" data-col="f_entrer" placeholder="..."></th>
                        <th style="border-right: 1px solid var(--border);"><input type="text" class="filter-col text-right" data-col="f_sortie" placeholder="..."></th>
                        <th style="border-right: 1px solid var(--border);"><input type="text" class="filter-col text-right" data-col="f_achat" placeholder="..."></th>
                        <th style="border-right: 1px solid var(--border);"><input type="text" class="filter-col text-right" data-col="f_ret_achat" placeholder="..."></th>
                        <th style="border-right: 1px solid var(--border);"><input type="text" class="filter-col text-right" data-col="f_vente" placeholder="..."></th>
                        <th style="border-right: 1px solid var(--border);"><input type="text" class="filter-col text-right" data-col="f_ret_vente" placeholder="..."></th>
                        <th style="border-right: 1px solid var(--border);"><input type="text" class="filter-col text-right" data-col="f_dispo" placeholder="..."></th>
                        <th style="border-right: 1px solid var(--border);"><input type="text" class="filter-col text-right" data-col="f_pv_ttc" placeholder="..."></th>
                        <th><input type="text" class="filter-col text-right" data-col="f_val_pv" placeholder="..."></th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @include('stock.etat.partials.table_body')
                </tbody>
                @if(count($etatStocks) > 0)
                <tfoot>
                    <tr class="table-totals">
                        <td colspan="3" class="totals-label" style="border-right: 1px solid var(--border);" id="tot_label">Total ({{ $etatStocks->total() }} Articles)</td>
                        <td class="amount-cell text-right" style="border-right: 1px solid var(--border);" id="tot_entrer">{{ $sumEntrer != 0 ? number_format($sumEntrer, 0, '', ' ') : '0' }}</td>
                        <td class="amount-cell text-right" style="border-right: 1px solid var(--border);" id="tot_sortie">{{ $sumSortie != 0 ? number_format($sumSortie, 0, '', ' ') : '0' }}</td>
                        <td class="amount-cell text-right" style="border-right: 1px solid var(--border);" id="tot_achat">{{ $sumAchat != 0 ? number_format($sumAchat, 0, '', ' ') : '0' }}</td>
                        <td class="amount-cell text-right text-muted" style="border-right: 1px solid var(--border);" id="tot_ret_achat">{{ $sumRetAchat != 0 ? number_format($sumRetAchat, 0, '', ' ') : '0' }}</td>
                        <td class="amount-cell text-right text-primary" style="border-right: 1px solid var(--border);" id="tot_vente">{{ $sumVente != 0 ? number_format($sumVente, 0, '', ' ') : '0' }}</td>
                        <td class="amount-cell text-right text-muted" style="border-right: 1px solid var(--border);" id="tot_ret_vente">{{ $sumRetVente != 0 ? number_format($sumRetVente, 0, '', ' ') : '0' }}</td>
                        <td class="amount-cell text-right font-bold text-success" style="border-right: 1px solid var(--border);" id="tot_dispo">{{ $sumDispo != 0 ? number_format($sumDispo, 0, '', ' ') : '0' }}</td>
                        <td class="amount-cell text-right" style="border-right: 1px solid var(--border);" id="tot_pv">{{ number_format($sumPv, 3, ',', ' ') }}</td>
                        <td class="amount-cell text-right font-bold" id="tot_val_pv">{{ number_format($sumValPv, 3, ',', ' ') }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        <!-- Pagination Wrapper -->
        <div class="pagination-wrapper" id="paginationWrapper">
            @if($etatStocks->hasPages())
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                <div>
                    {{ $etatStocks->appends(request()->query())->links('pagination::bootstrap-4') }}
                </div>
                <div style="font-size: 13px; color: var(--text-muted); font-weight: 500;" id="pagination_summary">
                    Affichage de {{ $etatStocks->firstItem() }} à {{ $etatStocks->lastItem() }} sur {{ $etatStocks->total() }} résultats
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Filtre (Modal Backdrop layout) -->
<div id="filterModal" class="modal-backdrop" style="display: none;">
    <div class="modal-content" style="max-width: 600px; padding: 24px; border-radius: var(--radius-lg); transform: scale(0.95); transition: transform 0.3s;">
        <!-- Bouton fermer modal -->
        <button type="button" onclick="closeFilterModal()" class="modal-close" style="top: 20px; right: 20px;">×</button>
        
        <!-- Titre en-tête modal -->
        <div style="font-size: 18px; font-weight: 700; color: var(--text-main); margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary" style="color: var(--primary);">
                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
            </svg>
            Filtres d'État de Stock
        </div>
        
        <div class="modal-body-wrapper">
            <!-- Grille de Filtres -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Famille</label>
                    <select name="familleid" class="form-control" style="height: 38px; padding: 6px 12px;">
                        <option value="">Sélectionner Famille...</option>
                        @foreach($familles as $f)
                            <option value="{{ $f->familleid }}" {{ request('familleid') == $f->familleid ? 'selected' : '' }}>{{ $f->famillelibelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Sous-Famille</label>
                    <select name="sousfamilleid" class="form-control" style="height: 38px; padding: 6px 12px;">
                        <option value="">Sélectionner Sous-Famille...</option>
                        @foreach($sousFamilles as $sf)
                            <option value="{{ $sf->sousfamilleid }}" {{ request('sousfamilleid') == $sf->sousfamilleid ? 'selected' : '' }}>{{ $sf->sousfamillelibelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Saison</label>
                    <select name="saisonid" class="form-control" style="height: 38px; padding: 6px 12px;">
                        <option value="">Sélectionner Saison...</option>
                        @foreach($saisons as $s)
                            <option value="{{ $s->category4id }}" {{ request('saisonid') == $s->category4id ? 'selected' : '' }}>{{ $s->category4libelle }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 16px; margin-top: 16px;">
                <div class="form-group" style="max-width: 32%;">
                    <label class="form-label">Rayon</label>
                    <select name="rayonid" class="form-control" style="height: 38px; padding: 6px 12px;">
                        <option value="">Sélectionner Rayon...</option>
                        @foreach($rayons as $r)
                            <option value="{{ $r->categoryid }}" {{ request('rayonid') == $r->categoryid ? 'selected' : '' }}>{{ $r->categorylibelle }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Actions Modal -->
            <div class="filters-actions" style="margin-top: 24px; padding-top: 16px; display: flex; justify-content: flex-end; gap: 12px; border-top: 1px dashed var(--border);">
                <button type="button" class="btn btn-outline" onclick="closeFilterModal()">Annuler</button>
                <a href="{{ route('stock.etat.index') }}" class="btn btn-outline">Réinitialiser</a>
                <button type="submit" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px;">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    Appliquer les Filtres
                </button>
            </div>
        </div>
    </div>
</div>
</form>

<script>
// Modal helpers
function openFilterModal() {
    const modal = document.getElementById('filterModal');
    modal.style.display = 'flex';
    void modal.offsetWidth;
    modal.classList.add('show');
}

function closeFilterModal() {
    const modal = document.getElementById('filterModal');
    modal.classList.remove('show');
    setTimeout(() => {
        modal.style.display = 'none';
    }, 300);
}

// Close on outside click
document.getElementById('filterModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeFilterModal();
    }
});

document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const globalSearch = document.getElementById('globalSearch');
    const filterInputs = document.querySelectorAll('.filter-col');
    const filterForm = document.getElementById('filterForm');
    const tableBody = document.getElementById('tableBody');
    const paginationWrapper = document.getElementById('paginationWrapper');

    let debounceTimer;

    function fetchFilteredData(url = '{{ route("stock.etat.index") }}') {
        const params = new URLSearchParams();

        // Global Search
        if (globalSearch && globalSearch.value) {
            params.append('search', globalSearch.value);
        }

        // Modal dropdown filters
        const famille = document.querySelector('select[name="familleid"]');
        const sousFamille = document.querySelector('select[name="sousfamilleid"]');
        const rayon = document.querySelector('select[name="rayonid"]');
        const saison = document.querySelector('select[name="saisonid"]');
        if (famille && famille.value) params.append('familleid', famille.value);
        if (sousFamille && sousFamille.value) params.append('sousfamilleid', sousFamille.value);
        if (rayon && rayon.value) params.append('rayonid', rayon.value);
        if (saison && saison.value) params.append('saisonid', saison.value);

        // Column-level filters
        filterInputs.forEach(input => {
            if (input.value) {
                params.append(input.getAttribute('data-col'), input.value);
            }
        });

        const fetchUrl = `${url}${url.includes('?') ? '&' : '?'}${params.toString()}`;

        // Opacity loader
        tableBody.style.opacity = '0.5';

        fetch(fetchUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            tableBody.style.opacity = '1';
            tableBody.innerHTML = data.html;
            if (paginationWrapper) {
                paginationWrapper.innerHTML = data.pagination;
            }
            attachPaginationEvents();

            // Update footer totals
            if (data.totals) {
                if (document.getElementById('tot_label')) {
                    document.getElementById('tot_label').textContent = `Total (${data.totals.articles_count} Articles)`;
                }
                if (document.getElementById('tot_entrer')) document.getElementById('tot_entrer').textContent = data.totals.entrer;
                if (document.getElementById('tot_sortie')) document.getElementById('tot_sortie').textContent = data.totals.sortie;
                if (document.getElementById('tot_achat')) document.getElementById('tot_achat').textContent = data.totals.achat;
                if (document.getElementById('tot_ret_achat')) document.getElementById('tot_ret_achat').textContent = data.totals.ret_achat;
                if (document.getElementById('tot_vente')) document.getElementById('tot_vente').textContent = data.totals.vente;
                if (document.getElementById('tot_ret_vente')) document.getElementById('tot_ret_vente').textContent = data.totals.ret_vente;
                if (document.getElementById('tot_dispo')) document.getElementById('tot_dispo').textContent = data.totals.dispo;
                if (document.getElementById('tot_pv')) document.getElementById('tot_pv').textContent = data.totals.pv;
                if (document.getElementById('tot_val_pv')) document.getElementById('tot_val_pv').textContent = data.totals.val_pv;
            }

            // Update KPIs
            if (data.kpis) {
                if (document.getElementById('kpi_articles_count')) document.getElementById('kpi_articles_count').textContent = data.kpis.articles_count;
                if (document.getElementById('kpi_dispo_total')) document.getElementById('kpi_dispo_total').textContent = data.kpis.dispo_total;
                if (document.getElementById('kpi_vente_total')) document.getElementById('kpi_vente_total').textContent = data.kpis.vente_total;
                if (document.getElementById('kpi_val_pv_total')) document.getElementById('kpi_val_pv_total').textContent = data.kpis.val_pv_total;
            }
        })
        .catch(err => {
            console.error('Error fetching stock state:', err);
            tableBody.style.opacity = '1';
        });
    }

    function handleInput() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => fetchFilteredData(), 400);
    }

    // Input listeners
    if (globalSearch) {
        globalSearch.addEventListener('input', handleInput);
    }
    filterInputs.forEach(input => {
        input.addEventListener('input', handleInput);
        input.addEventListener('change', handleInput);
    });

    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            fetchFilteredData();
            closeFilterModal();
        });
    }

    function attachPaginationEvents() {
        const paginationLinks = document.querySelectorAll('.pagination-wrapper a');
        paginationLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                fetchFilteredData(this.href);
            });
        });
    }

    attachPaginationEvents();
});
</script>
@endsection
