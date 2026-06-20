@extends('layouts.app')
@section('title', 'Calcul Commissions')

@section('content')
<div class="main-content-inner full-width">
    
    <!-- Table Card container -->
    <div class="table-container" style="background: white; border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden; margin-top: 20px;">
        
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center" style="background: #f8fafc; border-bottom: 1px solid var(--border);">
            <h2 class="table-title" style="font-size: 18px; font-weight: 700; color: var(--text); display: flex; align-items: center; gap: 8px; margin: 0;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--primary);">
                    <line x1="12" y1="1" x2="12" y2="23"></line>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
                Calcul Commissions
            </h2>
        </div>

        <!-- Filters Section -->
        <div style="padding: 20px 24px; border-bottom: 1px solid var(--border); background: #fbfcfd;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; align-items: end;">
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
                    <label class="form-label" style="font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">Vendeur</label>
                    <select class="form-control filter-dropdown" name="vendeur" id="filter_vendeur" style="width: 100%; border-radius: 6px; border: 1px solid var(--border); padding: 8px 12px; font-size: 13px;">
                        <option value="">Tous les vendeurs</option>
                        @foreach($vendeurs as $v)
                            <option value="{{ $v->employeeid }}">{{ $v->nom }} {{ $v->prenom }}</option>
                        @endforeach
                    </select>
                </div>
                
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

        <!-- Table -->
        <div class="table-responsive" style="min-height: 400px; overflow-x: auto; flex: 1; overflow-y: auto;">
            <table class="data-table" id="commissionsTable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Numéro</th>
                        <th>Vendeur</th>
                        <th>CA Net HT</th>
                        <th>% Commission</th>
                        <th>Montant Commission</th>
                    </tr>
                    <tr class="filter-row">
                        <th>
                            <div style="display: flex; align-items: center; gap: 4px;">
                                <input type="text" class="filter-col" data-col="f_date" placeholder="...">
                            </div>
                        </th>
                        <th><input type="text" class="filter-col" data-col="f_numero" placeholder="..."></th>
                        <th><input type="text" class="filter-col" data-col="f_vendeur" placeholder="..."></th>
                        <th><input type="text" class="filter-col" data-col="f_net_ht" placeholder="..."></th>
                        <th><input type="text" class="filter-col" data-col="f_taux" placeholder="..."></th>
                        <th><input type="text" class="filter-col" disabled style="background: #f1f5f9;"></th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @include('vente.commissions.partials.table_body')
                </tbody>
                <tfoot>
                    <tr style="background: #f8fafc; font-weight: 700; border-top: 2px solid var(--border);">
                        <td colspan="3" style="text-align: right; padding: 12px 16px; color: var(--text);">Totaux</td>
                        <td class="amount-cell" id="tot_netht">0,000</td>
                        <td class="amount-cell" style="background: #f1f5f9;"></td>
                        <td class="amount-cell" id="tot_commission" style="color: var(--primary);">0,000</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-wrapper" id="paginationWrapper" style="padding: 12px 16px; border-top: 1px solid var(--border);">
            {{ $commissions->links() }}
        </div>
    </div>
</div>

<style>
    /* Same styles as tickets/index.blade.php */
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
        display: none; 
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
                    if(document.getElementById('tot_netht')) document.getElementById('tot_netht').textContent = data.totals.net_ht;
                    if(document.getElementById('tot_commission')) document.getElementById('tot_commission').textContent = data.totals.commission;
                }
            })
            .catch(err => console.error('Error fetching commissions:', err));
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
        
        btnFilter.addEventListener('click', function() {
            fetchFilteredData();
        });

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
