@extends('layouts.app')

@section('title', 'Consultation des Tickets')

@section('content')
<div class="pos-container">
    
    <!-- Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Consultation des Tickets</h1>
            <p class="page-subtitle">Analysez vos ventes, surveillez vos indicateurs et gérez vos encaissements.</p>
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
                <span class="kpi-label">Nombre de Tickets</span>
                <span class="kpi-value" id="kpi_nb_tickets">{{ $kpis['nb_tickets'] ?? 0 }}</span>
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
                <span class="kpi-label">CA Global (TTC)</span>
                <span class="kpi-value text-info" id="kpi_ca_ttc">{{ $kpis['ca_ttc'] ?? '0' }}</span>
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
                <span class="kpi-label">Total Encaissé</span>
                <span class="kpi-value text-success" id="kpi_total_paye">{{ $kpis['total_paye'] ?? '0' }}</span>
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
                <span class="kpi-label">Reste à Payer</span>
                <span class="kpi-value text-danger" id="kpi_total_non_paye">{{ $kpis['total_non_paye'] ?? '0' }}</span>
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
                <input type="text" id="globalSearch" class="search-input" placeholder="Recherche rapide (N°, Client, Vendeur...)">
            </div>
        </div>

        <!-- Advanced Filters -->
        <div class="advanced-filters show" id="advancedFilters" style="display: block;">
            <div class="filters-grid">
                <div class="form-group">
                    <label class="form-label">Date Début</label>
                    <input type="date" class="form-control filter-date" name="date_du" id="filter_date_du" value="{{ request('date_du') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Date Fin</label>
                    <input type="date" class="form-control filter-date" name="date_au" id="filter_date_au" value="{{ request('date_au', now()->format('Y-m-d')) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Statut</label>
                    <select class="form-control filter-dropdown" name="statut" id="filter_statut">
                        <option value=""></option>
                        @foreach($statuts as $st)
                            <option value="{{ $st->statutdocumentid }}" {{ request('statut') == $st->statutdocumentid ? 'selected' : '' }}>{{ $st->libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Client</label>
                    <select class="form-control filter-dropdown" name="client" id="filter_client">
                        <option value=""></option>
                        @foreach($clients as $c)
                            <option value="{{ $c->clientid }}" {{ request('client') == $c->clientid ? 'selected' : '' }}>{{ $c->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Caissier</label>
                    <select class="form-control filter-dropdown" name="caissier" id="filter_caissier">
                        <option value=""></option>
                        @foreach($caissiers as $c)
                            <option value="{{ $c->userid }}" {{ request('caissier') == $c->userid ? 'selected' : '' }}>{{ $c->login }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Vendeur</label>
                    <select class="form-control filter-dropdown" name="vendeur" id="filter_vendeur">
                        <option value=""></option>
                        @foreach($vendeurs as $v)
                            <option value="{{ $v->employeeid }}" {{ request('vendeur') == $v->employeeid ? 'selected' : '' }}>{{ $v->nom }}</option>
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
            <table class="data-table" id="ticketsTable">
                <thead>
                    <tr>
                        <th style="width: 90px;">Date</th>
                        <th>N° Ticket</th>
                        <th>Statut</th>
                        <th>Code C.</th>
                        <th>Client</th>
                        <th>Code V.</th>
                        <th>Vendeur</th>
                        <th class="text-right">Qté</th>
                        <th class="text-right">Brut HT</th>
                        <th class="text-right">Remise</th>
                        <th class="text-right">Net HT</th>
                        <th class="text-right">TVA</th>
                        <th class="text-right">TTC</th>
                        <th class="text-right">Brut TTC</th>
                        <th class="text-right">Acompte</th>
                        <th class="text-right">Reste</th>
                    </tr>
                    <tr class="filter-row">
                        <th><input type="date" class="filter-col" data-col="f_date" title="Filtrer par date"></th>
                        <th><input type="text" class="filter-col" data-col="f_numero" placeholder="Filtrer..."></th>
                        <th>
                            <select class="filter-col" data-col="f_statut">
                                <option value=""></option>
                                @foreach($statuts as $st)
                                    <option value="{{ $st->libelle }}">{{ $st->libelle }}</option>
                                @endforeach
                            </select>
                        </th>
                        <th><input type="text" class="filter-col" data-col="f_code" placeholder="Filtrer..."></th>
                        <th>
                            <select class="filter-col" data-col="f_client">
                                <option value=""></option>
                                @foreach($clients as $c)
                                    <option value="{{ $c->nom }}">{{ $c->nom }}</option>
                                @endforeach
                            </select>
                        </th>
                        <th><input type="text" class="filter-col" data-col="f_code_vendeur" placeholder="Filtrer..."></th>
                        <th>
                            <select class="filter-col" data-col="f_vendeur">
                                <option value=""></option>
                                @foreach($vendeurs as $v)
                                    @php
                                        $vName = $v->nom;
                                        if (($v->prenom ?? '') && ($v->prenom !== $v->nom)) {
                                            $vName .= ' ' . $v->prenom;
                                        }
                                    @endphp
                                    <option value="{{ $v->nom }}">{{ $vName }}</option>
                                @endforeach
                            </select>
                        </th>
                        <th><input type="text" class="filter-col text-right" data-col="f_qte" placeholder="..."></th>
                        <th><input type="text" class="filter-col text-right" data-col="f_brutht" placeholder="..."></th>
                        <th><input type="text" class="filter-col text-right" data-col="f_remise" placeholder="..."></th>
                        <th><input type="text" class="filter-col text-right" data-col="f_netht" placeholder="..."></th>
                        <th><input type="text" class="filter-col text-right" data-col="f_tva" placeholder="..."></th>
                        <th><input type="text" class="filter-col text-right" data-col="f_ttc" placeholder="..."></th>
                        <th><input type="text" class="filter-col text-right" data-col="f_brutttc" placeholder="..."></th>
                        <th><input type="text" class="filter-col text-right" data-col="f_acompte" placeholder="..."></th>
                        <th><input type="text" class="filter-col text-right" data-col="f_reste" placeholder="..."></th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @include('vente.tickets.partials.table_body')
                </tbody>
                <tfoot>
                    <tr class="table-totals">
                        <td colspan="7" class="totals-label">Totaux</td>
                        <td class="amount-cell" id="tot_qte">{{ $totals['qte'] }}</td>
                        <td class="amount-cell text-muted" id="tot_brutht">{{ $totals['brut_ht'] }}</td>
                        <td class="amount-cell text-muted" id="tot_remise">{{ $totals['remise'] }}</td>
                        <td class="amount-cell text-muted" id="tot_netht">{{ $totals['net_ht'] }}</td>
                        <td class="amount-cell text-muted" id="tot_tva">{{ $totals['tva'] }}</td>
                        <td class="amount-cell font-bold text-primary" id="tot_ttc">{{ $totals['ttc'] }}</td>
                        <td class="amount-cell text-muted" id="tot_brutttc">{{ $totals['ttc'] }}</td>
                        <td class="amount-cell text-warning" id="tot_acompte">{{ $totals['acompte'] }}</td>
                        <td class="amount-cell text-danger" id="tot_reste">{{ $totals['reste'] }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-wrapper" id="paginationWrapper">
            {{ $tickets->links() }}
        </div>
    </div>
</div>

<!-- Ticket Modal -->
<div id="ticketModal" class="modal-backdrop" style="display: none;">
    <div class="modal-content">
        <button id="closeTicketModal" class="modal-close">&times;</button>
        <button id="printTicketBtn" class="btn btn-primary modal-action">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            Imprimer
        </button>
        <div id="ticketModalContent" class="modal-body">
            <!-- Receipt content goes here -->
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
        const advancedFilters = document.getElementById('advancedFilters');
        const modal = document.getElementById('ticketModal');

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

            // Optional: Show loading state on table body
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
                    if(document.getElementById('tot_qte')) document.getElementById('tot_qte').textContent = data.totals.qte;
                    if(document.getElementById('tot_brutht')) document.getElementById('tot_brutht').textContent = data.totals.brut_ht;
                    if(document.getElementById('tot_remise')) document.getElementById('tot_remise').textContent = data.totals.remise;
                    if(document.getElementById('tot_netht')) document.getElementById('tot_netht').textContent = data.totals.net_ht;
                    if(document.getElementById('tot_tva')) document.getElementById('tot_tva').textContent = data.totals.tva;
                    if(document.getElementById('tot_ttc')) document.getElementById('tot_ttc').textContent = data.totals.ttc;
                    if(document.getElementById('tot_brutttc')) document.getElementById('tot_brutttc').textContent = data.totals.ttc;
                    if(document.getElementById('tot_acompte')) document.getElementById('tot_acompte').textContent = data.totals.acompte;
                    if(document.getElementById('tot_reste')) document.getElementById('tot_reste').textContent = data.totals.reste;
                }

                // Update KPI Cards dynamically
                if (data.kpis) {
                    if(document.getElementById('kpi_nb_tickets')) document.getElementById('kpi_nb_tickets').textContent = data.kpis.nb_tickets;
                    if(document.getElementById('kpi_ca_ttc')) document.getElementById('kpi_ca_ttc').textContent = data.kpis.ca_ttc;
                    if(document.getElementById('kpi_total_paye')) document.getElementById('kpi_total_paye').textContent = data.kpis.total_paye;
                    if(document.getElementById('kpi_total_non_paye')) document.getElementById('kpi_total_non_paye').textContent = data.kpis.total_non_paye;
                }
            })
            .catch(err => {
                console.error('Error fetching tickets:', err);
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
                if ($('#filter_client').length) {
                    $('#filter_client').val('').trigger('change.select2');
                }

                fetchFilteredData();
            });
        }

        // Initialize Select2
        if (typeof $ !== 'undefined') {
            $('.filter-dropdown').each(function() {
                const placeholder = $(this).attr('name') === 'client' ? "Rechercher un client..." : "Sélectionner...";
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

        // Modal Logic (Double click on table row)
        tableBody.addEventListener('dblclick', function(e) {
            const row = e.target.closest('.ticket-row');
            if (row && row.dataset.id) {
                const ticketId = row.dataset.id;
                fetch(`/vente/tickets/${ticketId}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.text())
                .then(html => {
                    document.getElementById('ticketModalContent').innerHTML = html;
                    modal.style.display = 'flex';
                    // Trigger reflow to apply transition
                    void modal.offsetWidth;
                    modal.classList.add('show');
                })
                .catch(err => console.error('Error fetching ticket details:', err));
            }
        });

        document.getElementById('closeTicketModal').addEventListener('click', function() {
            modal.classList.remove('show');
            setTimeout(() => modal.style.display = 'none', 300);
        });

        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('show');
                setTimeout(() => this.style.display = 'none', 300);
            }
        });

        document.getElementById('printTicketBtn').addEventListener('click', function() {
            const content = document.getElementById('ticketModalContent').innerHTML;
            const printWindow = window.open('', '_blank', 'width=800,height=600');
            printWindow.document.write('<html><head><title>Imprimer Ticket</title></head><body style="margin:0; padding:0; display:flex; justify-content:center;">');
            printWindow.document.write(content);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.focus();
            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 500);
        });

        // Client-side Export to CSV
        const btnExport = document.getElementById('btnExport');
        if (btnExport) {
            btnExport.addEventListener('click', function() {
                // Header columns
                const headers = [];
                document.querySelectorAll('#ticketsTable thead tr:first-of-type th').forEach(th => {
                    headers.push(th.textContent.trim());
                });
                
                // Rows data
                const rows = [];
                document.querySelectorAll('#ticketsTable tbody tr').forEach(tr => {
                    if (tr.classList.contains('empty-state') || tr.querySelector('.empty-state') || tr.querySelector('.empty-state') !== null) return;
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
                link.setAttribute("download", `export_tickets_${new Date().toISOString().slice(0,10)}.csv`);
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
