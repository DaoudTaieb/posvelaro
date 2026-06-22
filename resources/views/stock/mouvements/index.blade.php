@extends('layouts.app')
@section('title', 'Mouvements Des Produits')

@section('content')
<form method="GET" action="{{ route('stock.mouvements.index') }}" id="filterForm">
<div class="pos-container">
    
    <!-- Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Mouvements des Produits</h1>
            <p class="page-subtitle">Suivi chronologique et comptable des mouvements de stock de vos articles.</p>
        </div>
        <div class="header-actions">
            <button type="button" class="btn btn-outline" onclick="openFilterModal()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                </svg>
                Filtres
            </button>
            <a href="{{ route('stock.mouvements.index') }}" class="btn btn-outline" title="Réinitialiser tous les filtres">
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
                <span class="kpi-label">Total Mouvements (Lignes)</span>
                <span class="kpi-value" id="kpi_total_mouvements">{{ number_format($mouvements->total(), 0, ',', ' ') }}</span>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon-wrapper bg-green-light">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
            </div>
            <div class="kpi-info">
                <span class="kpi-label">Total Achats (Global)</span>
                <span class="kpi-value text-success" id="kpi_total_achats">{{ number_format($sumAchat, 0, ',', ' ') }}</span>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon-wrapper bg-blue-light">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="20" x2="18" y2="10"></line>
                    <line x1="12" y1="20" x2="12" y2="4"></line>
                    <line x1="6" y1="20" x2="6" y2="14"></line>
                </svg>
            </div>
            <div class="kpi-info">
                <span class="kpi-label">Total Ventes (Global)</span>
                <span class="kpi-value text-primary" id="kpi_total_ventes">{{ number_format($sumVente, 0, ',', ' ') }}</span>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon-wrapper bg-red-light">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--danger)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M23 4v6h-6"></path>
                    <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                </svg>
            </div>
            <div class="kpi-info">
                <span class="kpi-label">Ajustements Net</span>
                <span class="kpi-value text-danger" id="kpi_ajustements_net">{{ number_format($sumEntrer - $sumSortie, 0, ',', ' ') }}</span>
            </div>
        </div>
    </div>

    <!-- Main Content Card -->
    <div class="content-card" style="min-height: 450px; display: flex; flex-direction: column;">
        
        <!-- Toolbar / Filters -->
        <div class="toolbar">
            <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap; width: 100%;">
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
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    Filtrer
                </button>
            </div>
        </div>

        <!-- Table Grid -->
        <div class="table-responsive" style="flex: 1;">
            <table class="data-table" id="dataTable" style="font-size: 12px;">
                <thead>
                    <tr>
                        <th style="border-right: 1px solid var(--border);">Code</th>
                        <th style="border-right: 1px solid var(--border);">Couleur</th>
                        <th style="border-right: 1px solid var(--border);">Taille</th>
                        <th class="text-right" style="width: 80px; border-right: 1px solid var(--border);">Achat</th>
                        <th class="text-right" style="width: 80px; border-right: 1px solid var(--border);">Entrer</th>
                        <th class="text-right" style="width: 80px; border-right: 1px solid var(--border);">Sortie</th>
                        <th class="text-right" style="width: 80px; border-right: 1px solid var(--border);">Vente</th>
                        <th style="border-right: 1px solid var(--border);">N° Piece</th>
                        <th style="border-right: 1px solid var(--border); width: 90px; text-align: center;">Date</th>
                        <th style="border-right: 1px solid var(--border); width: 60px; text-align: center;">Heure</th>
                        <th style="border-right: 1px solid var(--border);">Intitule</th>
                        <th>Site</th>
                    </tr>
                    <tr class="filter-row">
                        <th style="border-right: 1px solid var(--border);"><input type="text" class="filter-col" data-col="f_code" value="{{ request('code_search') }}" placeholder="Filtrer Code..."></th>
                        <th style="border-right: 1px solid var(--border);"><input type="text" class="filter-col" data-col="f_couleur" value="{{ request('couleur_search') }}" placeholder="Filtrer Couleur..."></th>
                        <th style="border-right: 1px solid var(--border);"><input type="text" class="filter-col" data-col="f_taille" value="{{ request('taille_search') }}" placeholder="Filtrer Taille..."></th>
                        <th style="border-right: 1px solid var(--border);"><input type="text" class="filter-col text-right" data-col="f_achat" placeholder="..."></th>
                        <th style="border-right: 1px solid var(--border);"><input type="text" class="filter-col text-right" data-col="f_entrer" placeholder="..."></th>
                        <th style="border-right: 1px solid var(--border);"><input type="text" class="filter-col text-right" data-col="f_sortie" placeholder="..."></th>
                        <th style="border-right: 1px solid var(--border);"><input type="text" class="filter-col text-right" data-col="f_vente" placeholder="..."></th>
                        <th style="border-right: 1px solid var(--border);"><input type="text" class="filter-col" data-col="f_piece" value="{{ request('piece_search') }}" placeholder="Filtrer Pièce..."></th>
                        <th style="border-right: 1px solid var(--border);"><input type="text" class="filter-col text-center" data-col="f_date" placeholder="Filtrer Date..."></th>
                        <th style="border-right: 1px solid var(--border);"><input type="text" class="filter-col text-center" data-col="f_heure" placeholder="Filtrer Heure..."></th>
                        <th style="border-right: 1px solid var(--border);"><input type="text" class="filter-col" data-col="f_intitule" value="{{ request('intitule_search') }}" placeholder="Filtrer Intitulé..."></th>
                        <th><input type="text" class="filter-col" data-col="f_site" value="{{ request('site_search') }}" placeholder="Filtrer Site..."></th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @include('stock.mouvements.partials.table_body')
                </tbody>
                @if(count($mouvements) > 0)
                <tfoot>
                    <tr class="table-totals">
                        <td colspan="3" class="totals-label" style="border-right: 1px solid var(--border);" id="tot_label">Total ({{ $mouvements->total() }} Mouvements)</td>
                        <td class="amount-cell text-success" style="border-right: 1px solid var(--border);" id="tot_achat">{{ $sumAchat != 0 ? number_format($sumAchat, 0, ',', ' ') : '0' }}</td>
                        <td class="amount-cell" style="border-right: 1px solid var(--border);" id="tot_entrer">{{ $sumEntrer != 0 ? number_format($sumEntrer, 0, ',', ' ') : '0' }}</td>
                        <td class="amount-cell text-danger" style="border-right: 1px solid var(--border);" id="tot_sortie">{{ $sumSortie != 0 ? number_format($sumSortie, 0, ',', ' ') : '0' }}</td>
                        <td class="amount-cell text-primary font-bold" style="border-right: 1px solid var(--border);" id="tot_vente">{{ $sumVente != 0 ? number_format($sumVente, 0, ',', ' ') : '0' }}</td>
                        <td colspan="5"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
        
        <!-- Pagination Wrapper -->
        <div class="pagination-wrapper" id="paginationWrapper">
            @if($mouvements->hasPages())
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                <div>
                    {{ $mouvements->appends(request()->query())->links('pagination::bootstrap-4') }}
                </div>
                <div style="font-size: 13px; color: var(--text-muted); font-weight: 500;" id="pagination_summary">
                    Affichage de {{ $mouvements->firstItem() }} à {{ $mouvements->lastItem() }} sur {{ $mouvements->total() }} résultats
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Filtre (Modal Backdrop layout) -->
<div id="filterModal" class="modal-backdrop" style="display: none;">
    <div class="modal-content" style="max-width: 600px; padding: 24px; border-radius: var(--radius-lg); transform: scale(0.95); transition: transform 0.3s;">
        <!-- Modal close button -->
        <button type="button" onclick="closeFilterModal()" class="modal-close" style="top: 20px; right: 20px;">×</button>
        
        <!-- Header title inside modal -->
        <div style="font-size: 18px; font-weight: 700; color: var(--text-main); margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary" style="color: var(--primary);">
                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
            </svg>
            Filtres des Mouvements Articles
        </div>
        
        <div class="modal-body-wrapper">
            <!-- Row 1 -->
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
            
            <!-- Row 2 -->
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

            <!-- Modal footer / actions -->
            <div class="filters-actions" style="margin-top: 24px; padding-top: 16px; display: flex; justify-content: flex-end; gap: 12px; border-top: 1px dashed var(--border);">
                <button type="button" class="btn btn-outline" onclick="closeFilterModal()">Annuler</button>
                <a href="{{ route('stock.mouvements.index') }}" class="btn btn-outline">Réinitialiser</a>
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
// Toggle collapsible groups of movements
function toggleGroup(className) {
    const rows = document.getElementsByClassName(className);
    const chevron = document.getElementById('icon-' + className);
    
    let isCollapsed = false;
    for(let i=0; i<rows.length; i++) {
        if (rows[i].style.display === 'none') {
            rows[i].style.display = '';
            isCollapsed = false;
        } else {
            rows[i].style.display = 'none';
            isCollapsed = true;
        }
    }
    
    // Rotate chevron arrow dynamically
    if (chevron) {
        if (isCollapsed) {
            chevron.style.transform = 'rotate(-90deg)';
        } else {
            chevron.style.transform = 'rotate(0deg)';
        }
    }
}

// Modal handling
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

// Click outside close trigger
document.getElementById('filterModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeFilterModal();
    }
});

document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const filterInputs = document.querySelectorAll('.filter-col');
    const filterForm = document.getElementById('filterForm');
    const tableBody = document.getElementById('tableBody');
    const paginationWrapper = document.getElementById('paginationWrapper');
    
    let debounceTimer;

    function fetchFilteredData(url = '{{ route("stock.mouvements.index") }}') {
        const params = new URLSearchParams();
        
        // Date filters
        const dateDu = document.querySelector('input[name="date_du"]');
        const dateAu = document.querySelector('input[name="date_au"]');
        if (dateDu && dateDu.value) params.append('date_du', dateDu.value);
        if (dateAu && dateAu.value) params.append('date_au', dateAu.value);

        // Modal category filters
        const famille = document.querySelector('select[name="familleid"]');
        const sousFamille = document.querySelector('select[name="sousfamilleid"]');
        const rayon = document.querySelector('select[name="rayonid"]');
        const saison = document.querySelector('select[name="saisonid"]');
        if (famille && famille.value) params.append('familleid', famille.value);
        if (sousFamille && sousFamille.value) params.append('sousfamilleid', sousFamille.value);
        if (rayon && rayon.value) params.append('rayonid', rayon.value);
        if (saison && saison.value) params.append('saisonid', saison.value);

        // Column filters
        filterInputs.forEach(input => {
            if (input.value) {
                params.append(input.getAttribute('data-col'), input.value);
            }
        });

        const fetchUrl = `${url}${url.includes('?') ? '&' : '?'}${params.toString()}`;
        
        // Show loading state
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
                if (document.getElementById('tot_label')) document.getElementById('tot_label').textContent = `Total (${data.totals.mouvements_count} Mouvements)`;
                if (document.getElementById('tot_achat')) document.getElementById('tot_achat').textContent = data.totals.achat;
                if (document.getElementById('tot_entrer')) document.getElementById('tot_entrer').textContent = data.totals.entrer;
                if (document.getElementById('tot_sortie')) document.getElementById('tot_sortie').textContent = data.totals.sortie;
                if (document.getElementById('tot_vente')) document.getElementById('tot_vente').textContent = data.totals.vente;
            }

            // Update KPIs
            if (data.kpis) {
                if (document.getElementById('kpi_total_mouvements')) document.getElementById('kpi_total_mouvements').textContent = data.kpis.total_mouvements;
                if (document.getElementById('kpi_total_achats')) document.getElementById('kpi_total_achats').textContent = data.kpis.total_achats;
                if (document.getElementById('kpi_total_ventes')) document.getElementById('kpi_total_ventes').textContent = data.kpis.total_ventes;
                if (document.getElementById('kpi_ajustements_net')) document.getElementById('kpi_ajustements_net').textContent = data.kpis.ajustements_net;
            }
        })
        .catch(err => {
            console.error('Error fetching movements:', err);
            tableBody.style.opacity = '1';
        });
    }

    function handleInput() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => fetchFilteredData(), 400);
    }

    // Attach search input listeners
    filterInputs.forEach(input => {
        input.addEventListener('input', handleInput);
        input.addEventListener('change', handleInput);
    });

    // Attach inline date changes
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        input.addEventListener('change', handleInput);
    });

    // Prevent default form submission and filter dynamically
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
