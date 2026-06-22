<?php

$old_content = file_get_contents('resources/views/vente/clients/index.blade.php');

// Extract Modals
$modal_start = strpos($old_content, '<!-- Modal Nouveau Client -->');
$modal_end = strpos($old_content, '@endsection', $modal_start);
$modals = '';
if ($modal_start !== false && $modal_end !== false) {
    $modals = substr($old_content, $modal_start, $modal_end - $modal_start);
} else {
    echo "Warning: Modals not found!\n";
}

// Extract Modals CSS
$modal_css_start = strpos($old_content, '/* Modal Styles */');
$modal_css_end = strpos($old_content, '/* Form Styles */', $modal_css_start);
if ($modal_css_end === false) {
    $modal_css_end = strpos($old_content, '/* Global Search */', $modal_css_start);
}

$modal_css = '';
if ($modal_css_start !== false && $modal_css_end !== false) {
    // Also extract Form styles
    $form_css_start = strpos($old_content, '/* Form Styles */');
    $form_css_end = strpos($old_content, '/* Action Buttons */', $form_css_start);
    if ($form_css_end === false) {
        $form_css_end = strpos($old_content, '/* Global Search */', $form_css_start);
    }
    
    $action_css_start = strpos($old_content, '/* Action Buttons */');
    $action_css_end = strpos($old_content, '/* Global Search */', $action_css_start);

    $modal_css = substr($old_content, $modal_css_start, $modal_css_end - $modal_css_start) . "\n" . 
                 substr($old_content, $form_css_start, $form_css_end - $form_css_start) . "\n" . 
                 substr($old_content, $action_css_start, $action_css_end - $action_css_start);
}

$new_content = <<<HTML
@extends('layouts.app')

@section('title', 'Consultation des Clients')

@section('content')
<div class="pos-container">
    
    <!-- Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Consultation des Clients</h1>
            <p class="page-subtitle">Gérez votre base clients, vos encours et vos statistiques.</p>
        </div>
        <div class="header-actions">
            <button type="button" class="btn btn-primary" id="btnNewClient">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Nouveau Client
            </button>
            <button class="btn btn-outline" onclick="window.location.reload()">
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
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <div class="kpi-info">
                <span class="kpi-label">Nombre de Clients</span>
                <span class="kpi-value" id="kpi_nb_clients">{{ \$kpis['nb_clients'] ?? 0 }}</span>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon-wrapper bg-red-light">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--danger)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="1" x2="12" y2="23"></line>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
            </div>
            <div class="kpi-info">
                <span class="kpi-label">Total Solde</span>
                <span class="kpi-value text-danger" id="kpi_total_solde">{{ \$kpis['total_solde'] ?? '0' }}</span>
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
                <input type="text" id="globalSearch" class="search-input" placeholder="Recherche rapide (Nom, Code, Ville...)">
            </div>
            <div class="header-actions">
                <button type="button" class="btn btn-outline btn-reset" id="btnClear">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M23 4v6h-6"></path>
                        <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                    </svg>
                    Réinitialiser
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="data-table" id="clientsTable">
                <thead>
                    <tr>
                        <th style="width: 80px;">Actions</th>
                        <th>Code</th>
                        <th>Raison Social</th>
                        <th>Tarif</th>
                        <th class="text-center">Crédit</th>
                        <th>% Rem</th>
                        <th class="text-center">Fidélité</th>
                        <th>Code TVA</th>
                        <th class="text-right">Solde</th>
                        <th class="text-right">Solde Dép.</th>
                        <th>Date S.D.</th>
                        <th>Ville</th>
                        <th>Adresse Fact.</th>
                        <th>Adresse Liv.</th>
                        <th>Téléphone</th>
                        <th>R.Commerce</th>
                        <th>Fax</th>
                        <th>Email</th>
                        <th>ID</th>
                        <th class="text-right">Solde Fidélité</th>
                        <th class="text-right">Cumul Fidélité</th>
                        <th class="text-right">Point Fidélité</th>
                    </tr>
                    <tr class="filter-row">
                        <th></th>
                        <th><input type="text" class="filter-col" data-col="f_code" placeholder="Filtrer..."></th>
                        <th><input type="text" class="filter-col" data-col="f_nom" placeholder="Filtrer..."></th>
                        <th><input type="text" class="filter-col" data-col="f_tarif" placeholder="Filtrer..."></th>
                        <th>
                            <select class="filter-col" data-col="f_credit">
                                <option value=""></option>
                                <option value="actif">Actif</option>
                                <option value="bloqué">Bloqué</option>
                            </select>
                        </th>
                        <th><input type="text" class="filter-col" data-col="f_remise" placeholder="..."></th>
                        <th>
                            <select class="filter-col" data-col="f_fidelite">
                                <option value=""></option>
                                <option value="oui">Oui</option>
                                <option value="non">Non</option>
                            </select>
                        </th>
                        <th><input type="text" class="filter-col" data-col="f_tva" placeholder="Filtrer..."></th>
                        <th><input type="text" class="filter-col text-right" data-col="f_solde" placeholder="..."></th>
                        <th><input type="text" class="filter-col text-right" data-col="f_soldedep" placeholder="..."></th>
                        <th><input type="date" class="filter-col" data-col="f_datesd" placeholder="..."></th>
                        <th><input type="text" class="filter-col" data-col="f_ville" placeholder="Filtrer..."></th>
                        <th><input type="text" class="filter-col" data-col="f_adrfact" placeholder="Filtrer..."></th>
                        <th><input type="text" class="filter-col" data-col="f_adrliv" placeholder="Filtrer..."></th>
                        <th><input type="text" class="filter-col" data-col="f_tel" placeholder="Filtrer..."></th>
                        <th><input type="text" class="filter-col" data-col="f_rc" placeholder="Filtrer..."></th>
                        <th><input type="text" class="filter-col" data-col="f_fax" placeholder="Filtrer..."></th>
                        <th><input type="text" class="filter-col" data-col="f_email" placeholder="Filtrer..."></th>
                        <th><input type="text" class="filter-col" data-col="f_id" placeholder="..."></th>
                        <th><input type="text" class="filter-col text-right" data-col="f_soldefid" placeholder="..."></th>
                        <th><input type="text" class="filter-col text-right" data-col="f_cumulfid" placeholder="..."></th>
                        <th><input type="text" class="filter-col text-right" data-col="f_pointfid" placeholder="..."></th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @include('vente.clients.partials.table_body')
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-wrapper" id="paginationWrapper">
            {{ \$clients->links() }}
        </div>
    </div>
</div>

{\$modals}
@endsection

@section('styles')
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
        --danger-border: #f87171;
        --warning: #f59e0b;
        --warning-bg: #fef3c7;
        --info: #0ea5e9;
        --info-bg: #e0f2fe;
        --purple: #8b5cf6;
        --purple-bg: #ede9fe;

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
        max-width: 100%;
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
    .bg-red-light { background: var(--danger-bg); }
    
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
    .text-danger { color: var(--danger); }

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
        background: var(--background);
    }
    .filter-row th {
        padding: 8px 16px;
        background: var(--surface);
        border-bottom: 2px solid var(--border);
        top: 45px; /* Offset for sticky header */
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
    select.filter-col {
        padding-right: 20px;
    }
    
    /* Typography Utilities */
    .font-medium { font-weight: 500; }
    .font-bold { font-weight: 600; }
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .truncate-text {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .amount-cell {
        font-family: 'Inter', sans-serif;
        font-variant-numeric: tabular-nums;
        text-align: right;
    }

    /* Badges */
    .badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
        background: #f1f5f9;
        color: var(--text-muted);
    }
    .badge-success {
        background: var(--success-bg);
        color: var(--success);
    }
    .badge-danger {
        background: var(--danger-bg);
        color: var(--danger);
    }
    .badge-secondary {
        background: var(--default-bg);
        color: var(--text-main);
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

{\$modal_css}
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Elements
        const globalSearch = document.getElementById('globalSearch');
        const filterInputs = document.querySelectorAll('.filter-col');
        const tableBody = document.getElementById('tableBody');
        const paginationWrapper = document.getElementById('paginationWrapper');
        const btnClear = document.getElementById('btnClear');

        let debounceTimer;

        function fetchFilteredData(url = '{{ route("vente.clients.index") }}') {
            const params = new URLSearchParams();
            
            // Collect global search
            if (globalSearch && globalSearch.value) params.append('q', globalSearch.value);
            
            // Collect column filters
            filterInputs.forEach(input => {
                if (input.value && !input.disabled) params.append(input.getAttribute('data-col'), input.value);
            });

            const fetchUrl = `\${url}\${url.includes('?') ? '&' : '?'}\${params.toString()}`;

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

                // Update KPI Cards dynamically
                if (data.kpis) {
                    if(document.getElementById('kpi_nb_clients')) document.getElementById('kpi_nb_clients').textContent = data.kpis.nb_clients;
                    if(document.getElementById('kpi_total_solde')) document.getElementById('kpi_total_solde').textContent = data.kpis.total_solde;
                }
                
                // Re-attach modal events for newly loaded rows
                attachRowEvents();
            })
            .catch(err => {
                console.error('Error fetching clients:', err);
                tableBody.style.opacity = '1';
            });
        }

        function handleInput() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => fetchFilteredData(), 400);
        }

        // Attach events
        if (globalSearch) globalSearch.addEventListener('input', handleInput);
        filterInputs.forEach(input => {
            if (!input.disabled) {
                input.addEventListener('input', handleInput);
                input.addEventListener('change', handleInput);
            }
        });
        
        // Clear Filters logic
        if (btnClear) {
            btnClear.addEventListener('click', function() {
                if (globalSearch) globalSearch.value = '';
                filterInputs.forEach(input => input.value = '');
                fetchFilteredData();
            });
        }

        function attachPaginationEvents() {
            if (!paginationWrapper) return;
            const links = paginationWrapper.querySelectorAll('a.page-link');
            links.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    fetchFilteredData(this.href);
                });
            });
        }

        attachPaginationEvents();

        // --------------------------------------------------------
        // Modal Logic (kept from original)
        // --------------------------------------------------------
        const newClientModal = document.getElementById('newClientModal');
        const confirmDeleteModal = document.getElementById('confirmDeleteModal');
        const btnNewClient = document.getElementById('btnNewClient');
        const btnCloseModal = document.getElementById('btnCloseModal');
        const btnCancelForm = document.getElementById('btnCancelForm');
        const newClientForm = document.getElementById('newClientForm');
        
        let clientToDelete = null;

        function openModal() {
            if (newClientForm) {
                newClientForm.reset();
                newClientForm.action = '{{ route("vente.clients.store") }}';
                // Default dates/checkboxes
                if(document.getElementById('date_naissance')) document.getElementById('date_naissance').value = '{{ date("Y-m-d") }}';
                if(document.getElementById('bloqueCredit')) document.getElementById('bloqueCredit').checked = false;
                if(document.getElementById('gFidelite')) document.getElementById('gFidelite').checked = true;
                
                // Method spoofing cleanup
                const methodInput = newClientForm.querySelector('input[name="_method"]');
                if(methodInput) methodInput.remove();
            }
            if (newClientModal) newClientModal.classList.add('show');
        }

        function closeModal() {
            if (newClientModal) newClientModal.classList.remove('show');
        }

        if (btnNewClient) btnNewClient.addEventListener('click', openModal);
        if (btnCloseModal) btnCloseModal.addEventListener('click', closeModal);
        if (btnCancelForm) btnCancelForm.addEventListener('click', closeModal);

        // Edit logic
        function attachRowEvents() {
            document.querySelectorAll('.btn-edit').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    if (newClientForm) {
                        newClientForm.action = `/vente/clients/\${id}`;
                        
                        // Add method spoofing for PUT
                        let methodInput = newClientForm.querySelector('input[name="_method"]');
                        if(!methodInput) {
                            methodInput = document.createElement('input');
                            methodInput.type = 'hidden';
                            methodInput.name = '_method';
                            methodInput.value = 'PUT';
                            newClientForm.appendChild(methodInput);
                        }

                        // Fill data
                        if(document.getElementById('raison')) document.getElementById('raison').value = this.getAttribute('data-nom') || '';
                        if(document.getElementById('matricule_fiscal')) document.getElementById('matricule_fiscal').value = this.getAttribute('data-mf') || '';
                        if(document.getElementById('telephone')) document.getElementById('telephone').value = this.getAttribute('data-tel') || '';
                        if(document.getElementById('email')) document.getElementById('email').value = this.getAttribute('data-email') || '';
                        if(document.getElementById('ville')) document.getElementById('ville').value = this.getAttribute('data-ville') || '';
                        if(document.getElementById('adresse')) document.getElementById('adresse').value = this.getAttribute('data-adresse') || '';
                        
                        if(document.getElementById('bloqueCredit')) document.getElementById('bloqueCredit').checked = this.getAttribute('data-credit') === '1';
                        if(document.getElementById('gFidelite')) document.getElementById('gFidelite').checked = this.getAttribute('data-fidelite') === '1';
                    }

                    if (newClientModal) newClientModal.classList.add('show');
                });
            });

            document.querySelectorAll('.btn-delete').forEach(btn => {
                btn.addEventListener('click', function() {
                    clientToDelete = this.getAttribute('data-id');
                    if(confirmDeleteModal) confirmDeleteModal.classList.add('show');
                });
            });
        }
        
        attachRowEvents();

        // Delete Logic
        const btnCancelDelete = document.getElementById('btnCancelDelete');
        const btnConfirmDelete = document.getElementById('btnConfirmDelete');

        if (btnCancelDelete) {
            btnCancelDelete.addEventListener('click', () => {
                if(confirmDeleteModal) confirmDeleteModal.classList.remove('show');
                clientToDelete = null;
            });
        }

        if (btnConfirmDelete) {
            btnConfirmDelete.addEventListener('click', () => {
                if (clientToDelete) {
                    fetch(`/vente/clients/\${clientToDelete}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            if(confirmDeleteModal) confirmDeleteModal.classList.remove('show');
                            fetchFilteredData(); // reload table
                        } else {
                            alert(data.message || 'Erreur lors de la suppression.');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Erreur serveur.');
                    });
                }
            });
        }
    });
</script>
@endsection
HTML;

$new_content = str_replace('{$modals}', $modals, $new_content);
$new_content = str_replace('{$modal_css}', $modal_css, $new_content);

file_put_contents('resources/views/vente/clients/index.blade.php', $new_content);

echo "Success!\n";

