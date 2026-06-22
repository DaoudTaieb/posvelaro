@extends('layouts.app')
@section('title', 'Calcul Commissions')

@section('content')
<div class="pos-container">
    
    <!-- Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Calcul Commissions</h1>
            <p class="page-subtitle">Analysez les commissions de vos vendeurs, suivez les performances et gérez les rémunérations.</p>
        </div>
        <div class="header-actions">
            <button class="btn btn-outline" id="btnExport">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                Exporter
            </button>
            <button class="btn btn-primary" onclick="window.location.reload()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="23 4 23 10 17 10"></polyline>
                    <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                </svg>
                Actualiser
            </button>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon-wrapper bg-indigo-light">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
            </div>
            <div class="kpi-info">
                <span class="kpi-label">Nombre de Ventes</span>
                <span class="kpi-value" id="kpi_nb_ventes">{{ $kpis['nb_ventes'] ?? 0 }}</span>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon-wrapper bg-blue-light">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--info)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="1" x2="12" y2="23"></line>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
            </div>
            <div class="kpi-info">
                <span class="kpi-label">CA Net HT Total</span>
                <span class="kpi-value text-info" id="kpi_net_ht">{{ $kpis['net_ht'] ?? '0' }}</span>
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
                <span class="kpi-label">Total Commissions</span>
                <span class="kpi-value text-success" id="kpi_commission">{{ $kpis['commission'] ?? '0' }}</span>
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
                <input type="text" id="globalSearch" class="search-input" placeholder="Recherche rapide (N°, Vendeur...)">
            </div>
        </div>

        <!-- Advanced Filters (always open) -->
        <div class="advanced-filters show" id="advancedFilters" style="display: block;">
            <div class="filters-grid">
                <div class="form-group">
                    <label class="form-label">Date Début</label>
                    <input type="date" class="form-control filter-date" name="date_du" id="filter_date_du" value="{{ request('date_du') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Date Fin</label>
                    <input type="date" class="form-control filter-date" name="date_au" id="filter_date_au" value="{{ request('date_au') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Vendeur</label>
                    <select class="form-control filter-dropdown" name="vendeur" id="filter_vendeur">
                        <option value=""></option>
                        @foreach($vendeurs as $v)
                            <option value="{{ $v->employeeid }}" {{ request('vendeur') == $v->employeeid ? 'selected' : '' }}>{{ $v->nom }} {{ $v->prenom }}</option>
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
            <table class="data-table" id="commissionsTable">
                <thead>
                    <tr>
                        <th style="width: 90px;">Date</th>
                        <th>Numéro</th>
                        <th>Vendeur</th>
                        <th class="text-right">CA Net HT</th>
                        <th class="text-right">% Commission</th>
                        <th class="text-right">Montant Commission</th>
                    </tr>
                    <tr class="filter-row">
                        <th><input type="date" class="filter-col" data-col="f_date" title="Filtrer par date"></th>
                        <th><input type="text" class="filter-col" data-col="f_numero" placeholder="Filtrer..."></th>
                        <th><input type="text" class="filter-col" data-col="f_vendeur" placeholder="Filtrer..."></th>
                        <th><input type="text" class="filter-col text-right" data-col="f_net_ht" placeholder="..."></th>
                        <th><input type="text" class="filter-col text-right" data-col="f_taux" placeholder="..."></th>
                        <th><input type="text" class="filter-col text-right" data-col="f_montant" placeholder="..."></th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @include('vente.commissions.partials.table_body')
                </tbody>
                <tfoot>
                    <tr class="table-totals">
                        <td colspan="3" class="totals-label">Totaux</td>
                        <td class="amount-cell font-bold text-primary" id="tot_netht">{{ $totals['net_ht'] }}</td>
                        <td class="amount-cell" style="background: #f1f5f9;"></td>
                        <td class="amount-cell font-bold text-success" id="tot_commission">{{ $totals['commission'] }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-wrapper" id="paginationWrapper">
            {{ $commissions->links() }}
        </div>
    </div>
</div>

@endsection

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

    :root {
        /* Colors - Indigo/Purple Theme */
        --primary: #4f46e5;
        --primary-hover: #4338ca;
        --primary-light: #e0e7ff;
        
        --surface: #ffffff;
        --background: #f8fafc;
        
        --text-main: #0f172a;
        --text-muted: #64748b;
        
        --border: #e2e8f0;
        --border-hover: #cbd5e1;
        --border-focus: #a5b4fc;
        
        /* Status Colors */
        --success: #10b981;
        --success-bg: #d1fae5;
        --danger: #ef4444;
        --danger-bg: #fee2e2;
        --warning: #f59e0b;
        --warning-bg: #fef3c7;
        --info: #0ea5e9;
        --info-bg: #e0f2fe;
        --purple: #8b5cf6;
        --purple-bg: #ede9fe;
        --default: #64748b;
        --default-bg: #f1f5f9;

        /* Radius & Shadows */
        --radius-lg: 12px;
        --radius-md: 8px;
        --radius-sm: 6px;
        
        --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    body {
        font-family: 'Inter', sans-serif;
        background-color: var(--background);
        color: var(--text-main);
    }

    /* Layout */
    .pos-container {
        padding: 24px;
        max-width: 1600px;
        margin: 0 auto;
    }

    /* Header */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 24px;
    }
    .page-title {
        font-size: 24px;
        font-weight: 700;
        color: var(--text-main);
        margin: 0 0 4px 0;
        letter-spacing: -0.025em;
    }
    .page-subtitle {
        font-size: 14px;
        color: var(--text-muted);
        margin: 0;
    }
    .header-actions {
        display: flex;
        gap: 12px;
    }

    /* KPI Cards */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 24px;
        margin-bottom: 24px;
    }
    .kpi-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: var(--shadow-sm);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }
    .kpi-icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .bg-indigo-light { background: var(--primary-light); }
    .bg-blue-light { background: var(--info-bg); }
    .bg-green-light { background: var(--success-bg); }
    
    .kpi-info {
        display: flex;
        flex-direction: column;
    }
    .kpi-label {
        font-size: 13px;
        font-weight: 500;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 4px;
    }
    .kpi-value {
        font-size: 24px;
        font-weight: 700;
        color: var(--text-main);
        line-height: 1.2;
    }
    .text-success { color: var(--success); }
    .text-danger { color: var(--danger); }
    .text-info { color: var(--info); }
    .text-primary { color: var(--primary); }
    .text-warning { color: var(--warning); }
    .text-muted { color: var(--text-muted) !important; }

    /* Content Card */
    .content-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }

    /* Toolbar */
    .toolbar {
        padding: 16px 24px;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: var(--surface);
    }
    .search-wrapper {
        position: relative;
        width: 360px;
    }
    .search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
    }
    .search-input {
        width: 100%;
        padding: 10px 16px 10px 40px;
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        font-size: 14px;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
        font-family: inherit;
    }
    .search-input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-light);
    }

    /* Buttons */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 16px;
        border-radius: var(--radius-md);
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        border: 1px solid transparent;
        font-family: inherit;
    }
    .btn-primary {
        background: var(--primary);
        color: white;
    }
    .btn-primary:hover {
        background: var(--primary-hover);
        box-shadow: var(--shadow-sm);
    }
    .btn-outline {
        background: var(--surface);
        border-color: var(--border);
        color: var(--text-main);
    }
    .btn-outline:hover {
        background: var(--background);
        border-color: var(--border-hover);
    }

    /* Advanced Filters */
    .advanced-filters {
        background: #fdfdfe;
        border-bottom: 1px solid var(--border);
        padding: 24px;
        display: none;
    }
    .advanced-filters.show {
        display: block;
        animation: slideDown 0.3s ease-out;
    }
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .filters-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        align-items: flex-end;
    }
    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .form-label {
        font-size: 13px;
        font-weight: 500;
        color: var(--text-main);
    }
    .form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        font-size: 13px;
        outline: none;
        transition: all 0.2s;
        font-family: inherit;
        background: var(--surface);
    }
    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-light);
    }
    .filters-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 20px;
        padding-top: 16px;
        border-top: 1px dashed var(--border);
    }
    .btn-reset {
        background: transparent;
        border-color: var(--border);
        color: var(--text-muted);
    }
    .btn-reset:hover {
        background: #fef2f2 !important;
        border-color: #fca5a5 !important;
        color: #ef4444 !important;
    }
    .btn-apply {
        background: var(--primary) !important;
        color: white !important;
    }
    .btn-apply:hover {
        background: var(--primary-hover) !important;
        box-shadow: var(--shadow-sm);
    }

    /* Table */
    .table-responsive {
        width: 100%;
        overflow-x: auto;
        min-height: 400px;
    }
    .data-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        text-align: left;
        font-size: 13px;
    }
    .data-table th, .data-table td {
        padding: 14px 16px;
        border-bottom: 1px solid var(--border);
        white-space: nowrap;
        vertical-align: middle;
    }
    .data-table thead th {
        background: var(--background);
        color: var(--text-muted);
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    .data-table tbody tr {
        transition: background-color 0.15s ease;
    }
    .data-table tbody tr:hover {
        background-color: var(--background);
    }
    .filter-row th {
        padding: 8px 16px;
        background: var(--surface);
        border-bottom: 2px solid var(--border);
        top: 45px;
        z-index: 9;
    }
    .filter-col {
        width: 100%;
        padding: 8px 10px;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        font-size: 12px;
        outline: none;
        transition: border-color 0.2s;
        background: var(--background);
    }
    .filter-col:focus {
        border-color: var(--primary);
        background: var(--surface);
        box-shadow: 0 0 0 2px var(--primary-light);
    }
    
    /* Typography Utilities */
    .font-medium { font-weight: 500; }
    .font-bold { font-weight: 600; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .amount-cell {
        font-family: 'Inter', sans-serif;
        font-variant-numeric: tabular-nums;
        text-align: right;
    }

    /* Totals Footer */
    .table-totals {
        background: var(--background);
    }
    .table-totals td {
        border-top: 2px solid var(--border);
        font-weight: 600;
    }
    .totals-label {
        text-align: right;
        color: var(--text-main);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-size: 12px;
    }

    /* Pagination */
    .pagination-wrapper {
        padding: 16px 24px;
        background: var(--surface);
        border-top: 1px solid var(--border);
    }
    .pagination-wrapper nav {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
    }
    .pagination-wrapper .d-sm-none {
        display: flex !important;
        justify-content: space-between;
        width: 100%;
    }
    .pagination-wrapper .d-none.d-sm-flex {
        display: none !important;
    }
    .pagination-wrapper ul.pagination {
        display: flex;
        list-style: none;
        padding: 0;
        margin: 0;
        gap: 6px;
    }
    .pagination-wrapper .page-item {
        margin: 0;
    }
    .pagination-wrapper .page-link,
    .pagination-wrapper .page-item span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
        padding: 0 14px;
        border-radius: var(--radius-md);
        border: 1px solid var(--border);
        background: var(--surface);
        color: var(--text-muted);
        font-size: 13px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s;
        cursor: pointer;
    }
    .pagination-wrapper .page-item a.page-link:hover {
        background: var(--background);
        border-color: var(--border-hover);
        color: var(--primary);
    }
    .pagination-wrapper .page-item.active .page-link,
    .pagination-wrapper .page-item.active span {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }
    .pagination-wrapper .page-item.disabled .page-link,
    .pagination-wrapper .page-item.disabled span {
        background: var(--background);
        color: #cbd5e1;
        border-color: var(--border);
        cursor: not-allowed;
    }
    .pagination-wrapper p {
        color: var(--text-muted);
        font-size: 13px;
        margin: 0;
    }
    
    @media (min-width: 576px) {
        .pagination-wrapper .d-sm-none {
            display: none !important;
        }
        .pagination-wrapper .d-none.d-sm-flex {
            display: flex !important;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }
    }

    /* Select2 Customization */
    .select2-container--default .select2-selection--single {
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        height: 40px;
        display: flex;
        align-items: center;
        background: var(--surface);
    }
    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-light);
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 38px;
        right: 8px;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: var(--text-main);
        font-size: 13px;
        padding-left: 12px;
    }
    .select2-dropdown {
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-lg);
        font-size: 13px;
        overflow: hidden;
    }
    .select2-search__field {
        border-radius: var(--radius-sm) !important;
        border: 1px solid var(--border) !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__clear {
        height: 38px;
        line-height: 38px;
        margin-right: 12px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .pos-container { padding: 12px; }
        .toolbar { flex-direction: column; gap: 12px; align-items: stretch; }
        .search-wrapper { width: 100%; }
        .page-header { flex-direction: column; gap: 16px; }
        .header-actions { width: 100%; }
        .header-actions .btn { flex: 1; }
    }
</style>
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

        function fetchFilteredData(url = '{{ route("vente.commissions.index") }}') {
            const params = new URLSearchParams();
            
            // Collect global search
            if (globalSearch.value) params.append('q', globalSearch.value);
            
            // Collect main filters
            dropdownFilters.forEach(input => {
                if (input.value) params.append(input.name, input.value);
            });

            // Collect column filters
            filterInputs.forEach(input => {
                if (input.value && !input.disabled) params.append(input.getAttribute('data-col'), input.value);
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
                if(paginationWrapper) paginationWrapper.innerHTML = data.pagination;
                attachPaginationEvents();

                // Update Totals Footer
                if (data.totals) {
                    if(document.getElementById('tot_netht')) document.getElementById('tot_netht').textContent = data.totals.net_ht;
                    if(document.getElementById('tot_commission')) document.getElementById('tot_commission').textContent = data.totals.commission;
                }

                // Update KPI Cards dynamically
                if (data.kpis) {
                    if(document.getElementById('kpi_nb_ventes')) document.getElementById('kpi_nb_ventes').textContent = data.kpis.nb_ventes;
                    if(document.getElementById('kpi_net_ht')) document.getElementById('kpi_net_ht').textContent = data.kpis.net_ht;
                    if(document.getElementById('kpi_commission')) document.getElementById('kpi_commission').textContent = data.kpis.commission;
                }
            })
            .catch(err => {
                console.error('Error fetching commissions:', err);
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
                dropdownFilters.forEach(input => input.value = '');
                
                // Reset select2
                if ($('#filter_vendeur').length) {
                    $('#filter_vendeur').val('').trigger('change.select2');
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

        // Client-side Export to CSV
        const btnExport = document.getElementById('btnExport');
        if (btnExport) {
            btnExport.addEventListener('click', function() {
                // Header columns
                const headers = [];
                document.querySelectorAll('#commissionsTable thead tr:first-of-type th').forEach(th => {
                    headers.push(th.textContent.trim());
                });
                
                // Rows data
                const rows = [];
                document.querySelectorAll('#commissionsTable tbody tr').forEach(tr => {
                    if (tr.querySelector('.empty-state') !== null) return;
                    const cells = [];
                    tr.querySelectorAll('td').forEach(td => {
                        let text = td.textContent.trim().replace(/\s+/g, ' ');
                        cells.push(text);
                    });
                    if (cells.length > 0) {
                        rows.push(cells);
                    }
                });

                if (rows.length === 0) {
                    alert('Aucune donnée à exporter.');
                    return;
                }

                // Build CSV content with UTF-8 BOM
                let csvContent = "\uFEFF";
                csvContent += headers.join(';') + '\n';
                rows.forEach(row => {
                    const rowData = row.map(val => {
                        let cleanVal = val.replace(/"/g, '""');
                        if (cleanVal.includes(';') || cleanVal.includes('\n')) {
                            cleanVal = `"${cleanVal}"`;
                        }
                        return cleanVal;
                    });
                    csvContent += rowData.join(';') + '\n';
                });

                // Download trigger
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const url = URL.createObjectURL(blob);
                const link = document.createElement("a");
                link.setAttribute("href", url);
                link.setAttribute("download", `export_commissions_${new Date().toISOString().slice(0,10)}.csv`);
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
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
