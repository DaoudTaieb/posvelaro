@extends('layouts.app')

@section('title', 'Saisie Bon de Transfert Envoyé Velaro')

@section('content')
<div class="pos-container">

    <!-- En-tête -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Saisie Bon de Transfert</h1>
            <p class="page-subtitle">Créez ou modifiez un bon de transfert vers un autre site.</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('transfert.envoye.create') }}" class="btn btn-outline">
                Nouveau
            </a>
            <button type="button" class="btn btn-primary" onclick="submitForm('envoyer')">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="22" y1="2" x2="11" y2="13"></line>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                </svg>
                Envoyer
            </button>
            <button class="btn btn-outline" title="Impression A4">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <rect x="6" y="14" width="12" height="8"></rect>
                </svg>
            </button>
            <a href="{{ route('transfert.envoye.index') }}" class="btn btn-outline" title="Fermer">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
                Fermer
            </a>
        </div>
    </div>

    <!-- Container Formulaire & Tableau -->
    <div class="content-card">
        <form method="POST" action="{{ route('transfert.envoye.store') }}" id="headerForm">
            @csrf
            
            <!-- Onglets (Custom Velaro) -->
            <div style="display: flex; gap: 24px; border-bottom: 1px solid var(--border); margin-bottom: 20px; padding: 0 24px;">
                <div class="tab-link active" onclick="switchTab('general')" style="padding: 12px 4px; font-size: 14px; font-weight: 600; color: var(--primary); border-bottom: 2px solid var(--primary); cursor: pointer;">
                    Général
                </div>
                <div class="tab-link" onclick="switchTab('detail')" style="padding: 12px 4px; font-size: 14px; font-weight: 500; color: var(--text-muted); cursor: pointer;">
                    Détail
                </div>
            </div>

            <div style="padding: 0 24px;">
                <!-- ONGLET GENERAL -->
                <div id="tab-general" class="tab-content-panel" style="display: block;">
                    <div style="display: flex; gap: 20px; margin-bottom: 15px;">
                        <div style="flex: 1;">
                            <label class="form-label">Expéditeur</label>
                            <input type="text" class="form-control" value="{{ $site ? $site->libelle : 'Velaro' }}" disabled style="background: #f8fafc;">
                            <input type="hidden" name="siteid" value="{{ $site ? $site->siteid : '' }}">
                        </div>
                        <div style="flex: 1;">
                            <label class="form-label">Récepteur <span class="text-danger">*</span></label>
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
                        <div style="flex: 1;">
                            <label class="form-label">Type</label>
                            <select name="typetransfertid" class="form-control" {{ isset($bon) ? 'disabled' : '' }}>
                                <option value="1" {{ (isset($bon) && $bon->typetransfertid == 1) ? 'selected' : '' }}>Standard</option>
                                <option value="2" {{ (isset($bon) && $bon->typetransfertid == 2) ? 'selected' : '' }}>Défaut</option>
                                <option value="3" {{ (isset($bon) && $bon->typetransfertid == 3) ? 'selected' : '' }}>Déparé</option>
                            </select>
                        </div>
                    </div>
                    <div style="display: flex; gap: 20px; margin-bottom: 15px;">
                        <div style="flex: 2;">
                            <label class="form-label">Observation</label>
                            <input type="text" name="description" class="form-control" placeholder="Entrez une observation..." value="{{ $bon->description ?? '' }}">
                        </div>
                        <div style="display: flex; align-items: flex-end;">
                            <button type="submit" class="btn btn-primary" title="Enregistrer brouillon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                    <polyline points="7 3 7 8 15 8"></polyline>
                                </svg>
                                Enregistrer
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ONGLET DETAIL -->
                <div id="tab-detail" class="tab-content-panel" style="display: none;">
                    <div style="display: flex; gap: 20px; margin-bottom: 15px;">
                        <div style="flex: 1;">
                            <label class="form-label">Chauffeur</label>
                            <select name="chauffeurid" class="form-control">
                                <option value=""></option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->employeeid }}" {{ (isset($bon) && $bon->chauffeurid == $emp->employeeid) ? 'selected' : '' }}>{{ $emp->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div style="flex: 1;">
                            <label class="form-label">Véhicule</label>
                            <select name="vehiculeid" class="form-control">
                                <option value=""></option>
                                @foreach($vehicules as $v)
                                    <option value="{{ $v->vehiculeid }}" {{ (isset($bon) && $bon->vehiculeid == $v->vehiculeid) ? 'selected' : '' }}>{{ $v->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div style="flex: 1;">
                            <label class="form-label">Trajet</label>
                            <input type="text" name="trajet" class="form-control" value="{{ $bon->trajet ?? '' }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABLEAU DES LIGNES -->
            <div class="table-responsive" style="margin-top: 20px;">
                <table class="data-table" id="dataTable">
                    <thead>
                        <tr>
                            <th style="width: 15%;">Réf</th>
                            <th>Désignation</th>
                            <th style="width: 10%;">Taille</th>
                            <th style="width: 10%;">Couleur</th>
                            <th style="width: 10%; text-align: center;">Qté</th>
                            <th style="width: 10%; text-align: center;">Qté Envoi</th>
                            <th style="width: 12%;">Prix Vente</th>
                            <th style="width: 60px; text-align: center;">Act.</th>
                        </tr>
                        <!-- Ligne de filtre locale pour le tableau en mémoire -->
                        <tr class="filter-row">
                            <th><input type="text" id="filterRef" class="filter-col" placeholder="Filtrer..."></th>
                            <th><input type="text" id="filterDesig" class="filter-col" placeholder="Filtrer..."></th>
                            <th><input type="text" id="filterTaille" class="filter-col" placeholder="Filtrer..."></th>
                            <th><input type="text" id="filterCouleur" class="filter-col" placeholder="Filtrer..."></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="linesTableBody">
                        <!-- Ligne de saisie -->
                        <tr id="inputRow" class="input-row" style="background: var(--primary-light); {{ isset($bon) ? 'display:none;' : '' }}">
                            <td style="display: flex; gap: 5px;">
                                <input type="text" id="searchInput" class="form-control" style="height: 28px; padding: 4px 8px;" placeholder="Réf..." autocomplete="off">
                                <button type="button" class="btn btn-outline" style="width: 28px; height: 28px; padding: 0; display: flex; align-items: center; justify-content: center;" onclick="openProductModal()">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="11" cy="11" r="8"></circle>
                                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                    </svg>
                                </button>
                            </td>
                            <td><input type="text" id="designationInput" class="form-control" style="height: 28px; padding: 4px 8px; background: #f1f5f9;" disabled></td>
                            <td><input type="text" id="tailleInput" class="form-control" style="height: 28px; padding: 4px 8px; background: #f1f5f9;" disabled></td>
                            <td><input type="text" id="couleurInput" class="form-control" style="height: 28px; padding: 4px 8px; background: #f1f5f9;" disabled></td>
                            <td><input type="number" id="qteInput" class="form-control" style="height: 28px; padding: 4px 8px; text-align: center;" value="1" min="1"></td>
                            <td><input type="number" id="qteEnvoiInput" class="form-control" style="height: 28px; padding: 4px 8px; text-align: center;" value="1" min="1"></td>
                            <td><input type="text" id="prixInput" class="form-control" style="height: 28px; padding: 4px 8px; background: #f1f5f9;" disabled></td>
                            <td style="text-align: center;">
                                <button type="button" class="btn" style="color: var(--success); padding: 4px; background: transparent;" onclick="addLine()">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="12" y1="5" x2="12" y2="19"></line>
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                    </svg>
                                </button>
                                <button type="button" class="btn" style="color: var(--danger); padding: 4px; background: transparent;" onclick="clearInput()">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                    </svg>
                                </button>
                            </td>
                        </tr>

                        <!-- Lignes existantes -->
                        @if(isset($lignes) && count($lignes) > 0)
                            @foreach($lignes as $index => $ligne)
                                <tr class="existing-row hover-row" data-ref="{{ strtolower($ligne->produitcode) }}" data-desig="{{ strtolower($ligne->designation) }}" data-taille="{{ strtolower($ligne->taille) }}" data-couleur="{{ strtolower($ligne->couleur) }}">
                                    <td class="font-medium" style="color: var(--primary);">
                                        {{ $ligne->produitcode }}
                                        <input type="hidden" name="lignes[{{ $index }}][produitid]" value="{{ $ligne->produitid }}">
                                        <input type="hidden" name="lignes[{{ $index }}][produit2id]" value="{{ $ligne->produit2id }}">
                                    </td>
                                    <td>{{ $ligne->designation }}</td>
                                    <td>{{ $ligne->taille }}</td>
                                    <td>{{ $ligne->couleur }}</td>
                                    <td style="text-align: center;">
                                        <input type="number" name="lignes[{{ $index }}][qte]" value="{{ (int)$ligne->qte }}" class="form-control" style="width: 70px; height: 28px; padding: 4px; display: inline-block; text-align: center;" min="1" {{ isset($bon) ? 'readonly' : '' }}>
                                    </td>
                                    <td style="text-align: center;">
                                        <input type="number" name="lignes[{{ $index }}][qteenvoi]" value="{{ (int)($ligne->qteenvoi ?? $ligne->qte) }}" class="form-control" style="width: 70px; height: 28px; padding: 4px; display: inline-block; text-align: center;" min="1" onchange="updateTotals()">
                                    </td>
                                    <td>
                                        {{ number_format($ligne->ttc, 3, '.', '') }}
                                        <input type="hidden" name="lignes[{{ $index }}][prix]" value="{{ $ligne->ttc }}">
                                    </td>
                                    <td style="text-align: center;">
                                        @if(!isset($bon))
                                        <button type="button" class="btn" style="color: var(--danger); padding: 4px; background: transparent;" onclick="this.closest('tr').remove(); updateTotals();">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            </svg>
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr id="empty-row">
                                <td colspan="8" style="padding: 40px; text-align: center; color: var(--text-muted);">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 16px; opacity: 0.5;">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                        <line x1="16" y1="13" x2="8" y2="13"></line>
                                        <line x1="16" y1="17" x2="8" y2="17"></line>
                                        <polyline points="10 9 9 9 8 9"></polyline>
                                    </svg>
                                    <div style="font-weight: 600; font-size: 15px; margin-bottom: 8px;">Aucune ligne ajoutée</div>
                                    <div style="font-size: 13px;">Recherchez un produit pour commencer la saisie.</div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            
            <!-- Footer Infos -->
            <div style="padding: 15px 24px; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: #f8fafc; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;">
                <div style="font-weight: 600; color: var(--text-muted);">Nombre de lignes : <span id="lineCount" style="color: var(--text-main);">{{ isset($lignes) ? count($lignes) : 0 }}</span></div>
                <div style="display: flex; gap: 40px; font-weight: 600;">
                    <div>Total Qté Demandée : <span id="totalQte" style="color: var(--primary);">0</span></div>
                    <div>Total Qté Envoi : <span id="totalQteEnvoi" style="color: var(--success);">0</span></div>
                </div>
            </div>

        </form>
    </div>
</div>

<!-- Modal Produit (Réutilisé) -->
@include('transfert.demande_envoye.partials.product_modal')

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

    function switchTab(tabId) {
        document.querySelectorAll('.tab-link').forEach(t => {
            t.classList.remove('active');
            t.style.color = 'var(--text-muted)';
            t.style.borderBottom = 'none';
            t.style.fontWeight = '500';
        });
        document.querySelectorAll('.tab-content-panel').forEach(p => p.style.display = 'none');
        
        const targetTab = event.currentTarget;
        targetTab.classList.add('active');
        targetTab.style.color = 'var(--primary)';
        targetTab.style.borderBottom = '2px solid var(--primary)';
        targetTab.style.fontWeight = '600';
        
        document.getElementById('tab-' + tabId).style.display = 'block';
    }

    let currentSelectedProduct = null;
    let lineIndex = {{ isset($lignes) ? count($lignes) : 0 }};

    function openProductModal() {
        document.getElementById('productModal').style.display = 'flex';
        setTimeout(() => document.getElementById('modalSearchInput').focus(), 100);
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
        document.getElementById('prixInput').value = product.ttc_vente || product.ttc || '';
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
        const prix = document.getElementById('prixInput').value;
        
        const emptyRow = document.getElementById('empty-row');
        if (emptyRow) emptyRow.style.display = 'none';

        const tbody = document.getElementById('linesTableBody');
        const tr = document.createElement('tr');
        tr.className = 'existing-row hover-row';
        tr.setAttribute('data-ref', currentSelectedProduct.produitcode.toLowerCase());
        tr.setAttribute('data-desig', currentSelectedProduct.designation.toLowerCase());
        tr.setAttribute('data-taille', (currentSelectedProduct.taille || '').toLowerCase());
        tr.setAttribute('data-couleur', (currentSelectedProduct.couleur || '').toLowerCase());
        
        tr.innerHTML = `
            <td class="font-medium" style="color: var(--primary);">
                ${currentSelectedProduct.produitcode}
                <input type="hidden" name="lignes[${lineIndex}][produitid]" value="${currentSelectedProduct.produitid}">
                <input type="hidden" name="lignes[${lineIndex}][produit2id]" value="${currentSelectedProduct.produit2id || currentSelectedProduct.produitid}">
            </td>
            <td>${currentSelectedProduct.designation}</td>
            <td>${currentSelectedProduct.taille || ''}</td>
            <td>${currentSelectedProduct.couleur || ''}</td>
            <td style="text-align: center;">
                <input type="number" name="lignes[${lineIndex}][qte]" value="${qte}" class="form-control" style="width: 70px; height: 28px; padding: 4px; display: inline-block; text-align: center;" min="1" onchange="updateTotals()">
            </td>
            <td style="text-align: center;">
                <input type="number" name="lignes[${lineIndex}][qteenvoi]" value="${qteEnvoi}" class="form-control" style="width: 70px; height: 28px; padding: 4px; display: inline-block; text-align: center;" min="1" onchange="updateTotals()">
            </td>
            <td>
                ${parseFloat(prix).toFixed(3)}
                <input type="hidden" name="lignes[${lineIndex}][prix]" value="${prix}">
            </td>
            <td style="text-align: center;">
                <button type="button" class="btn" style="color: var(--danger); padding: 4px; background: transparent;" onclick="this.closest('tr').remove(); updateTotals(); filterTable();">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
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
        filterTable(); // re-apply filter to new row
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
                    tbody.innerHTML = `<tr><td colspan="9" style="padding: 40px; text-align: center; color: var(--text-muted); font-weight: 600;">Aucun produit trouvé</td></tr>`;
                    return;
                }

                data.forEach(p => {
                    const tr = document.createElement('tr');
                    tr.style.cursor = 'pointer';
                    tr.onmouseover = () => tr.style.background = '#f1f5f9';
                    tr.onmouseout = () => tr.style.background = 'white';
                    tr.onclick = () => selectProduct(p);

                    tr.innerHTML = `
                        <td class="font-medium" style="color: var(--primary);">${p.produitcode || ''}</td>
                        <td>${p.reference || ''}</td>
                        <td>${p.barcode2 || ''}</td>
                        <td>${p.produitlibelle || ''}</td>
                        <td>${p.famillelibelle || ''}</td>
                        <td>${p.sousfamillelibelle || ''}</td>
                        <td>${parseFloat(p.ttc_vente || p.ttc || 0).toFixed(3)}</td>
                        <td><span class="status-badge ${p.total_stock > 0 ? 'status-paid' : 'status-danger'}">${p.total_stock || 0}</span></td>
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

    // Filtre client sur les lignes
    function filterTable() {
        const filterRef = document.getElementById('filterRef').value.toLowerCase();
        const filterDesig = document.getElementById('filterDesig').value.toLowerCase();
        const filterTaille = document.getElementById('filterTaille').value.toLowerCase();
        const filterCouleur = document.getElementById('filterCouleur').value.toLowerCase();

        const rows = document.querySelectorAll('.existing-row');
        
        rows.forEach(row => {
            const ref = row.getAttribute('data-ref') || '';
            const desig = row.getAttribute('data-desig') || '';
            const taille = row.getAttribute('data-taille') || '';
            const couleur = row.getAttribute('data-couleur') || '';

            let show = true;
            if (filterRef && !ref.includes(filterRef)) show = false;
            if (filterDesig && !desig.includes(filterDesig)) show = false;
            if (filterTaille && !taille.includes(filterTaille)) show = false;
            if (filterCouleur && !couleur.includes(filterCouleur)) show = false;

            row.style.display = show ? 'table-row' : 'none';
        });
    }

    ['filterRef', 'filterDesig', 'filterTaille', 'filterCouleur'].forEach(id => {
        const el = document.getElementById(id);
        if(el) {
            el.addEventListener('input', filterTable);
        }
    });

    // Initialisation
    updateTotals();
</script>
@endsection
