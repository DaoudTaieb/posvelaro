@extends('layouts.app')

@section('title', 'Saisie Demande de transfert')

@section('styles')
<style>
    .header-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 20px;
        background: white;
        border-bottom: 1px solid var(--border);
    }
    .header-actions {
        display: flex;
        gap: 5px;
    }
    .btn-header {
        background: white;
        border: 1px solid var(--border);
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 13px;
        color: var(--text);
        cursor: pointer;
    }
    .btn-header:hover { background: #f8fafc; }
    
    .tabs {
        display: flex;
        padding: 0 20px;
        background: white;
        border-bottom: 1px solid var(--border);
    }
    .tab {
        padding: 10px 15px;
        font-size: 13px;
        font-weight: 500;
        color: #7e22ce;
        border-bottom: 2px solid #7e22ce;
        cursor: pointer;
    }
    
    .form-section {
        background: white;
        padding: 15px 20px;
        border-bottom: 1px solid var(--border);
        display: flex;
        gap: 20px;
        align-items: flex-end;
    }
    
    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .form-group label {
        font-size: 12px;
        color: var(--text-secondary);
    }
    .form-control {
        padding: 6px 10px;
        border: 1px solid var(--border);
        border-radius: 4px;
        font-size: 13px;
        outline: none;
    }
    .form-control:disabled {
        background: #f1f5f9;
        color: var(--text-muted);
    }
    
    .grid-section {
        background: white;
        min-height: 200px;
    }
    
    .grid-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }
    .grid-table th {
        padding: 8px;
        font-weight: 600;
        color: var(--text-secondary);
        background: #f8fafc;
        border: 1px solid var(--border);
        text-align: left;
    }
    .grid-table td {
        padding: 8px;
        border: 1px solid var(--border);
        color: var(--text);
    }
    
    .input-row td {
        padding: 4px;
    }
    .input-row input {
        width: 100%;
        padding: 4px;
        border: 1px solid var(--border);
        border-radius: 3px;
        box-sizing: border-box;
        outline: none;
    }
    
    .btn-save {
        padding: 6px 15px;
        background: white;
        border: 1px solid var(--text);
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 32px;
    }
    .btn-save:hover { background: #f8fafc; }
    
    .footer-bar {
        padding: 10px 20px;
        background: #f8fafc;
        border-top: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        font-size: 12px;
        font-weight: 600;
        color: var(--text);
    }
    
    /* Modal Styles */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }
    .modal-content {
        background: white;
        width: 95%;
        height: 95%;
        border-radius: 4px;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 20px;
        border-bottom: 1px solid var(--border);
    }
    .modal-filters {
        display: flex;
        gap: 15px;
        padding: 10px 20px;
        background: white;
        border-bottom: 1px solid var(--border);
        align-items: center;
        flex-wrap: wrap;
    }
    .modal-table-container {
        flex: 1;
        overflow: auto;
    }
    .modal-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
    }
    .modal-table th {
        padding: 6px;
        border: 1px solid var(--border);
        background: #f8fafc;
        color: var(--text-secondary);
        font-weight: 600;
        text-align: left;
    }
    .modal-table td {
        padding: 6px;
        border: 1px solid var(--border);
        color: var(--text);
    }
    .col-search {
        width: 100%;
        padding: 4px;
        border: 1px solid var(--border);
        border-radius: 3px;
        box-sizing: border-box;
        margin-top: 4px;
    }
</style>
@endsection

@section('content')
<div class="main-content-inner full-width" style="padding: 0;">
    
    <!-- En-tête -->
    <div class="header-bar">
        <h1 style="font-size: 16px; font-weight: 700; margin: 0;">Saisie Demande de transfert</h1>
        <div class="header-actions">
            <a href="{{ route('transfert.demande_envoye.create') }}" class="btn-header" style="text-decoration: none;">Nouveau</a>
            
            <button type="button" class="btn-header" onclick="submitForm('envoyer')" {{ (isset($demande) && $demande->etatdemandetransfertid != 1) ? 'disabled' : '' }}>Envoyer</button>
            <button type="button" class="btn-header">Annuler Demande</button>
            
            <a href="{{ route('transfert.demande_envoye.index') }}" class="btn-header" style="text-decoration: none; padding: 6px 10px;" title="Fermer">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </a>
        </div>
    </div>
    
    <!-- Onglets -->
    <div class="tabs">
        <div class="tab">Général</div>
    </div>

    <!-- Formulaire Maître -->
    <div class="tabs-content" style="padding: 15px 20px;">
        <form method="POST" action="{{ route('transfert.demande_envoye.store') }}" id="headerForm">
            @csrf
            <div style="display: flex; gap: 20px; align-items: flex-end;">
                <!-- Expéditeur -->
                <div class="form-group" style="flex: 1;">
                    <label>Expéditeur</label>
                    <input type="text" class="form-control" value="{{ $site ? $site->libelle : 'Velaro' }}" disabled>
                    <input type="hidden" name="siteid" value="{{ $site ? $site->siteid : '' }}">
                </div>

                <!-- Récepteur -->
                <div class="form-group" style="flex: 1;">
                    <label>Récepteur</label>
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

                <!-- Observation -->
                <div class="form-group" style="flex: 2;">
                    <label>Observation</label>
                    <input type="text" name="description" class="form-control" placeholder="Observation" value="{{ $demande->description ?? 'Observation' }}" {{ isset($demande) ? 'disabled' : '' }}>
                </div>

                <!-- Actions -->
                <div style="display: flex; gap: 5px; margin-bottom: 2px;">
                    <button type="submit" class="btn-save" title="Enregistrer l'en-tête">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                    </button>
                </div>
            </div>

    <!-- Grille de Détails -->
    <div class="grid-section">
        <table class="grid-table">
            <thead>
                <tr>
                    <th style="width: 150px;">Réf</th>
                    <th>Désignation</th>
                    <th style="width: 80px;">Taille</th>
                    <th style="width: 80px;">Couleur</th>
                    <th style="width: 80px;">Qte</th>
                    <th style="width: 100px;">Prix Vente</th>
                    <th style="width: 60px; text-align: center;">Clear</th>
                </tr>
            </thead>
            <tbody>
                <!-- Ligne de saisie -->
                <tr class="input-row" style="background: #f8fafc;">
                    <td style="display: flex; gap: 2px;">
                        <input type="text" id="input-ref" placeholder="Réf..." style="width: calc(100% - 30px);">
                        <button type="button" class="btn-header" style="padding: 2px 6px;" onclick="openProductModal()">...</button>
                    </td>
                    <td><input type="text" id="input-des" disabled></td>
                    <td><input type="text" id="input-taille" disabled></td>
                    <td><input type="text" id="input-couleur" disabled></td>
                    <td><input type="number" id="input-qte" value="1" min="1"></td>
                    <td><input type="text" id="input-prix" disabled></td>
                    <td style="text-align: center; display: flex; gap: 5px; justify-content: center;">
                        <button type="button" class="btn-header" style="padding: 2px 6px; color: #16a34a; border-color: #16a34a;" onclick="addLine(event)">+</button>
                        <button type="button" class="btn-header" style="padding: 2px 6px; color: #ef4444; border-color: #ef4444;" onclick="clearInputRow(event)">x</button>
                    </td>
                </tr>
                <!-- Lignes de détail existantes -->
                @if(isset($lignes) && count($lignes) > 0)
                    @foreach($lignes as $index => $ligne)
                        <tr class="existing-row">
                            <td>{{ $ligne->reference ?? $ligne->produitcode }}</td>
                            <td>{{ $ligne->produitlibelle }}</td>
                            <td>{{ $ligne->taillelibelle }}</td>
                            <td>{{ $ligne->couleurlibelle }}</td>
                            <td>{{ $ligne->qte }}</td>
                            <td>{{ number_format($ligne->ttc, 2) }}</td>
                            <td style="text-align: center;">
                                <button type="button" class="btn-header" style="padding: 2px 6px; color: #ef4444; border-color: #ef4444;" onclick="this.closest('tr').remove(); updateFooter();" {{ isset($demande) && $demande->etatdemandetransfertid != 1 ? 'disabled' : '' }}>x</button>
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
                    <td colspan="7" style="padding: 40px; text-align: center; color: var(--text-muted); font-weight: 600;">
                        No data to display
                        <br><br>
                        <span style="font-weight: 400; font-size: 11px;">(Veuillez ajouter des produits avec la zone ci-dessus)</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    </form>

    <!-- Footer -->
    <div class="footer-bar">
        <div>Nombre de ligne 0</div>
        <div>0</div>
    </div>
    <div style="padding: 10px 15px; background: #f8fafc; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
        <div style="display: flex; align-items: center; justify-content: center; width: 24px; height: 24px; background: #7e22ce; color: white; border-radius: 4px; font-weight: bold; font-size: 12px;">1</div>
        <div style="font-size: 12px; color: var(--text-secondary);">
            Page Size: 
            <select style="padding: 4px; border: 1px solid var(--border); border-radius: 4px;">
                <option>20</option>
            </select>
        </div>
    </div>

    <!-- Modal Sélection des Produits -->
    <div id="productModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <div style="flex:1; display:flex; justify-content:center;">
                    <h2 style="margin:0; font-size: 16px; font-weight: 700; color: var(--text);">Sélection des produits</h2>
                </div>
                <div style="display: flex; gap: 5px;">
                    <button class="btn-header" style="padding: 6px 15px;" onclick="document.getElementById('productModal').style.display='none'">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </button>
                    <button class="btn-header" style="padding: 6px 15px;" onclick="document.getElementById('productModal').style.display='none'">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
    @include('transfert.demande_envoye.partials.product_modal')

</div>

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
        searchProducts(); // Load initially or wait for click? Let's load initially
    }

    function searchProducts() {
        const sousFamille = document.getElementById('filter-sf').value;
        const famille = document.getElementById('filter-f').value;
        const saison = document.getElementById('filter-s').value;
        const categorie = document.getElementById('filter-c').value;
        const marque = document.getElementById('filter-m').value;
        const search = document.getElementById('filter-search').value;

        const url = new URL('{{ route("transfert.demande_envoye.search_products") }}');
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
                tbody.innerHTML = '';
                
                if(data.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="9" style="padding: 40px; text-align: center; color: var(--text-muted); font-weight: 600;">No data to display</td></tr>`;
                    return;
                }

                data.forEach(p => {
                    const tr = document.createElement('tr');
                    tr.style.cursor = 'pointer';
                    tr.onmouseover = () => tr.style.background = '#f1f5f9';
                    tr.onmouseout = () => tr.style.background = 'white';
                    tr.onclick = () => selectProduct(p);

                    tr.innerHTML = `
                        <td>${p.produitcode || ''}</td>
                        <td>${p.reference || ''}</td>
                        <td>${p.barcode2 || ''}</td>
                        <td>${p.produitlibelle || ''}</td>
                        <td>${p.famillelibelle || ''}</td>
                        <td>${p.sousfamillelibelle || ''}</td>
                        <td>${p.ttc_vente || ''}</td>
                        <td>${p.total_stock || 0}</td>
                        <td>${p.fournisseur || ''}</td>
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
        document.getElementById('input-prix').value = product.ttc_vente || '';
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

        const tbody = document.querySelector('.grid-table tbody');
        const emptyRow = document.getElementById('empty-row');
        if(emptyRow) emptyRow.style.display = 'none';

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${currentSelectedProduct.reference || currentSelectedProduct.produitcode}</td>
            <td>${currentSelectedProduct.produitlibelle || ''}</td>
            <td>${currentSelectedProduct.taillelibelle || ''}</td>
            <td>${currentSelectedProduct.couleurlibelle || ''}</td>
            <td>${qte}</td>
            <td>${currentSelectedProduct.ttc_vente || ''}</td>
            <td style="text-align: center;">
                <button type="button" class="btn-header" style="padding: 2px 6px; color: #ef4444; border-color: #ef4444;" onclick="this.closest('tr').remove(); updateFooter();">x</button>
                <input type="hidden" name="lignes[${lineIndex}][produitid]" value="${currentSelectedProduct.produitid}">
                <input type="hidden" name="lignes[${lineIndex}][produit2id]" value="${currentSelectedProduct.produit2id}">
                <input type="hidden" name="lignes[${lineIndex}][qte]" value="${qte}">
                <input type="hidden" name="lignes[${lineIndex}][prix]" value="${currentSelectedProduct.ttc_vente || 0}">
            </td>
        `;

        tbody.appendChild(tr);
        lineIndex++;
        
        updateFooter();
        clearInputRow();
    }

    function updateFooter() {
        const rows = document.querySelectorAll('.grid-table tbody tr:not(.input-row):not(#empty-row)');
        let totalQte = 0;
        
        rows.forEach(row => {
            const qteInput = row.querySelector('input[name$="[qte]"]');
            if(qteInput) {
                totalQte += parseFloat(qteInput.value) || 0;
            }
        });

        const footerDivs = document.querySelectorAll('.footer-bar div');
        if(footerDivs.length >= 2) {
            footerDivs[0].textContent = 'Nombre de ligne ' + rows.length;
            footerDivs[1].textContent = totalQte;
        }

        if(rows.length === 0) {
            const emptyRow = document.getElementById('empty-row');
            if(emptyRow) emptyRow.style.display = 'table-row';
        }
    }

    // Support Enter key on Qte
    document.getElementById('input-qte').addEventListener('keypress', function(e) {
        if(e.key === 'Enter') {
            e.preventDefault();
            addLine();
        }
    });
</script>
@endsection
