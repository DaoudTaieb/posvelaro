@extends('layouts.app')

@section('title', 'Consultation des Clients')

@section('styles')
<style>
    /* Table Card container */
    .table-container {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 12px;
        box-shadow: var(--shadow-sm);
        overflow: hidden;
        margin-top: 20px;
    }

    .table-header-wrapper {
        padding: 20px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid var(--border);
    }

    .table-title {
        font-size: 18px;
        font-weight: 700;
        color: var(--text);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.2s;
        text-decoration: none;
    }

    .btn-primary:hover {
        background: #4338ca;
    }

    /* Table styles */
    .data-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 13px;
    }

    .data-table th, 
    .data-table td {
        padding: 12px 16px;
        border-bottom: 1px solid var(--border);
        white-space: nowrap;
    }

    .data-table th {
        background: #f8fafc;
        color: var(--text-secondary);
        font-weight: 600;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 0.5px;
    }

    .data-table tr:hover td {
        background: #f1f5f9;
    }

    .data-table tr:last-child td {
        border-bottom: none;
    }

    /* Filters row */
    .filter-row th {
        padding: 8px 16px;
        background: #f8fafc;
        border-bottom: 2px solid var(--border);
    }

    .filter-input {
        width: 100%;
        min-width: 80px;
        padding: 6px 10px;
        border: 1px solid var(--border);
        border-radius: 6px;
        font-size: 12px;
        font-family: inherit;
        outline: none;
        transition: border-color 0.2s;
    }
    
    .filter-input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 2px var(--primary-light);
    }

    /* Badges & specific cells */
    .badge-check {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        border-radius: 6px;
    }
    .badge-check.active {
        background: var(--success-bg);
        color: var(--success);
    }
    .badge-check.inactive {
        background: #f1f5f9;
        color: #94a3b8;
    }

    .badge-text {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
        background: #f1f5f9;
        color: var(--text-secondary);
    }

    .amount-cell {
        font-family: 'Courier New', Courier, monospace;
        font-weight: 600;
        color: var(--text);
    }

    /* Actions */
    .action-btns {
        display: flex;
        gap: 6px;
    }

    .btn-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 6px;
        border: 1px solid var(--border);
        background: white;
        color: var(--text-secondary);
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-icon:hover {
        background: var(--bg);
        color: var(--primary);
    }
    .btn-icon.delete:hover {
        color: var(--danger);
        border-color: var(--danger-border);
        background: var(--danger-bg);
    }

    /* Pagination wrapper */
    .pagination-wrapper {
        padding: 16px 24px;
        border-top: 1px solid var(--border);
        background: #fff;
    }
    
    .pagination-wrapper svg {
        width: 1.25rem;
        height: 1.25rem;
    }
    
    .pagination-wrapper nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 13px;
    }

    .pagination-wrapper nav > div:first-child {
        display: none; /* Hide mobile pagination for desktop */
    }

    .pagination-wrapper nav > div:last-child {
        display: flex;
        justify-content: space-between;
        width: 100%;
        align-items: center;
    }

    .pagination-wrapper p {
        margin: 0;
        color: var(--text-secondary);
    }

    .pagination-wrapper span.relative,
    .pagination-wrapper a.relative {
        display: inline-flex;
        align-items: center;
        padding: 8px 12px;
        margin-left: -1px;
        background: #fff;
        border: 1px solid var(--border);
        color: var(--text-secondary);
        text-decoration: none;
        transition: background 0.2s;
    }

    .pagination-wrapper a.relative:hover {
        background: #f1f5f9;
        color: var(--primary);
    }

    .pagination-wrapper span[aria-current="page"] > span {
        background: var(--primary-light);
        color: var(--primary);
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        padding: 8px 12px;
        border: 1px solid var(--border);
        margin-left: -1px;
    }

    .pagination-wrapper span.relative svg,
    .pagination-wrapper a.relative svg {
        width: 16px;
        height: 16px;
    }
    
    .table-responsive {
        overflow-x: auto;
    }

    /* Modal Styles */
    .modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(15, 23, 42, 0.5);
        backdrop-filter: blur(4px);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 100;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .modal-backdrop.show {
        opacity: 1;
        visibility: visible;
    }

    .modal-container {
        background: var(--bg-card);
        border-radius: 12px;
        width: 100%;
        max-width: 800px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        transform: scale(0.95);
        transition: transform 0.3s ease;
        display: flex;
        flex-direction: column;
        max-height: 90vh;
    }

    .modal-backdrop.show .modal-container {
        transform: scale(1);
    }

    .modal-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f8fafc;
        border-radius: 12px 12px 0 0;
    }

    .modal-title {
        font-size: 16px;
        font-weight: 700;
        color: var(--text);
    }

    .btn-close {
        background: transparent;
        border: none;
        color: var(--text-muted);
        cursor: pointer;
        padding: 4px;
        border-radius: 6px;
        transition: all 0.2s;
    }
    .btn-close:hover {
        background: var(--border);
        color: var(--text);
    }

    .modal-body {
        padding: 24px;
        overflow-y: auto;
    }

    .modal-footer {
        padding: 16px 24px;
        border-top: 1px solid var(--border);
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        background: #f8fafc;
        border-radius: 0 0 12px 12px;
    }

    /* Form Styles */
    .form-grid {
        display: grid;
        grid-template-columns: repeat(12, 1fr);
        gap: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .form-group.col-12 { grid-column: span 12; }
    .form-group.col-8 { grid-column: span 8; }
    .form-group.col-6 { grid-column: span 6; }
    .form-group.col-4 { grid-column: span 4; }
    .form-group.col-3 { grid-column: span 3; }

    .form-group.row-align {
        flex-direction: row;
        align-items: center;
        gap: 10px;
        height: 100%;
        padding-top: 24px; /* Align with inputs that have labels */
    }

    .form-label {
        font-size: 13px;
        font-weight: 500;
        color: var(--text-secondary);
    }

    .form-control {
        padding: 10px 12px;
        border: 1px solid var(--border);
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
        color: var(--text);
        transition: all 0.2s;
        width: 100%;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-light);
    }

    textarea.form-control {
        resize: vertical;
        min-height: 80px;
    }

    .form-checkbox {
        width: 18px;
        height: 18px;
        border-radius: 4px;
        border: 1px solid var(--border);
        cursor: pointer;
        accent-color: var(--primary);
    }

    .checkbox-label {
        font-size: 14px;
        font-weight: 500;
        color: var(--text);
        cursor: pointer;
        user-select: none;
    }

    /* Action Buttons */
    .btn-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 40px;
        border-radius: 8px;
        border: 1px solid var(--border);
        background: white;
        color: var(--text);
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-action:hover {
        background: #f1f5f9;
    }

    .btn-action.confirm {
        border-color: var(--primary);
        color: var(--primary);
    }
    .btn-action.confirm:hover {
        background: var(--primary-light);
    }

    .btn-action.cancel {
        border-color: var(--danger-border);
        color: var(--danger);
    }
    .btn-action.cancel:hover {
        background: var(--danger-bg);
    }

    /* Global Search */
    .header-actions {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    /* Pagination Styling */
    .pagination {
        display: flex;
        list-style: none;
        padding: 0;
        margin: 0;
        gap: 6px;
        align-items: center;
        justify-content: flex-end;
    }
    .page-item .page-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 12px;
        min-width: 32px;
        height: 32px;
        border: 1px solid var(--border);
        border-radius: 6px;
        color: var(--text);
        text-decoration: none;
        font-size: 13px;
        background: var(--bg-card);
        transition: all 0.2s;
    }
    .page-item:not(.disabled) .page-link:hover {
        background: var(--bg-body);
        border-color: var(--text-muted);
    }
    .page-item.active .page-link {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }
    .page-item.disabled .page-link {
        color: var(--text-muted);
        background: var(--bg-body);
        cursor: not-allowed;
    }
    .pagination-wrapper {
        padding: 16px;
        border-top: 1px solid var(--border);
    }
    
    /* Fix Bootstrap 5 default views without Bootstrap CSS */
    .pagination-wrapper nav > div:first-child {
        display: none; /* Hide mobile pagination */
    }
    .pagination-wrapper nav > div.d-none {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
    }
    .pagination-wrapper p.small.text-muted {
        margin: 0;
        color: var(--text-muted);
        font-size: 13px;
    }

    .global-search {
        position: relative;
        display: flex;
        align-items: center;
    }

    .global-search .search-icon {
        position: absolute;
        left: 10px;
        width: 16px;
        height: 16px;
        color: var(--text-muted);
        pointer-events: none;
    }

    .global-search input {
        padding: 8px 12px 8px 34px;
        border: 1px solid var(--border);
        border-radius: 6px;
        font-size: 13px;
        font-family: inherit;
        outline: none;
        transition: all 0.2s;
        width: 220px;
    }

    .global-search input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 2px var(--primary-light);
        width: 260px;
    }
</style>
@endsection

@section('content')
<div class="main-content-inner full-width">
    
    @if(session('success'))
    <div style="background: var(--success-bg); border: 1px solid var(--success-border); color: var(--success); padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-weight: 500;">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div style="background: var(--danger-bg); border: 1px solid var(--danger-border); color: var(--danger); padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-weight: 500;">
        {{ session('error') }}
    </div>
    @endif

    <div class="table-container">
        <!-- Header -->
        <div class="table-header-wrapper">
            <h2 class="table-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                Consultation des Clients
            </h2>
            <div class="header-actions">
                <div class="global-search">
                    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" id="globalSearchInput" placeholder="Enter text to search...">
                </div>
                <button type="button" class="btn-primary" id="btnNewClient">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Nouveau Client
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
                        <th>Solde</th>
                        <th>Solde Dép.</th>
                        <th>Date S.D.</th>
                        <th>Ville</th>
                        <th>Adresse Fact.</th>
                        <th>Adresse Liv.</th>
                        <!-- Nouvelles colonnes -->
                        <th>Téléphone</th>
                        <th>R.Commerce</th>
                        <th>Fax</th>
                        <th>Email</th>
                        <th>ID</th>
                        <th>Solde Fidélité</th>
                        <th>Cumul Fidélité</th>
                        <th>Point Fidélité</th>
                    </tr>
                    <tr class="filter-row">
                        <th></th>
                        <th><input type="text" class="filter-input" data-col="1" placeholder="Filtrer..."></th>
                        <th><input type="text" class="filter-input" data-col="2" placeholder="Filtrer..."></th>
                        <th><input type="text" class="filter-input" data-col="3" placeholder="Filtrer..."></th>
                        <th></th>
                        <th><input type="text" class="filter-input" data-col="5" placeholder="Filtrer..."></th>
                        <th></th>
                        <th><input type="text" class="filter-input" data-col="7" placeholder="Filtrer..."></th>
                        <th><input type="text" class="filter-input" data-col="8" placeholder="Filtrer..."></th>
                        <th><input type="text" class="filter-input" data-col="9" placeholder="Filtrer..."></th>
                        <th><input type="text" class="filter-input" data-col="10" placeholder="Filtrer..."></th>
                        <th><input type="text" class="filter-input" data-col="11" placeholder="Filtrer..."></th>
                        <th><input type="text" class="filter-input" data-col="12" placeholder="Filtrer..."></th>
                        <th><input type="text" class="filter-input" data-col="13" placeholder="Filtrer..."></th>
                        <!-- Filtres nouvelles colonnes -->
                        <th><input type="text" class="filter-input" data-col="14" placeholder="Filtrer..."></th>
                        <th><input type="text" class="filter-input" data-col="15" placeholder="Filtrer..."></th>
                        <th><input type="text" class="filter-input" data-col="16" placeholder="Filtrer..."></th>
                        <th><input type="text" class="filter-input" data-col="17" placeholder="Filtrer..."></th>
                        <th><input type="text" class="filter-input" data-col="18" placeholder="Filtrer..."></th>
                        <th><input type="text" class="filter-input" data-col="19" placeholder="Filtrer..."></th>
                        <th><input type="text" class="filter-input" data-col="20" placeholder="Filtrer..."></th>
                        <th><input type="text" class="filter-input" data-col="21" placeholder="Filtrer..."></th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @include('vente.clients.partials.table_body')
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-wrapper">
            {{ $clients->links() }}
        </div>
    </div>
</div>

<!-- Modal Nouveau Client -->
<div class="modal-backdrop" id="newClientModal">
    <div class="modal-container">
        <div class="modal-header">
            <h3 class="modal-title">Nouveau Client</h3>
            <button class="btn-close" id="btnCloseModal">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <form id="newClientForm" action="{{ route('vente.clients.store') }}" method="POST">
                @csrf
                <div class="form-grid">
                    <!-- Row 1 -->
                    <div class="form-group col-8">
                        <label class="form-label">Raison</label>
                        <input type="text" class="form-control" id="raison" name="raison" required>
                    </div>
                    <div class="form-group col-4 row-align">
                        <input type="checkbox" id="bloqueCredit" name="bloque_credit" class="form-checkbox">
                        <label for="bloqueCredit" class="checkbox-label">Bloque Crédit</label>
                    </div>

                    <!-- Row 2 -->
                    <div class="form-group col-4">
                        <label class="form-label">Matricule Fiscal</label>
                        <input type="text" class="form-control" id="matricule_fiscal" name="matricule_fiscal">
                    </div>
                    <div class="form-group col-4 row-align" style="padding-top: 28px;">
                        <input type="checkbox" id="gFidelite" name="g_fidelite" class="form-checkbox" checked>
                        <label for="gFidelite" class="checkbox-label">G.Fidélite</label>
                    </div>
                    <div class="form-group col-4">
                        <label class="form-label">N° Carte Fid</label>
                        <input type="text" class="form-control" id="carte_fid" name="carte_fid">
                    </div>

                    <!-- Row 3 -->
                    <div class="form-group col-6">
                        <label class="form-label">Telephone</label>
                        <input type="text" class="form-control" id="telephone" name="telephone">
                    </div>
                    <div class="form-group col-6">
                        <label class="form-label">E-mail</label>
                        <input type="email" class="form-control" id="email" name="email">
                    </div>

                    <!-- Row 4 -->
                    <div class="form-group col-6">
                        <label class="form-label">Date de Naissance</label>
                        <input type="date" class="form-control" id="date_naissance" name="date_naissance" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="form-group col-6">
                        <label class="form-label">Ville</label>
                        <input type="text" class="form-control" id="ville" name="ville">
                    </div>

                    <!-- Row 5 -->
                    <div class="form-group col-12">
                        <label class="form-label">Adresse</label>
                        <textarea class="form-control" id="adresse" name="adresse" rows="2"></textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="submit" form="newClientForm" class="btn-action confirm" id="btnSubmitForm" title="Valider">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </button>
            <button type="button" class="btn-action cancel" id="btnCancelForm" title="Annuler">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
    </div>
</div>

<!-- Modal Confirmation Suppression -->
<div class="modal-backdrop" id="confirmDeleteModal">
    <div class="modal-container" style="max-width: 400px; text-align: center; padding: 30px;">
        <div style="color: var(--danger); margin-bottom: 20px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
        </div>
        <h3 style="margin-bottom: 10px; color: var(--text);">Confirmer la suppression</h3>
        <p style="color: var(--text-secondary); margin-bottom: 30px;">Voulez-vous vraiment supprimer ce client ? Cette action est irréversible.</p>
        
        <div style="display: flex; gap: 12px; justify-content: center;">
            <button type="button" class="btn" id="btnCancelDelete" style="background: #f1f5f9; color: var(--text); border: none; padding: 10px 24px; border-radius: 8px; font-weight: 500; cursor: pointer;">
                Annuler
            </button>
            <button type="button" class="btn" id="btnConfirmDelete" style="background: var(--danger); color: white; border: none; padding: 10px 24px; border-radius: 8px; font-weight: 500; cursor: pointer;">
                Supprimer
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Backend Ajax filtering
    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('.filter-input');
        const globalSearchInput = document.getElementById('globalSearchInput');
        const tableBody = document.getElementById('tableBody');
        let filterTimeout = null;

        function fetchFilteredData(url = null) {
            const params = new URLSearchParams();
            
            // Global search
            if (globalSearchInput.value.trim() !== '') {
                params.append('q', globalSearchInput.value.trim());
            }

            // Column filters
            inputs.forEach(inp => {
                if (inp.value.trim() !== '') {
                    params.append('col_' + inp.getAttribute('data-col'), inp.value.trim());
                }
            });

            // If an explicit URL is provided (e.g., from pagination), use it. Otherwise construct it.
            let fetchUrl = url;
            if (!fetchUrl) {
                fetchUrl = '{{ route("vente.clients.index") }}?' + params.toString();
            } else if (params.toString() !== '') {
                // If url already has params, append correctly
                fetchUrl += (fetchUrl.includes('?') ? '&' : '?') + params.toString();
            }

            fetch(fetchUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                tableBody.innerHTML = data.html;
                document.querySelector('.pagination-wrapper').innerHTML = data.pagination;
                attachPaginationEvents();
            })
            .catch(error => console.error('Error fetching data:', error));
        }

        function handleInput() {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(() => {
                fetchFilteredData();
            }, 300); // Debounce time
        }

        // Attach event listeners to all filter inputs
        inputs.forEach(input => {
            input.addEventListener('input', handleInput);
        });

        // Attach event listener to global search
        globalSearchInput.addEventListener('input', handleInput);

        // Handle pagination clicks via Ajax
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

        // Modal Logic
        const modal = document.getElementById('newClientModal');
        const btnNewClient = document.getElementById('btnNewClient');
        const btnCloseModal = document.getElementById('btnCloseModal');
        const btnCancelForm = document.getElementById('btnCancelForm');
        
        function openModal() {
            modal.classList.add('show');
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
        }
        
        function closeModal() {
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }

        btnNewClient.addEventListener('click', function() {
            const form = document.getElementById('newClientForm');
            form.reset();
            form.action = "{{ route('vente.clients.store') }}";
            const methodInput = form.querySelector('input[name="_method"]');
            if (methodInput) methodInput.remove();
            document.querySelector('.modal-title').textContent = 'Nouveau Client';
            openModal();
        });
        
        btnCloseModal.addEventListener('click', closeModal);
        btnCancelForm.addEventListener('click', closeModal);

        // Delete Confirmation Modal Logic
        const deleteModal = document.getElementById('confirmDeleteModal');
        const btnCancelDelete = document.getElementById('btnCancelDelete');
        const btnConfirmDelete = document.getElementById('btnConfirmDelete');
        let clientIdToDelete = null;

        function closeDeleteModal() {
            deleteModal.classList.remove('show');
            document.body.style.overflow = '';
            clientIdToDelete = null;
        }

        btnCancelDelete.addEventListener('click', closeDeleteModal);
        deleteModal.addEventListener('click', function(e) {
            if (e.target === deleteModal) closeDeleteModal();
        });

        btnConfirmDelete.addEventListener('click', function() {
            if (!clientIdToDelete) return;
            
            // Show loading state on button
            const originalText = btnConfirmDelete.textContent;
            btnConfirmDelete.textContent = 'Suppression...';
            btnConfirmDelete.disabled = true;

            fetch(`/vente/clients/${clientIdToDelete}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    closeDeleteModal();
                    fetchFilteredData(); // reload table
                } else {
                    alert(data.message || 'Erreur lors de la suppression.');
                }
            })
            .catch(err => console.error(err))
            .finally(() => {
                btnConfirmDelete.textContent = originalText;
                btnConfirmDelete.disabled = false;
            });
        });

        // Edit and Delete handlers using event delegation
        tableBody.addEventListener('click', function(e) {
            const deleteBtn = e.target.closest('.btn-delete');
            if (deleteBtn) {
                clientIdToDelete = deleteBtn.getAttribute('data-id');
                deleteModal.classList.add('show');
                document.body.style.overflow = 'hidden';
                return;
            }

            const editBtn = e.target.closest('.btn-edit');
            if (editBtn) {
                const id = editBtn.getAttribute('data-id');
                const form = document.getElementById('newClientForm');
                
                // Populate fields
                document.getElementById('raison').value = editBtn.getAttribute('data-nom');
                document.getElementById('matricule_fiscal').value = editBtn.getAttribute('data-mf');
                document.getElementById('telephone').value = editBtn.getAttribute('data-tel');
                document.getElementById('email').value = editBtn.getAttribute('data-email');
                document.getElementById('ville').value = editBtn.getAttribute('data-ville');
                document.getElementById('adresse').value = editBtn.getAttribute('data-adresse');
                document.getElementById('bloqueCredit').checked = editBtn.getAttribute('data-credit') === '1';
                document.getElementById('gFidelite').checked = editBtn.getAttribute('data-fidelite') === '1';
                
                // Update form action and method for update
                form.action = `/vente/clients/${id}`;
                let methodInput = form.querySelector('input[name="_method"]');
                if (!methodInput) {
                    methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'PUT';
                    form.appendChild(methodInput);
                }
                
                document.querySelector('.modal-title').textContent = 'Modifier Client';
                openModal();
            }
        });

        // Close when clicking outside
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });
    });
</script>
@endsection
