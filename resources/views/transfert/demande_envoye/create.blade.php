@extends('layouts.app')

@section('title', 'Saisie Demande de transfert Golden Pos')

@section('styles')
<style>
    /* Modal Styles */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(15, 23, 42, 0.6); /* Slate 900 with opacity */
        backdrop-filter: blur(4px);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }
    .modal-content {
        background: var(--surface);
        width: 95%;
        max-width: 1200px;
        height: 90%;
        border-radius: 12px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 24px;
        border-bottom: 1px solid var(--border);
        background: #f8fafc;
    }
    .modal-title {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
        color: var(--text-main);
    }
    .modal-filters {
        display: flex;
        gap: 15px;
        padding: 16px 24px;
        background: white;
        border-bottom: 1px solid var(--border);
        align-items: flex-end;
        flex-wrap: wrap;
    }
    .modal-table-container {
        flex: 1;
        overflow: auto;
        padding: 0;
    }
    
    .input-row td {
        padding: 8px 12px !important;
        background: #f8fafc;
    }
    .input-row input {
        width: 100%;
        padding: 6px 10px;
        border: 1px solid var(--border);
        border-radius: 6px;
        box-sizing: border-box;
        outline: none;
        font-size: 13px;
        transition: border-color 0.2s;
    }
    .input-row input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px var(--primary-light);
    }
    .input-row input:disabled {
        background: #f1f5f9;
        color: var(--text-muted);
    }

    .action-btn-small {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 4px 8px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        border: 1px solid transparent;
        transition: all 0.2s;
        background: white;
    }
    .action-btn-small.add {
        color: var(--success);
        border-color: var(--success);
    }
    .action-btn-small.add:hover {
        background: var(--success);
        color: white;
    }
    .action-btn-small.delete {
        color: var(--danger);
        border-color: var(--danger);
    }
    .action-btn-small.delete:hover {
        background: var(--danger);
        color: white;
    }
    
    .footer-bar {
        padding: 12px 24px;
        background: #f8fafc;
        border-top: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        font-size: 14px;
        font-weight: 600;
        color: var(--text-main);
    }
</style>
@endsection

@section('content')
<div class="pos-container">
    
    <!-- Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Saisie Demande de Transfert</h1>
            <p class="page-subtitle">Nouvelle demande ou modification d'une demande existante.</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('transfert.demande_envoye.create') }}" class="btn btn-outline">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Nouveau
            </a>
            
            <button type="button" class="btn btn-primary" onclick="submitForm('envoyer')" {{ (isset($demande) && $demande->etatdemandetransfertid != 1) ? 'disabled' : '' }}>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="22" y1="2" x2="11" y2="13"></line>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                </svg>
                Envoyer
            </button>
            
            <button type="button" class="btn btn-danger">Annuler Demande</button>
            
            <a href="{{ route('transfert.demande_envoye.index') }}" class="btn btn-outline">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
                Fermer
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('transfert.demande_envoye.store') }}" id="headerForm">
        @csrf
        
        <!-- Informations Générales -->
        <div class="content-card" style="margin-bottom: 20px;">
            <div style="padding: 20px; display: flex; gap: 24px; align-items: flex-end; flex-wrap: wrap;">
                
                <div class="form-group" style="flex: 1; min-width: 200px; margin: 0;">
                    <label class="form-label">Expéditeur</label>
                    <input type="text" class="form-control" value="{{ $site ? $site->libelle : 'Golden Pos' }}" disabled>
                    <input type="hidden" name="siteid" value="{{ $site ? $site->siteid : '' }}">
                </div>

                <div class="form-group" style="flex: 1; min-width: 200px; margin: 0;">
                    <label class="form-label">Récepteur</label>
                    <select name="siterecepteurid" class="form-control" required {{ isset($demande) ? 'disabled' : '' }}>
                        <option value="">Sélectionner un site...</option>
                        @foreach($sites as $s)
                            <option value="{{ $s->siteid }}" {{ (isset($demande) && $demande->siterecepteurid == $s->siteid) ? 'selected' : '' }}>{{ $s->libelle }}</option>
                        @endforeach
                    </select>
                    @if(isset($demande))
                        <input type="hidden" name="siterecepteurid" value="{{ $demande->siterecepteurid }}">
                        <input type="hidden" name="demandetransfertid" value="{{ $demande->demandetransfertid }}">
                    @endif
                </div>

                <div class="form-group" style="flex: 2; min-width: 300px; margin: 0;">
                    <label class="form-label">Observation</label>
                    <input type="text" name="description" class="form-control" placeholder="Observation optionnelle..." value="{{ $demande->description ?? '' }}" {{ isset($demande) ? 'disabled' : '' }}>
                </div>

                <div style="margin: 0;">
                    <button type="submit" class="btn btn-outline" title="Enregistrer l'en-tête" style="height: 38px; width: 38px; padding: 0; display: flex; align-items: center; justify-content: center;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Tableau des Produits -->
        <div class="content-card">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 180px;">Réf</th>
                            <th>Désignation</th>
                            <th style="width: 120px;">Taille</th>
                            <th style="width: 120px;">Couleur</th>
                            <th style="width: 100px; text-align: center;">Qte</th>
                            <th style="width: 120px; text-align: right;">Prix Vente</th>
                            <th style="width: 80px; text-align: center;">Actions</th>
                        </tr>
                        <tr class="filter-row">
                            <th><input type="text" class="local-filter" data-col="0" placeholder="Filtrer Réf..." style="width:100%; border:1px solid var(--border); border-radius:4px; padding:4px;"></th>
                            <th><input type="text" class="local-filter" data-col="1" placeholder="Filtrer Désignation..." style="width:100%; border:1px solid var(--border); border-radius:4px; padding:4px;"></th>
                            <th><input type="text" class="local-filter" data-col="2" placeholder="Filtrer Taille..." style="width:100%; border:1px solid var(--border); border-radius:4px; padding:4px;"></th>
                            <th><input type="text" class="local-filter" data-col="3" placeholder="Filtrer Couleur..." style="width:100%; border:1px solid var(--border); border-radius:4px; padding:4px;"></th>
                            <th><input type="text" class="local-filter" data-col="4" placeholder="Filtrer Qte..." style="width:100%; border:1px solid var(--border); border-radius:4px; padding:4px; text-align:center;"></th>
                            <th><input type="text" class="local-filter" data-col="5" placeholder="Filtrer Prix..." style="width:100%; border:1px solid var(--border); border-radius:4px; padding:4px; text-align:right;"></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Ligne de saisie -->
                        <tr class="input-row">
                            <td style="display: flex; gap: 8px;">
                                <input type="text" id="input-ref" placeholder="Scanner Réf..." style="flex: 1;">
                                <button type="button" class="btn btn-outline" style="padding: 4px 8px; height: auto;" onclick="openProductModal()" title="Recherche avancée">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="11" cy="11" r="8"></circle>
                                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                    </svg>
                                </button>
                            </td>
                            <td><input type="text" id="input-des" disabled placeholder="Désignation"></td>
                            <td><input type="text" id="input-taille" disabled placeholder="Taille"></td>
                            <td><input type="text" id="input-couleur" disabled placeholder="Couleur"></td>
                            <td><input type="number" id="input-qte" value="1" min="1" style="text-align: center;"></td>
                            <td><input type="text" id="input-prix" disabled placeholder="Prix" style="text-align: right;"></td>
                            <td style="text-align: center;">
                                <div style="display: flex; gap: 4px; justify-content: center;">
                                    <button type="button" class="action-btn-small add" onclick="addLine(event)">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                    </button>
                                    <button type="button" class="action-btn-small delete" onclick="clearInputRow(event)">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Lignes de détail existantes -->
                        @if(isset($lignes) && count($lignes) > 0)
                            @foreach($lignes as $index => $ligne)
                                <tr class="existing-row hover-row">
                                    <td>{{ $ligne->reference ?? $ligne->produitcode }}</td>
                                    <td>{{ $ligne->produitlibelle }}</td>
                                    <td>{{ $ligne->taillelibelle }}</td>
                                    <td>{{ $ligne->couleurlibelle }}</td>
                                    <td style="text-align: center; font-weight: 600;">{{ $ligne->qte }}</td>
                                    <td style="text-align: right;">{{ number_format($ligne->ttc, 2, ',', ' ') }} MAD</td>
                                    <td style="text-align: center;">
                                        <button type="button" class="action-btn-small delete" onclick="this.closest('tr').remove(); updateFooter();" {{ isset($demande) && $demande->etatdemandetransfertid != 1 ? 'disabled' : '' }}>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                        </button>
                                        <input type="hidden" name="lignes[{{ $index }}][produitid]" value="{{ $ligne->produitid }}">
                                        <input type="hidden" name="lignes[{{ $index }}][produit2id]" value="{{ $ligne->produit2id }}">
                                        <input type="hidden" name="lignes[{{ $index }}][qte]" value="{{ $ligne->qte }}">
                                        <input type="hidden" name="lignes[{{ $index }}][prix]" value="{{ $ligne->ttc }}">
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                        
                        <!-- Lignes vides par défaut -->
                        <tr id="empty-row" style="{{ isset($lignes) && count($lignes) > 0 ? 'display: none;' : '' }}">
                            <td colspan="7" style="padding: 60px; text-align: center; color: var(--text-muted);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 16px; opacity: 0.5;">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                    <polyline points="21 15 16 10 5 21"></polyline>
                                </svg>
                                <div style="font-weight: 600; font-size: 15px; margin-bottom: 8px;">Aucun produit ajouté</div>
                                <div style="font-size: 13px;">Veuillez scanner une référence ou utiliser la recherche avancée pour ajouter des produits.</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Footer -->
            <div class="footer-bar">
                <div>Total Lignes: <span id="total-lines" style="color: var(--primary);">{{ isset($lignes) ? count($lignes) : 0 }}</span></div>
                <div>Quantité Totale: <span id="total-qte" style="color: var(--primary);">0</span></div>
            </div>
        </div>
    </form>

    <!-- Modal Sélection des Produits -->
    @include('transfert.demande_envoye.partials.product_modal')

</div>

@endsection

@section('scripts')
<script>
    function submitForm(actionType) {
        let form = document.getElementById('headerForm');
        let input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'action_type';
        input.value = actionType;
        form.appendChild(input);
        form.submit();
    }

    let currentSelectedProduct = null;
    let lineIndex = {{ isset($lignes) ? count($lignes) : 0 }};

    function openProductModal() {
        document.getElementById('productModal').style.display = 'flex';
        searchProducts(); // Load initially
    }

    function searchProducts() {
        // Ensure elements exist before reading values (in case partial changes)
        const sf = document.getElementById('filter-sf');
        const f = document.getElementById('filter-f');
        const s = document.getElementById('filter-s');
        const c = document.getElementById('filter-c');
        const m = document.getElementById('filter-m');
        const searchInput = document.getElementById('filter-search');

        const sousFamille = sf ? sf.value : '';
        const famille = f ? f.value : '';
        const saison = s ? s.value : '';
        const categorie = c ? c.value : '';
        const marque = m ? m.value : '';
        const search = searchInput ? searchInput.value : '';

        const url = new URL('{{ route("transfert.demande_envoye.search_products") }}', window.location.origin);
        if(sousFamille) url.searchParams.append('sousfamilleid', sousFamille);
        if(famille) url.searchParams.append('familleid', famille);
        if(saison) url.searchParams.append('saisonid', saison);
        if(categorie) url.searchParams.append('categoryid', categorie);
        if(marque) url.searchParams.append('marqueid', marque);
        if(search) url.searchParams.append('search', search);

        fetch(url)
            .then(res => res.json())
            .then(data => {
                const tbody = document.getElementById('modal-tbody');
                if (!tbody) return;
                
                tbody.innerHTML = '';
                
                if(data.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="9" style="padding: 40px; text-align: center; color: var(--text-muted); font-weight: 600;">Aucun produit trouvé</td></tr>`;
                    return;
                }

                data.forEach(p => {
                    const tr = document.createElement('tr');
                    tr.style.cursor = 'pointer';
                    tr.style.borderBottom = '1px solid #f1f5f9';
                    tr.style.transition = 'background 0.15s';
                    tr.onmouseenter = () => tr.style.background = '#f0f4ff';
                    tr.onmouseleave = () => tr.style.background = '';
                    tr.onclick = () => selectProduct(p);

                    const cellStyle = 'padding: 10px 12px; font-size: 12px; color: #334155; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;';
                    const stock = p.total_stock ? parseInt(p.total_stock) : 0;
                    const stockColor = stock > 0 ? '#16a34a' : '#dc2626';

                    tr.innerHTML = `
                        <td style="${cellStyle} font-weight: 600;">${p.produitcode || ''}</td>
                        <td style="${cellStyle}">${p.reference || ''}</td>
                        <td style="${cellStyle} font-size: 11px; color: #64748b;">${p.barcode2 || ''}</td>
                        <td style="${cellStyle} white-space: normal;">${p.produitlibelle || ''}</td>
                        <td style="${cellStyle}">${p.famillelibelle || ''}</td>
                        <td style="${cellStyle}">${p.sousfamillelibelle || ''}</td>
                        <td style="${cellStyle} text-align: right; font-weight: 600; color: #16a34a;">${p.ttc_vente ? parseFloat(p.ttc_vente).toFixed(3) : ''}</td>
                        <td style="${cellStyle} text-align: center; font-weight: 700; color: ${stockColor};">${stock}</td>
                        <td style="${cellStyle}">${p.fournisseur || ''}</td>
                    `;
                    tbody.appendChild(tr);
                });
            });
    }

    function selectProduct(product) {
        currentSelectedProduct = product;
        document.getElementById('productModal').style.display = 'none';
        
        // Populate the main grid input row
        document.getElementById('input-ref').value = product.reference || product.produitcode;
        document.getElementById('input-des').value = product.produitlibelle || '';
        document.getElementById('input-taille').value = product.taillelibelle || '';
        document.getElementById('input-couleur').value = product.couleurlibelle || '';
        document.getElementById('input-prix').value = product.ttc_vente ? parseFloat(product.ttc_vente).toFixed(2) + ' MAD' : '';
        document.getElementById('input-qte').value = 1;
        document.getElementById('input-qte').focus();
    }

    function clearInputRow(e) {
        if(e) e.preventDefault();
        currentSelectedProduct = null;
        document.getElementById('input-ref').value = '';
        document.getElementById('input-des').value = '';
        document.getElementById('input-taille').value = '';
        document.getElementById('input-couleur').value = '';
        document.getElementById('input-prix').value = '';
        document.getElementById('input-qte').value = 1;
    }

    function addLine(e) {
        if(e) e.preventDefault();
        if(!currentSelectedProduct) {
            alert('Veuillez d\'abord sélectionner un produit.');
            return;
        }

        const qte = document.getElementById('input-qte').value;
        if(qte <= 0) {
            alert('Quantité invalide.');
            return;
        }

        const tbody = document.querySelector('.data-table tbody');
        const emptyRow = document.getElementById('empty-row');
        if(emptyRow) emptyRow.style.display = 'none';

        const tr = document.createElement('tr');
        tr.classList.add('existing-row', 'hover-row');
        tr.innerHTML = `
            <td>${currentSelectedProduct.reference || currentSelectedProduct.produitcode}</td>
            <td>${currentSelectedProduct.produitlibelle || ''}</td>
            <td>${currentSelectedProduct.taillelibelle || ''}</td>
            <td>${currentSelectedProduct.couleurlibelle || ''}</td>
            <td style="text-align: center; font-weight: 600;">${qte}</td>
            <td style="text-align: right;">${currentSelectedProduct.ttc_vente ? parseFloat(currentSelectedProduct.ttc_vente).toFixed(2) + ' MAD' : ''}</td>
            <td style="text-align: center;">
                <button type="button" class="action-btn-small delete" onclick="this.closest('tr').remove(); updateFooter();">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
                <input type="hidden" name="lignes[${lineIndex}][produitid]" value="${currentSelectedProduct.produitid}">
                <input type="hidden" name="lignes[${lineIndex}][produit2id]" value="${currentSelectedProduct.produit2id}">
                <input type="hidden" name="lignes[${lineIndex}][qte]" value="${qte}">
                <input type="hidden" name="lignes[${lineIndex}][prix]" value="${currentSelectedProduct.ttc_vente || 0}">
            </td>
        `;

        // Insert before empty row
        tbody.insertBefore(tr, emptyRow);
        lineIndex++;
        
        updateFooter();
        clearInputRow();
    }

    function updateFooter() {
        const rows = document.querySelectorAll('.data-table tbody tr.existing-row');
        let totalQte = 0;
        
        rows.forEach(row => {
            const qteInput = row.querySelector('input[name$="[qte]"]');
            if(qteInput) {
                totalQte += parseFloat(qteInput.value) || 0;
            }
        });

        document.getElementById('total-lines').textContent = rows.length;
        document.getElementById('total-qte').textContent = totalQte;

        if(rows.length === 0) {
            const emptyRow = document.getElementById('empty-row');
            if(emptyRow) emptyRow.style.display = 'table-row';
        }
    }

    // Init footer on load
    document.addEventListener('DOMContentLoaded', function() {
        updateFooter();
    });

    // Support Enter key on Qte
    document.getElementById('input-qte').addEventListener('keypress', function(e) {
        if(e.key === 'Enter') {
            e.preventDefault();
            addLine();
        }
    });

    // Client-side filtering logic for the detail table
    const localFilters = document.querySelectorAll('.local-filter');
    localFilters.forEach(filter => {
        filter.addEventListener('input', function() {
            filterLocalTable();
        });
    });

    function filterLocalTable() {
        const rows = document.querySelectorAll('.data-table tbody tr.existing-row');
        const filters = Array.from(localFilters).map(f => ({
            col: parseInt(f.getAttribute('data-col')),
            value: f.value.toLowerCase().trim()
        }));

        let visibleCount = 0;
        let totalQte = 0;

        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            let isMatch = true;

            filters.forEach(f => {
                if (f.value !== '') {
                    // Extract text content. For Qte, we might need to handle the fact that we show raw text, 
                    // but wait, Qte is displayed as text in existing rows, the input is hidden.
                    const cellText = cells[f.col].textContent.toLowerCase();
                    if (!cellText.includes(f.value)) {
                        isMatch = false;
                    }
                }
            });

            if (isMatch) {
                row.style.display = '';
                visibleCount++;
                const qteInput = row.querySelector('input[name$="[qte]"]');
                if(qteInput) {
                    totalQte += parseFloat(qteInput.value) || 0;
                }
            } else {
                row.style.display = 'none';
            }
        });

        // Update footer based on visible rows only (or total? Usually total for the document, but visible if filtering)
        // Let's update footer to show visible / total
        const totalRows = rows.length;
        document.getElementById('total-lines').textContent = visibleCount + ' (sur ' + totalRows + ')';
        document.getElementById('total-qte').textContent = totalQte;
        
        const emptyRow = document.getElementById('empty-row');
        if (visibleCount === 0 && totalRows > 0) {
            // All rows filtered out
            if(emptyRow) {
                emptyRow.style.display = 'table-row';
                emptyRow.querySelector('div').textContent = 'Aucun produit ne correspond aux filtres';
            }
        } else if (visibleCount > 0) {
            if(emptyRow) emptyRow.style.display = 'none';
        } else if (totalRows === 0) {
            // No rows at all
            if(emptyRow) {
                emptyRow.style.display = 'table-row';
                emptyRow.querySelector('div').textContent = 'Aucun produit ajouté';
            }
        }
    }
</script>
@endsection
