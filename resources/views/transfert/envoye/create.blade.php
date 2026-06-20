@extends('layouts.app')

@section('title', 'Saisie Bon de transfert')

@section('styles')
<style>
    .header-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 20px;
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
        cursor: pointer;
        font-size: 13px;
        color: var(--text);
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .btn-header:hover {
        background: #f1f5f9;
    }
    .tabs {
        display: flex;
        border-bottom: 1px solid var(--border);
        background: white;
        padding: 0 20px;
        margin-top: 5px;
    }
    .tab {
        padding: 10px 20px;
        cursor: pointer;
        font-size: 13px;
        color: var(--text-secondary);
        border-bottom: 2px solid transparent;
    }
    .tab.active {
        color: #7e22ce;
        border-bottom-color: #7e22ce;
        font-weight: 600;
    }
    .tab-content-panel {
        display: none;
    }
    .tab-content-panel.active {
        display: block;
    }
    .form-section {
        padding: 15px 20px;
        background: white;
        border-bottom: 1px solid var(--border);
    }
    .form-row {
        display: flex;
        gap: 20px;
        margin-bottom: 15px;
    }
    .form-group {
        flex: 1;
    }
    .form-group label {
        display: block;
        font-size: 12px;
        color: var(--text-secondary);
        margin-bottom: 4px;
    }
    .form-control {
        width: 100%;
        padding: 6px 10px;
        border: 1px solid var(--border);
        border-radius: 4px;
        font-size: 13px;
        box-sizing: border-box;
    }
    .form-control:disabled {
        background: #f1f5f9;
        cursor: not-allowed;
    }
    .btn-save {
        background: white;
        border: 1px solid var(--border);
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 50px;
        height: 32px;
    }
    .btn-save:hover { background: #f1f5f9; }
    
    .table-container {
        background: white;
        margin-top: 10px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
    }
    th {
        padding: 6px 8px;
        font-weight: 600;
        color: var(--text-secondary);
        background: #f8fafc;
        border: 1px solid var(--border);
        border-bottom: 2px solid var(--border);
        text-align: left;
    }
    td {
        padding: 6px 8px;
        border: 1px solid var(--border);
        color: var(--text);
    }
    .input-cell {
        width: 100%;
        border: 1px solid var(--border);
        padding: 4px;
        box-sizing: border-box;
        border-radius: 2px;
        font-size: 11px;
    }
    .input-row {
        background: #f8fafc;
    }
</style>
@endsection

@section('content')
<div class="main-content-inner full-width" style="padding: 0;">
    
    <!-- En-tête -->
    <div class="header-bar">
        <h1 style="font-size: 16px; font-weight: 700; margin: 0;">Saisie Bon de transfert</h1>
        <div class="header-actions">
            <a href="{{ route('transfert.envoye.create') }}" class="btn-header" style="text-decoration: none;">Nouveau</a>
            <button type="button" class="btn-header" onclick="submitForm('envoyer')">Envoyer</button>
            <button class="btn-header" title="Impression A4 Bon de Transfert" style="color: #0284c7;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            </button>
            <button class="btn-header" title="Impression Ticket Bon de Transfert">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            </button>
            <a href="{{ route('transfert.envoye.index') }}" class="btn-header" style="text-decoration: none; padding: 6px 10px;" title="Fermer">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </a>
        </div>
    </div>
    
    <form method="POST" action="{{ route('transfert.envoye.store') }}" id="headerForm">
        @csrf
        
        <!-- Onglets -->
        <div class="tabs">
            <div class="tab active" onclick="switchTab('general')">Général</div>
            <div class="tab" onclick="switchTab('detail')">Détail</div>
        </div>

        <!-- Formulaire principal -->
        <div class="form-section">
            
            <!-- ONGLET GENERAL -->
            <div id="tab-general" class="tab-content-panel active">
                <div class="form-row">
                    <!-- Expéditeur -->
                    <div class="form-group">
                        <label>Expéditeur</label>
                        <input type="text" class="form-control" value="{{ $site ? $site->libelle : 'Velaro' }}" disabled>
                        <input type="hidden" name="siteid" value="{{ $site ? $site->siteid : '' }}">
                    </div>

                    <!-- Récepteur -->
                    <div class="form-group">
                        <label>Récepteur</label>
                        <select name="siterecepteurid" class="form-control" required {{ isset($bon) ? 'disabled' : '' }}>
                            <option value=""></option>
                            @foreach($sites as $s)
                                <option value="{{ $s->siteid }}" {{ (isset($bon) && $bon->siterecepteurid == $s->siteid) ? 'selected' : '' }}>{{ $s->libelle }}</option>
                            @endforeach
                        </select>
                        @if(isset($bon))
                            <input type="hidden" name="siterecepteurid" value="{{ $bon->siterecepteurid }}">
                            <input type="hidden" name="bontransfertid" value="{{ $bon->bontransfertid }}">
                        @endif
                    </div>
                    
                    <!-- Type -->
                    <div class="form-group">
                        <label>Type</label>
                        <select name="typetransfertid" class="form-control" {{ isset($bon) ? 'disabled' : '' }}>
                            <option value="1" {{ (isset($bon) && $bon->typetransfertid == 1) ? 'selected' : '' }}>Standard</option>
                            <option value="2" {{ (isset($bon) && $bon->typetransfertid == 2) ? 'selected' : '' }}>Defaut</option>
                            <option value="3" {{ (isset($bon) && $bon->typetransfertid == 3) ? 'selected' : '' }}>Déparé</option>
                        </select>
                    </div>
                </div>

                <div class="form-row" style="margin-bottom: 0;">
                    <!-- Observation -->
                    <div class="form-group" style="flex: 2;">
                        <label>Observation</label>
                        <input type="text" name="description" class="form-control" placeholder="Observation" value="{{ $bon->description ?? '' }}">
                    </div>
                    
                    <div style="display: flex; align-items: flex-end;">
                        <button type="submit" class="btn-save" title="Enregistrer">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                <polyline points="7 3 7 8 15 8"></polyline>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ONGLET DETAIL -->
            <div id="tab-detail" class="tab-content-panel">
                <div class="form-row" style="margin-bottom: 0;">
                    <div class="form-group">
                        <label>Chauffeur</label>
                        <select name="chauffeurid" class="form-control">
                            <option value=""></option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->employeeid }}" {{ (isset($bon) && $bon->chauffeurid == $emp->employeeid) ? 'selected' : '' }}>{{ $emp->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Véhicule</label>
                        <select name="vehiculeid" class="form-control">
                            <option value=""></option>
                            @foreach($vehicules as $v)
                                <option value="{{ $v->vehiculeid }}" {{ (isset($bon) && $bon->vehiculeid == $v->vehiculeid) ? 'selected' : '' }}>{{ $v->libelle }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Trajet</label>
                        <input type="text" name="trajet" class="form-control" value="{{ $bon->trajet ?? '' }}">
                    </div>
                </div>
            </div>
            
        </div>
    </form>

    <!-- Tableau des lignes -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 15%;">Réf</th>
                    <th>Désignation</th>
                    <th style="width: 8%;">Taille</th>
                    <th style="width: 8%;">Couleur</th>
                    <th style="width: 8%;">Qte</th>
                    <th style="width: 8%;">Qte Envoi</th>
                    <th style="width: 10%;">Prix Vente</th>
                    <th style="width: 5%;"></th>
                </tr>
            </thead>
            <tbody id="linesTableBody">
                <!-- Ligne de saisie -->
                <tr class="input-row" {{ isset($bon) ? 'style=display:none;' : '' }}>
                    <td style="display: flex; gap: 5px;">
                        <input type="text" id="searchInput" class="input-cell" placeholder="Réf..." autocomplete="off">
                        <button type="button" class="btn-save" style="width: 24px; height: 24px;" onclick="openProductModal()">...</button>
                    </td>
                    <td><input type="text" id="designationInput" class="input-cell" disabled></td>
                    <td><input type="text" id="tailleInput" class="input-cell" disabled></td>
                    <td><input type="text" id="couleurInput" class="input-cell" disabled></td>
                    <td><input type="number" id="qteInput" class="input-cell" value="1" min="1"></td>
                    <td><input type="number" id="qteEnvoiInput" class="input-cell" value="1" min="1"></td>
                    <td><input type="text" id="prixInput" class="input-cell" disabled></td>
                    <td style="text-align: center;">
                        <button type="button" class="btn-icon" style="color: #22c55e; border-color: #22c55e;" onclick="addLine()">+</button>
                        <button type="button" class="btn-icon" style="color: #ef4444; border-color: #ef4444;" onclick="clearInput()">x</button>
                    </td>
                </tr>

                <!-- Lignes de détail existantes -->
                @if(isset($lignes) && count($lignes) > 0)
                    @foreach($lignes as $index => $ligne)
                        <tr class="existing-row">
                            <td>
                                {{ $ligne->produitcode }}
                                <input type="hidden" name="lignes[{{ $index }}][produitid]" value="{{ $ligne->produitid }}">
                                <input type="hidden" name="lignes[{{ $index }}][produit2id]" value="{{ $ligne->produit2id }}">
                            </td>
                            <td>{{ $ligne->designation }}</td>
                            <td>{{ $ligne->taille }}</td>
                            <td>{{ $ligne->couleur }}</td>
                            <td>
                                <input type="number" name="lignes[{{ $index }}][qte]" value="{{ (int)$ligne->qte }}" class="input-cell" style="width: 60px;" min="1" {{ isset($bon) ? 'readonly' : '' }}>
                            </td>
                            <td>
                                <input type="number" name="lignes[{{ $index }}][qteenvoi]" value="{{ (int)($ligne->qteenvoi ?? $ligne->qte) }}" class="input-cell" style="width: 60px;" min="1">
                            </td>
                            <td>
                                {{ number_format($ligne->ttc, 3, '.', '') }}
                                <input type="hidden" name="lignes[{{ $index }}][prix]" value="{{ $ligne->ttc }}">
                            </td>
                            <td style="text-align: center;">
                                @if(!isset($bon))
                                <button type="button" class="btn-icon" style="color: #ef4444; padding: 2px 6px;" onclick="this.closest('tr').remove(); updateTotals();">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr id="empty-row">
                        <td colspan="8" style="padding: 40px; text-align: center; color: var(--text-muted); font-size: 14px; font-weight: 600;">
                            No data to display
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
        
        <div style="padding: 10px 15px; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <div style="font-weight: 600; font-size: 11px;">Nombre de ligne <span id="lineCount">{{ isset($lignes) ? count($lignes) : 0 }}</span></div>
            <div style="display: flex; gap: 50px; font-weight: 600; font-size: 11px; margin-right: 150px;">
                <span id="totalQte">0</span>
                <span id="totalQteEnvoi">0</span>
            </div>
        </div>
        
        <div style="padding: 10px 15px; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: white;">
            <div style="background: #7e22ce; color: white; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; border-radius: 4px; font-weight: bold; font-size: 12px;">
                1
            </div>
            <div style="display: flex; align-items: center; gap: 10px; color: var(--text-secondary); font-size: 12px;">
                <span>Page Size:</span>
                <select class="form-control" style="width: 60px; padding: 4px;">
                    <option>15</option>
                    <option>20</option>
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Modal Produit (Réutilisé) -->
@include('transfert.demande_envoye.partials.product_modal')

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

    function switchTab(tabId) {
        // Retirer la classe active de tous les onglets
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-content-panel').forEach(p => p.classList.remove('active'));
        
        // Ajouter la classe active à l'onglet cliqué
        event.target.classList.add('active');
        document.getElementById('tab-' + tabId).classList.add('active');
    }

    let currentSelectedProduct = null;
    let lineIndex = {{ isset($lignes) ? count($lignes) : 0 }};

    function openProductModal() {
        document.getElementById('productModal').style.display = 'flex';
        document.getElementById('modalSearchInput').focus();
    }

    function closeProductModal() {
        document.getElementById('productModal').style.display = 'none';
    }

    function selectProduct(product) {
        currentSelectedProduct = product;
        document.getElementById('searchInput').value = product.produitcode;
        document.getElementById('designationInput').value = product.designation;
        document.getElementById('tailleInput').value = product.taille || '';
        document.getElementById('couleurInput').value = product.couleur || '';
        document.getElementById('prixInput').value = product.ttc;
        document.getElementById('qteInput').value = 1;
        document.getElementById('qteEnvoiInput').value = 1;
        
        closeProductModal();
        document.getElementById('qteInput').focus();
    }

    function clearInput() {
        currentSelectedProduct = null;
        document.getElementById('searchInput').value = '';
        document.getElementById('designationInput').value = '';
        document.getElementById('tailleInput').value = '';
        document.getElementById('couleurInput').value = '';
        document.getElementById('prixInput').value = '';
        document.getElementById('qteInput').value = 1;
        document.getElementById('qteEnvoiInput').value = 1;
    }

    function addLine() {
        if (!currentSelectedProduct) {
            alert('Veuillez sélectionner un produit');
            return;
        }

        const qte = parseInt(document.getElementById('qteInput').value) || 1;
        const qteEnvoi = parseInt(document.getElementById('qteEnvoiInput').value) || 1;
        
        const emptyRow = document.getElementById('empty-row');
        if (emptyRow) emptyRow.style.display = 'none';

        const tbody = document.getElementById('linesTableBody');
        const tr = document.createElement('tr');
        tr.className = 'existing-row';
        
        tr.innerHTML = `
            <td>
                ${currentSelectedProduct.produitcode}
                <input type="hidden" name="lignes[${lineIndex}][produitid]" value="${currentSelectedProduct.produitid}">
                <input type="hidden" name="lignes[${lineIndex}][produit2id]" value="${currentSelectedProduct.produit2id}">
            </td>
            <td>${currentSelectedProduct.designation}</td>
            <td>${currentSelectedProduct.taille || ''}</td>
            <td>${currentSelectedProduct.couleur || ''}</td>
            <td>
                <input type="number" name="lignes[${lineIndex}][qte]" value="${qte}" class="input-cell" style="width: 60px;" min="1" onchange="updateTotals()">
            </td>
            <td>
                <input type="number" name="lignes[${lineIndex}][qteenvoi]" value="${qteEnvoi}" class="input-cell" style="width: 60px;" min="1" onchange="updateTotals()">
            </td>
            <td>
                ${currentSelectedProduct.ttc}
                <input type="hidden" name="lignes[${lineIndex}][prix]" value="${currentSelectedProduct.ttc}">
            </td>
            <td style="text-align: center;">
                <button type="button" class="btn-icon" style="color: #ef4444; padding: 2px 6px;" onclick="this.closest('tr').remove(); updateTotals();">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                </button>
            </td>
        `;

        // Insert just before the empty row
        if (emptyRow) {
            tbody.insertBefore(tr, emptyRow);
        } else {
            tbody.appendChild(tr);
        }

        lineIndex++;
        clearInput();
        updateTotals();
    }

    function updateTotals() {
        const rows = document.querySelectorAll('.existing-row');
        document.getElementById('lineCount').textContent = rows.length;
        
        if (rows.length === 0) {
            const emptyRow = document.getElementById('empty-row');
            if (emptyRow) emptyRow.style.display = 'table-row';
        }
        
        let tQte = 0;
        let tQteEnvoi = 0;
        rows.forEach(row => {
            const qInput = row.querySelector('input[name*="[qte]"]');
            const qeInput = row.querySelector('input[name*="[qteenvoi]"]');
            if (qInput) tQte += parseInt(qInput.value) || 0;
            if (qeInput) tQteEnvoi += parseInt(qeInput.value) || 0;
        });
        
        document.getElementById('totalQte').textContent = tQte;
        document.getElementById('totalQteEnvoi').textContent = tQteEnvoi;
    }

    function searchProducts() {
        const sousFamille = document.getElementById('filter-sf')?.value;
        const famille = document.getElementById('filter-f')?.value;
        const saison = document.getElementById('filter-s')?.value;
        const categorie = document.getElementById('filter-c')?.value;
        const marque = document.getElementById('filter-m')?.value;
        const search = document.getElementById('filter-search')?.value;

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

    document.getElementById('searchInput')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            openProductModal();
            document.getElementById('filter-search').value = this.value;
            searchProducts();
        }
    });

    // Run once on load to init totals if modifying
    updateTotals();
</script>
@endsection
