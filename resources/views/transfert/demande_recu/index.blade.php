@extends('layouts.app')

@section('title', 'Pointage Demandes de Transferts Reçues Velaro')

@section('content')
<div class="pos-container">
    
    <!-- Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Pointage Demandes Reçues</h1>
            <p class="page-subtitle">Pointez et validez les transferts de stock reçus.</p>
        </div>
        <div class="header-actions">
            <!-- Action principale -->
            <button type="submit" form="pointageForm" class="btn btn-primary" title="Enregistrer le pointage">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                    <polyline points="7 3 7 8 15 8"></polyline>
                </svg>
                Enregistrer Pointage
            </button>
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
                <span class="kpi-label">Total Lignes</span>
                <span class="kpi-value" id="kpi-total">{{ number_format($totalLignes ?? 0, 0, ',', ' ') }}</span>
            </div>
        </div>
        
        <div class="kpi-card">
            <div class="kpi-icon-wrapper bg-blue-light">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--info)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </div>
            <div class="kpi-info">
                <span class="kpi-label">À Traiter</span>
                <span class="kpi-value text-info" id="kpi-atraiter">{{ number_format($aTraiter ?? 0, 0, ',', ' ') }}</span>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon-wrapper bg-green-light">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <div class="kpi-info">
                <span class="kpi-label">Validées</span>
                <span class="kpi-value text-success" id="kpi-validees">{{ number_format($validees ?? 0, 0, ',', ' ') }}</span>
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
                <input type="text" id="globalSearch" name="search" class="search-input" placeholder="Recherche rapide (N°, Demandeur, Réf)..." value="{{ request('search') }}">
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
        <form method="POST" action="{{ route('transfert.demande_recu.pointer') }}" id="pointageForm">
            @csrf
            <div class="table-responsive">
                <table class="data-table" id="dataTable">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">
                                <label class="checkbox-wrapper">
                                    <input type="checkbox" id="selectAll">
                                    <span class="checkmark"></span>
                                </label>
                            </th>
                            <th style="width: 10%;">Numéro</th>
                            <th style="width: 90px;">Date</th>
                            <th style="width: 12%;">Demandeur</th>
                            <th style="width: 12%;">Référence</th>
                            <th style="width: 8%;">Couleur</th>
                            <th style="width: 8%;">Taille</th>
                            <th style="width: 70px; text-align: center;">Dmdé</th>
                            <th style="width: 70px; text-align: center;">Stk</th>
                            <th style="width: 10%;">Etat</th>
                            <th style="width: 12%;">Observation</th>
                            <th style="width: 90px; text-align: center;">Qté Validée</th>
                        </tr>
                        <tr class="filter-row">
                            <th></th>
                            <th><input type="text" class="filter-col" data-col="f_numero" placeholder="Filtrer..."></th>
                            <th><input type="date" class="filter-col" data-col="f_date" title="Filtrer par date"></th>
                            <th><input type="text" class="filter-col" data-col="f_demandeur" placeholder="Filtrer..."></th>
                            <th><input type="text" class="filter-col" data-col="f_reference" placeholder="Filtrer..."></th>
                            <th><input type="text" class="filter-col" data-col="f_couleur" placeholder="Filtrer..."></th>
                            <th><input type="text" class="filter-col" data-col="f_taille" placeholder="Filtrer..."></th>
                            <th><input type="text" class="filter-col" data-col="f_qte_demandee" placeholder="Qté"></th>
                            <th><input type="text" class="filter-col" data-col="f_stock" placeholder="Stk"></th>
                            <th><input type="text" class="filter-col" data-col="f_etat" placeholder="Filtrer..."></th>
                            <th><input type="text" class="filter-col" data-col="f_cause" placeholder="Filtrer..."></th>
                            <th><input type="text" class="filter-col" data-col="f_qte_validee" placeholder="Qté V."></th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        @include('transfert.demande_recu.partials.table', ['demandes' => $demandes])
                    </tbody>
                </table>
            </div>
        </form>

        <!-- Pagination -->
        <div class="pagination-wrapper" id="paginationWrapper">
            {{ $demandes->appends(request()->all())->links('pagination::bootstrap-4') }}
        </div>

    </div>
</div>
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
        const selectAllCheckbox = document.getElementById('selectAll');

        let debounceTimer;

        function fetchFilteredData(url = '{{ route("transfert.demande_recu.index") }}') {
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
                attachCheckboxEvents();

                // Update KPI Cards dynamically
                if (data.kpis) {
                    if(document.getElementById('kpi-total')) document.getElementById('kpi-total').textContent = data.kpis.total;
                    if(document.getElementById('kpi-atraiter')) document.getElementById('kpi-atraiter').textContent = data.kpis.atraiter;
                    if(document.getElementById('kpi-validees')) document.getElementById('kpi-validees').textContent = data.kpis.validees;
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
                if ($('#filter_etat').length) {
                    $('#filter_etat').val('tous').trigger('change.select2');
                }

                fetchFilteredData();
            });
        }

        // Initialize Select2
        if (typeof $ !== 'undefined') {
            $('.filter-dropdown').each(function() {
                $(this).select2({
                    placeholder: "Sélectionner...",
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
        
        function attachCheckboxEvents() {
            if(selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    const checkboxes = document.querySelectorAll('input[name="selected_demandes[]"]');
                    checkboxes.forEach(cb => {
                        cb.checked = selectAllCheckbox.checked;
                    });
                });
            }
        }
        
        attachPaginationEvents();
        attachCheckboxEvents();
    });
</script>
@endsection
