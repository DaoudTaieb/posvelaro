@extends('layouts.app')

@section('title', 'Consultation Bons de Transferts Envoyés Golden Pos')

@section('content')
<div class="pos-container">
    
    <!-- Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Consultation Bons de Transferts</h1>
            <p class="page-subtitle">Gérez et suivez vos transferts de stock envoyés vers d'autres sites.</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('transfert.envoye.create') }}" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Nouveau
            </a>
            
            <a href="{{ route('transfert.envoye.impression_multiple') }}" class="btn btn-outline">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <rect x="6" y="14" width="12" height="8"></rect>
                </svg>
                Impression Multiple
            </a>

            <button class="btn btn-outline" onclick="window.history.back()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
                Fermer
            </button>
        </div>
    </div>

    <!-- KPI Cards -->
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
                <span class="kpi-label">Total Bons</span>
                <span class="kpi-value" id="kpi-total">{{ number_format($totalBons ?? 0, 0, ',', ' ') }}</span>
            </div>
        </div>
        
        <div class="kpi-card">
            <div class="kpi-icon-wrapper bg-blue-light">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--info)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
            </div>
            <div class="kpi-info">
                <span class="kpi-label">Brouillons</span>
                <span class="kpi-value text-info" id="kpi-brouillon">{{ number_format($brouillons ?? 0, 0, ',', ' ') }}</span>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon-wrapper bg-green-light">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="22" y1="2" x2="11" y2="13"></line>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                </svg>
            </div>
            <div class="kpi-info">
                <span class="kpi-label">Envoyés</span>
                <span class="kpi-value text-success" id="kpi-envoye">{{ number_format($envoyes ?? 0, 0, ',', ' ') }}</span>
            </div>
        </div>
    </div>

    <!-- Main Content Card -->
    <div class="content-card">
        
        <!-- Toolbar -->
        <div class="toolbar">
            <div class="search-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="search-icon">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" id="globalSearch" name="search" class="search-input" placeholder="Recherche rapide (N°, Récepteur, Description)..." value="{{ request('search') }}">
            </div>
        </div>

        <!-- Advanced Filters -->
        <div class="advanced-filters show" id="advancedFilters" style="display: block;">
            <div class="filters-grid">
                <div class="form-group">
                    <label class="form-label">Date Début</label>
                    <input type="date" class="form-control filter-date" name="datedebut" id="filter_date_du" value="{{ $defaultDateDebut }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Date Fin</label>
                    <input type="date" class="form-control filter-date" name="datefin" id="filter_date_au" value="{{ $defaultDateFin }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Récepteur</label>
                    <select class="form-control filter-dropdown" name="siterecepteurid" id="filter_recepteur">
                        <option value=""></option>
                        @foreach($sites as $s)
                            <option value="{{ $s->siteid }}" {{ request('siterecepteurid') == $s->siteid ? 'selected' : '' }}>
                                {{ $s->libelle }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Etat</label>
                    <select class="form-control filter-dropdown" name="etatid" id="filter_etat">
                        <option value="tous">Tous les états</option>
                        @foreach($etats as $etat)
                            <option value="{{ $etat->etatdemandetransfertid }}" {{ request('etatid') == $etat->etatdemandetransfertid ? 'selected' : '' }}>
                                {{ $etat->libelle }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>
            <div class="filters-actions">
                <button type="button" class="btn btn-outline btn-reset" id="btnClear">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M23 4v6h-6"></path>
                        <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                    </svg>
                    Réinitialiser
                </button>
                <button type="button" class="btn btn-primary btn-apply" id="btnFilter">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    Appliquer
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="data-table" id="dataTable">
                <thead>
                    <tr>
                        <th style="width: 12%;">Numéro</th>
                        <th style="width: 100px;">Date</th>
                        <th style="width: 15%;">Récepteur</th>
                        <th style="width: 80px; text-align: center;">Qté</th>
                        <th style="width: 15%;">Trajet</th>
                        <th>Observation</th>
                        <th style="width: 10%;">Etat</th>
                        <th style="width: 80px; text-align: center;">Actions</th>
                    </tr>
                    <tr class="filter-row">
                        <th><input type="text" class="filter-col" data-col="f_numero" placeholder="Filtrer..."></th>
                        <th><input type="date" class="filter-col" data-col="f_date" title="Filtrer par date"></th>
                        <th><input type="text" class="filter-col" data-col="f_recepteur" placeholder="Filtrer..."></th>
                        <th><input type="text" class="filter-col" data-col="f_qte" placeholder="Qté"></th>
                        <th><input type="text" class="filter-col" data-col="f_trajet" placeholder="Filtrer..."></th>
                        <th><input type="text" class="filter-col" data-col="f_description" placeholder="Filtrer..."></th>
                        <th><input type="text" class="filter-col" data-col="f_etat" placeholder="Filtrer..."></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @include('transfert.envoye.partials.table', ['bontransferts' => $bontransferts])
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-wrapper" id="paginationWrapper">
            {{ $bontransferts->appends(request()->all())->links('pagination::bootstrap-4') }}
        </div>

    </div>
</div>

<style>
    /* Quick styles for inline dropdowns if needed, though usually handled by JS or a class in Golden Pos */
    .dropdown-menu.show { display: block !important; }
</style>
@endsection

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Elements
        const globalSearch = document.getElementById('globalSearch');
        const filterInputs = document.querySelectorAll('.filter-col');
        const dropdownFilters = document.querySelectorAll('.filter-dropdown, .filter-date');

        const btnFilter = document.getElementById('btnFilter');
        const tableBody = document.getElementById('tableBody');
        const paginationWrapper = document.getElementById('paginationWrapper');

        let debounceTimer;

        function fetchFilteredData(url = '{{ route("transfert.envoye.index") }}') {
            const params = new URLSearchParams();
            
            // Collect global search
            if (globalSearch.value) params.append('search', globalSearch.value);
            
            // Collect main filters
            dropdownFilters.forEach(input => {
                if (input.value && input.value !== 'tous') params.append(input.name, input.value);
            });

            // Collect column filters
            filterInputs.forEach(input => {
                if (input.value && !input.disabled) params.append(input.getAttribute('data-col'), input.value);
            });
            


            const fetchUrl = `${url.split('?')[0]}?${params.toString()}`;

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
                if(paginationWrapper) paginationWrapper.innerHTML = data.pagination;
                attachPaginationEvents();

                // Update KPI Cards dynamically
                if (data.kpis) {
                    if(document.getElementById('kpi-total')) document.getElementById('kpi-total').textContent = data.kpis.totalBons;
                    if(document.getElementById('kpi-brouillon')) document.getElementById('kpi-brouillon').textContent = data.kpis.brouillons;
                    if(document.getElementById('kpi-envoye')) document.getElementById('kpi-envoye').textContent = data.kpis.envoyes;
                }
            })
            .catch(err => {
                console.error('Error fetching data:', err);
                tableBody.style.opacity = '1';
            });
        }

        function handleInput() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => fetchFilteredData(), 400);
        }

        // Attach events
        globalSearch.addEventListener('input', handleInput);

        filterInputs.forEach(input => {
            if (!input.disabled) {
                input.addEventListener('input', handleInput);
                input.addEventListener('change', handleInput);
            }
        });
        
        btnFilter.addEventListener('click', fetchFilteredData);

        // Clear Filters logic
        const btnClear = document.getElementById('btnClear');
        if (btnClear) {
            btnClear.addEventListener('click', function() {
                globalSearch.value = '';
                filterInputs.forEach(input => input.value = '');
                
                // Keep default dates or clear them
                document.getElementById('filter_date_du').value = '';
                document.getElementById('filter_date_au').value = '';
                
                // Reset select2
                if ($('#filter_recepteur').length) {
                    $('#filter_recepteur').val('').trigger('change.select2');
                }
                if ($('#filter_etat').length) {
                    $('#filter_etat').val('tous').trigger('change.select2');
                }

                fetchFilteredData();
            });
        }

        // Initialize Select2
        if (typeof $ !== 'undefined') {
            $('.filter-dropdown').each(function() {
                const placeholder = $(this).attr('name') === 'siterecepteurid' ? "Rechercher un récepteur..." : "Sélectionner...";
                $(this).select2({
                    placeholder: placeholder,
                    allowClear: true,
                    width: '100%'
                }).on('select2:select select2:unselect', handleInput);
            });
        }

        // Handle enter and change on main filters
        dropdownFilters.forEach(input => {
            if(input.tagName === 'INPUT' && input.type === 'date') {
                input.addEventListener('change', handleInput);
            }
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') fetchFilteredData();
            });
        });

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

    // Close dropdowns if clicked outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.action-dropdown')) {
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                menu.classList.remove('show');
            });
        }
    });
</script>
@endsection
