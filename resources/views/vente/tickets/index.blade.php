@extends('layouts.app')

@section('title', 'Consultation des Tickets')

@section('content')
<div class="main-content-inner full-width">
    
    <!-- Table Card container -->
    <div class="table-container" style="background: white; border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden; margin-top: 20px;">
        
        <!-- Table Header -->
        <div class="table-header-wrapper" style="padding: 20px 24px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border);">
            <h2 class="table-title" style="font-size: 18px; font-weight: 700; color: var(--text); display: flex; align-items: center; gap: 8px; margin: 0;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--primary);">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
                Consultation des Tickets
            </h2>
            <div class="actions">
                <button class="btn-icon" style="background: none; border: none; cursor: pointer; color: var(--text-secondary);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Advanced Filters Area -->
        <div style="background: #f8fafc; padding: 16px 24px; border-bottom: 1px solid var(--border);">
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; align-items: end;">
                <!-- Dates -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Du</label>
                    <input type="date" class="form-control filter-date" name="date_du" id="filter_date_du" value="" style="width: 100%; border-radius: 6px; border: 1px solid var(--border); padding: 8px 12px; font-size: 13px;">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Au</label>
                    <input type="date" class="form-control filter-date" name="date_au" id="filter_date_au" value="" style="width: 100%; border-radius: 6px; border: 1px solid var(--border); padding: 8px 12px; font-size: 13px;">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Statut</label>
                    <select class="form-control filter-dropdown" name="statut" id="filter_statut" style="width: 100%; border-radius: 6px; border: 1px solid var(--border); padding: 8px 12px; font-size: 13px;">
                        <option value="">Tous les statuts</option>
                        @foreach($statuts as $st)
                            <option value="{{ $st->statutdocumentid }}" {{ request('statut') == $st->statutdocumentid ? 'selected' : '' }}>{{ $st->libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Client</label>
                    <select class="form-control filter-dropdown" name="client" id="filter_client" style="width: 100%; border-radius: 6px; border: 1px solid var(--border); padding: 8px 12px; font-size: 13px;">
                        <option value="">Tous les clients</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->clientid }}">{{ $c->nom }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Second Row of Dropdowns -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Caissier</label>
                    <select class="form-control filter-dropdown" name="caissier" id="filter_caissier" style="width: 100%; border-radius: 6px; border: 1px solid var(--border); padding: 8px 12px; font-size: 13px;">
                        <option value="">Tous les caissiers</option>
                        @foreach($caissiers as $c)
                            <option value="{{ $c->userid }}">{{ $c->login }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Vendeur</label>
                    <select class="form-control filter-dropdown" name="vendeur" id="filter_vendeur" style="width: 100%; border-radius: 6px; border: 1px solid var(--border); padding: 8px 12px; font-size: 13px;">
                        <option value="">Tous les vendeurs</option>
                        @foreach($vendeurs as $v)
                            <option value="{{ $v->employeeid }}">{{ $v->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div></div>
                <div style="text-align: right;">
                    <button type="button" id="btnFilter" style="background: var(--primary); color: white; border: none; padding: 8px 24px; border-radius: 6px; font-size: 13px; font-weight: 500; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                        </svg>
                        Appliquer
                    </button>
                </div>
            </div>
        </div>

        <!-- Global Search -->
        <div style="padding: 16px 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: flex-end; background: white;">
            <div style="position: relative; width: 300px;">
                <input type="text" id="globalSearch" class="form-control" placeholder="Recherche globale..." style="width: 100%; padding: 10px 16px 10px 40px; border-radius: 8px; border: 1px solid var(--border); font-size: 13px; outline: none;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%);">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </div>
        </div>

        <div class="table-responsive" style="min-height: 400px; overflow-x: auto;">
            <table class="data-table" id="ticketsTable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Numero</th>
                        <th>Statut</th>
                        <th>Code</th>
                        <th>Client</th>
                        <th>Code Vendeur</th>
                        <th>Vendeur</th>
                        <th>QTE</th>
                        <th>Brut HT</th>
                        <th>%Rem</th>
                        <th>Net HT</th>
                        <th>TVA</th>
                        <th>Total TTC</th>
                        <th>Brut TTC</th>
                        <th>Acompte</th>
                        <th>Reste à Payer</th>
                    </tr>
                    <tr class="filter-row">
                        <th>
                            <div style="display: flex; align-items: center; gap: 4px;">
                                <input type="text" class="filter-col" data-col="f_date">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                            </div>
                        </th>
                        <th><input type="text" class="filter-col" data-col="f_numero"></th>
                        <th><input type="text" class="filter-col" data-col="f_statut"></th>
                        <th><input type="text" class="filter-col" data-col="f_code"></th>
                        <th><input type="text" class="filter-col" data-col="f_client"></th>
                        <th><input type="text" class="filter-col" data-col="f_code_vendeur"></th>
                        <th><input type="text" class="filter-col" data-col="f_vendeur"></th>
                        <th><input type="text" class="filter-col" disabled style="background: #f1f5f9;"></th>
                        <th><input type="text" class="filter-col" disabled style="background: #f1f5f9;"></th>
                        <th><input type="text" class="filter-col" disabled style="background: #f1f5f9;"></th>
                        <th><input type="text" class="filter-col" disabled style="background: #f1f5f9;"></th>
                        <th><input type="text" class="filter-col" disabled style="background: #f1f5f9;"></th>
                        <th><input type="text" class="filter-col" disabled style="background: #f1f5f9;"></th>
                        <th><input type="text" class="filter-col" disabled style="background: #f1f5f9;"></th>
                        <th><input type="text" class="filter-col" disabled style="background: #f1f5f9;"></th>
                        <th><input type="text" class="filter-col" disabled style="background: #f1f5f9;"></th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @include('vente.tickets.partials.table_body')
                </tbody>
                <tfoot>
                    <tr style="background: #f8fafc; font-weight: 700; border-top: 2px solid var(--border);">
                        <td colspan="7" style="text-align: right; padding: 12px 16px; color: var(--text);">Totaux</td>
                        <td class="amount-cell" id="tot_qte">0</td>
                        <td class="amount-cell" id="tot_brutht">0</td>
                        <td class="amount-cell" id="tot_remise">0</td>
                        <td class="amount-cell" id="tot_netht">0</td>
                        <td class="amount-cell" id="tot_tva">0</td>
                        <td class="amount-cell" id="tot_ttc">0</td>
                        <td class="amount-cell" id="tot_brutttc">0</td>
                        <td class="amount-cell" id="tot_acompte">0</td>
                        <td class="amount-cell" id="tot_reste">0</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-wrapper" id="paginationWrapper" style="padding: 12px 16px; border-top: 1px solid var(--border);">
            {{ $tickets->links() }}
        </div>
    </div>
</div>

@endsection

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

    .filter-row th {
        padding: 8px 16px;
        background: #f8fafc;
        border-bottom: 2px solid var(--border);
    }

    .filter-col {
        width: 100%;
        padding: 6px 10px;
        border: 1px solid var(--border);
        border-radius: 6px;
        font-size: 12px;
        outline: none;
        transition: border-color 0.2s;
    }

    .filter-col:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 2px var(--primary-light);
    }

    .amount-cell {
        font-family: 'Courier New', Courier, monospace;
        font-weight: 600;
        text-align: right;
        color: var(--text);
    }

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

    /* Bootstrap 5 Pagination Fallback Styles */
    .pagination {
        display: flex;
        padding-left: 0;
        list-style: none;
        margin: 0;
        gap: 4px;
    }
    .page-item .page-link {
        position: relative;
        display: block;
        color: var(--text-secondary);
        text-decoration: none;
        background-color: #fff;
        border: 1px solid var(--border);
        padding: 6px 12px;
        border-radius: 6px;
        font-weight: 500;
        transition: all 0.2s;
    }
    .page-item .page-link:hover {
        background-color: #f1f5f9;
        color: var(--primary);
    }
    .page-item.active .page-link {
        z-index: 3;
        color: #fff;
        background-color: var(--primary);
        border-color: var(--primary);
    }
    .page-item.disabled .page-link {
        color: #94a3b8;
        pointer-events: none;
        background-color: #f8fafc;
        border-color: var(--border);
    }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const globalSearch = document.getElementById('globalSearch');
        const filterInputs = document.querySelectorAll('.filter-col');
        const dropdownFilters = document.querySelectorAll('.filter-dropdown, .filter-date');
        const btnFilter = document.getElementById('btnFilter');
        const tableBody = document.getElementById('tableBody');
        const paginationWrapper = document.querySelector('.pagination-wrapper');

        let debounceTimer;

        function fetchFilteredData(url = '{{ route("vente.tickets.index") }}') {
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

            fetch(fetchUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                tableBody.innerHTML = data.html;
                paginationWrapper.innerHTML = data.pagination;
                attachPaginationEvents();

                // Update Totals
                if (data.totals) {
                    if(document.getElementById('tot_qte')) document.getElementById('tot_qte').textContent = data.totals.qte;
                    if(document.getElementById('tot_brutht')) document.getElementById('tot_brutht').textContent = data.totals.brut_ht;
                    if(document.getElementById('tot_remise')) document.getElementById('tot_remise').textContent = data.totals.remise;
                    if(document.getElementById('tot_netht')) document.getElementById('tot_netht').textContent = data.totals.net_ht;
                    if(document.getElementById('tot_tva')) document.getElementById('tot_tva').textContent = data.totals.tva;
                    if(document.getElementById('tot_ttc')) document.getElementById('tot_ttc').textContent = data.totals.ttc;
                    if(document.getElementById('tot_brutttc')) document.getElementById('tot_brutttc').textContent = data.totals.ttc; // brut ttc is same as ttc?
                    if(document.getElementById('tot_acompte')) document.getElementById('tot_acompte').textContent = data.totals.acompte;
                    if(document.getElementById('tot_reste')) document.getElementById('tot_reste').textContent = data.totals.reste;
                }
            })
            .catch(err => console.error('Error fetching tickets:', err));
        }

        function handleInput() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => fetchFilteredData(), 400);
        }

        // Attach events
        globalSearch.addEventListener('input', handleInput);
        filterInputs.forEach(input => {
            if (!input.disabled) input.addEventListener('input', handleInput);
        });
        
        // The main filter button handles dropdowns & dates
        btnFilter.addEventListener('click', function() {
            fetchFilteredData();
        });

        // Handle enter on main filters
        dropdownFilters.forEach(input => {
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
</script>
@endsection
