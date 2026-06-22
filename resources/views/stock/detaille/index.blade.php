@extends('layouts.app')
@section('title', 'Stock Détaillé')

@section('content')
<form method="GET" action="{{ route('stock.detaille.index') }}" id="filterForm">
<div class="pos-container">
    
    <!-- Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Stock Détaillé</h1>
            <p class="page-subtitle">Analyse détaillée des mouvements de stock : achats, transferts, ventes, entrées/sorties et écarts.</p>
        </div>
        <div class="header-actions">
            <button type="button" class="btn btn-outline" onclick="openFilterModal()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                </svg>
                Filtres
            </button>
            <a href="{{ route('stock.detaille.index') }}" class="btn btn-outline" title="Réinitialiser tous les filtres">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                    <path d="M3 3v5h5"/>
                </svg>
                Réinitialiser
            </a>
            <button type="button" class="btn btn-outline" onclick="window.print()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <rect x="6" y="14" width="12" height="8"></rect>
                </svg>
                Imprimer
            </button>
        </div>
    </div>

    <!-- KPI Summary Grid -->
    @php
        $sumAchat = 0;
        $sumTransfert = 0;
        $sumVente = 0;
        $sumEs = 0;
        $sumStock = 0;
        $sumEcart = 0;
        foreach($articles as $article) {
            $sumAchat += $article->total_achat;
            $sumTransfert += $article->total_transfert;
            $sumVente += $article->total_vente;
            $sumEs += $article->total_es;
            $sumStock += $article->total_stock;
            $sumEcart += $article->total_ecart;
        }
    @endphp
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon-wrapper bg-indigo-light">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                    <line x1="12" y1="22.08" x2="12" y2="12"></line>
                </svg>
            </div>
            <div class="kpi-info">
                <span class="kpi-label">Articles Filtrés</span>
                <span class="kpi-value">{{ $articles->total() }}</span>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon-wrapper bg-green-light">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
            </div>
            <div class="kpi-info">
                <span class="kpi-label">Total Achats (Page)</span>
                <span class="kpi-value text-success">{{ number_format($sumAchat, 0, ',', ' ') }}</span>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon-wrapper bg-blue-light">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="20" x2="18" y2="10"></line>
                    <line x1="12" y1="20" x2="12" y2="4"></line>
                    <line x1="6" y1="20" x2="6" y2="14"></line>
                </svg>
            </div>
            <div class="kpi-info">
                <span class="kpi-label">Total Ventes (Page)</span>
                <span class="kpi-value text-primary">{{ number_format($sumVente, 0, ',', ' ') }}</span>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon-wrapper bg-red-light">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--danger)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="9" y1="3" x2="9" y2="21"></line>
                    <line x1="15" y1="3" x2="15" y2="21"></line>
                    <line x1="3" y1="9" x2="21" y2="9"></line>
                    <line x1="3" y1="15" x2="21" y2="15"></line>
                </svg>
            </div>
            <div class="kpi-info">
                <span class="kpi-label">Stock Cumulé (Page)</span>
                <span class="kpi-value text-danger">{{ number_format($sumStock, 0, ',', ' ') }}</span>
            </div>
        </div>
    </div>

    <!-- Main Content Card -->
    <div class="content-card" style="min-height: 450px; display: flex; flex-direction: column;">
        
        <!-- Table Grid -->
        <div class="table-responsive" style="flex: 1;">
            <table class="data-table" id="dataTable" style="font-size: 13px;">
                <thead>
                    <tr>
                        <th style="border-right: 1px solid var(--border);">Référence</th>
                        <th style="border-right: 1px solid var(--border);">Code</th>
                        <th style="border-right: 1px solid var(--border);">Libelle</th>
                        <th class="text-right" style="width: 100px; border-right: 1px solid var(--border);">Achat</th>
                        <th class="text-right" style="width: 100px; border-right: 1px solid var(--border);">Transfert</th>
                        <th class="text-right" style="width: 100px; border-right: 1px solid var(--border);">Vente</th>
                        <th class="text-right" style="width: 100px; border-right: 1px solid var(--border);">ES</th>
                        <th class="text-right" style="width: 100px; border-right: 1px solid var(--border); font-weight: 700;">Stock</th>
                        <th class="text-right" style="width: 100px;">Ecart</th>
                    </tr>
                    <tr class="filter-row">
                        <th style="border-right: 1px solid var(--border);">
                            <input type="text" name="ref_search" class="filter-col" value="{{ request('ref_search') }}" placeholder="Filtrer Référence..." onkeypress="if(event.key === 'Enter') this.form.submit();">
                        </th>
                        <th style="border-right: 1px solid var(--border);">
                            <input type="text" name="code_search" class="filter-col" value="{{ request('code_search') }}" placeholder="Filtrer Code..." onkeypress="if(event.key === 'Enter') this.form.submit();">
                        </th>
                        <th style="border-right: 1px solid var(--border);">
                            <input type="text" name="libelle_search" class="filter-col" value="{{ request('libelle_search') }}" placeholder="Filtrer Libelle..." onkeypress="if(event.key === 'Enter') this.form.submit();">
                        </th>
                        <th style="border-right: 1px solid var(--border);">
                            <input type="text" name="achat_search" class="filter-col text-right" value="{{ request('achat_search') }}" placeholder="..." onkeypress="if(event.key === 'Enter') this.form.submit();">
                        </th>
                        <th style="border-right: 1px solid var(--border);">
                            <input type="text" name="transfert_search" class="filter-col text-right" value="{{ request('transfert_search') }}" placeholder="..." onkeypress="if(event.key === 'Enter') this.form.submit();">
                        </th>
                        <th style="border-right: 1px solid var(--border);">
                            <input type="text" name="vente_search" class="filter-col text-right" value="{{ request('vente_search') }}" placeholder="..." onkeypress="if(event.key === 'Enter') this.form.submit();">
                        </th>
                        <th style="border-right: 1px solid var(--border);">
                            <input type="text" name="es_search" class="filter-col text-right" value="{{ request('es_search') }}" placeholder="..." onkeypress="if(event.key === 'Enter') this.form.submit();">
                        </th>
                        <th style="border-right: 1px solid var(--border);">
                            <input type="text" name="stock_search" class="filter-col text-right" value="{{ request('stock_search') }}" placeholder="..." onkeypress="if(event.key === 'Enter') this.form.submit();">
                        </th>
                        <th>
                            <input type="text" name="ecart_search" class="filter-col text-right" value="{{ request('ecart_search') }}" placeholder="..." onkeypress="if(event.key === 'Enter') this.form.submit();">
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($articles as $article)
                    <tr class="data-row">
                        <td style="color: var(--text-secondary); border-right: 1px solid var(--border);">{{ $article->reference }}</td>
                        <td class="font-medium" style="color: var(--text); border-right: 1px solid var(--border);">{{ $article->produitcode }}</td>
                        <td style="font-weight: 500; color: var(--text-main); border-right: 1px solid var(--border);">{{ $article->produitlibelle }}</td>
                        <td class="amount-cell" style="border-right: 1px solid var(--border);">{{ number_format($article->total_achat, 0, ',', ' ') }}</td>
                        <td class="amount-cell" style="border-right: 1px solid var(--border);">{{ number_format($article->total_transfert, 0, ',', ' ') }}</td>
                        <td class="amount-cell" style="border-right: 1px solid var(--border);">{{ number_format($article->total_vente, 0, ',', ' ') }}</td>
                        <td class="amount-cell text-muted" style="border-right: 1px solid var(--border);">{{ number_format($article->total_es, 0, ',', ' ') }}</td>
                        <td class="amount-cell font-bold" style="border-right: 1px solid var(--border); background: rgba(99, 102, 241, 0.02); color: {{ $article->total_stock < 0 ? 'var(--danger)' : ($article->total_stock > 0 ? 'var(--success)' : 'var(--text-main)') }};">
                            {{ number_format($article->total_stock, 0, ',', ' ') }}
                        </td>
                        <td class="amount-cell font-medium" style="color: {{ $article->total_ecart < 0 ? 'var(--danger)' : ($article->total_ecart > 0 ? 'var(--success)' : 'var(--text-muted)') }};">
                            {{ number_format($article->total_ecart, 0, ',', ' ') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9">
                            <div class="empty-state" style="padding: 60px 24px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                                </svg>
                                <p>Aucune donnée chargée</p>
                                <span style="font-size: 13px; color: var(--text-muted);">Veuillez cliquer sur le bouton "Filtres" ci-dessus pour sélectionner une Famille, Rayon ou Saison et charger les articles.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if(count($articles) > 0)
                <tfoot>
                    <tr class="table-totals">
                        <td colspan="3" class="totals-label" style="border-right: 1px solid var(--border);">Total ({{ $articles->total() }} Articles)</td>
                        <td class="amount-cell" style="border-right: 1px solid var(--border);">{{ number_format($sumAchat, 0, ',', ' ') }}</td>
                        <td class="amount-cell" style="border-right: 1px solid var(--border);">{{ number_format($sumTransfert, 0, ',', ' ') }}</td>
                        <td class="amount-cell" style="border-right: 1px solid var(--border);">{{ number_format($sumVente, 0, ',', ' ') }}</td>
                        <td class="amount-cell text-muted" style="border-right: 1px solid var(--border);">{{ number_format($sumEs, 0, ',', ' ') }}</td>
                        <td class="amount-cell font-bold text-primary" style="border-right: 1px solid var(--border); background: rgba(99, 102, 241, 0.05);">{{ number_format($sumStock, 0, ',', ' ') }}</td>
                        <td class="amount-cell font-medium">{{ number_format($sumEcart, 0, ',', ' ') }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        <!-- Pagination Wrapper -->
        @if($articles->hasPages())
        <div class="pagination-wrapper">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                <div>
                    {{ $articles->appends(request()->query())->links('pagination::bootstrap-4') }}
                </div>
                <div style="font-size: 13px; color: var(--text-muted); font-weight: 500;">
                    Affichage de {{ $articles->firstItem() }} à {{ $articles->lastItem() }} sur {{ $articles->total() }} résultats
                </div>
            </div>
        </div>
        @endif

    </div>
</div>

<!-- Modal Filtre (Modal Backdrop layout) -->
<div id="filterModal" class="modal-backdrop" style="display: none;">
    <div class="modal-content" style="max-width: 600px; padding: 24px; border-radius: var(--radius-lg); transform: scale(0.95); transition: transform 0.3s;">
        <!-- Modal close button -->
        <button type="button" onclick="closeFilterModal()" class="modal-close" style="top: 20px; right: 20px;">×</button>
        
        <!-- Header title inside modal -->
        <div style="font-size: 18px; font-weight: 700; color: var(--text-main); margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-primary" style="color: var(--primary);">
                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
            </svg>
            Filtres du Stock Détaillé
        </div>
        
        <div class="modal-body-wrapper">
            <!-- Row 1 -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Famille</label>
                    <select name="familleid" class="form-control" style="height: 38px; padding: 6px 12px;">
                        <option value="">Sélectionner Famille...</option>
                        @foreach($familles as $f)
                            <option value="{{ $f->familleid }}" {{ request('familleid') == $f->familleid ? 'selected' : '' }}>{{ $f->famillelibelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Sous-Famille</label>
                    <select name="sousfamilleid" class="form-control" style="height: 38px; padding: 6px 12px;">
                        <option value="">Sélectionner Sous-Famille...</option>
                        @foreach($sousFamilles as $sf)
                            <option value="{{ $sf->sousfamilleid }}" {{ request('sousfamilleid') == $sf->sousfamilleid ? 'selected' : '' }}>{{ $sf->sousfamillelibelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Saison</label>
                    <select name="saisonid" class="form-control" style="height: 38px; padding: 6px 12px;">
                        <option value="">Sélectionner Saison...</option>
                        @foreach($saisons as $s)
                            <option value="{{ $s->category4id }}" {{ request('saisonid') == $s->category4id ? 'selected' : '' }}>{{ $s->category4libelle }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <!-- Row 2 -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 16px; margin-top: 16px;">
                <div class="form-group" style="max-width: 32%;">
                    <label class="form-label">Rayon</label>
                    <select name="rayonid" class="form-control" style="height: 38px; padding: 6px 12px;">
                        <option value="">Sélectionner Rayon...</option>
                        @foreach($rayons as $r)
                            <option value="{{ $r->categoryid }}" {{ request('rayonid') == $r->categoryid ? 'selected' : '' }}>{{ $r->categorylibelle }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Modal footer / actions -->
            <div class="filters-actions" style="margin-top: 24px; padding-top: 16px; display: flex; justify-content: flex-end; gap: 12px; border-top: 1px dashed var(--border);">
                <button type="button" class="btn btn-outline" onclick="closeFilterModal()">Annuler</button>
                <a href="{{ route('stock.detaille.index') }}" class="btn btn-outline">Réinitialiser</a>
                <button type="submit" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px;">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    Appliquer les Filtres
                </button>
            </div>
        </div>
    </div>
</div>
</form>

<script>
// Modal handling
function openFilterModal() {
    const modal = document.getElementById('filterModal');
    modal.style.display = 'flex';
    void modal.offsetWidth;
    modal.classList.add('show');
}

function closeFilterModal() {
    const modal = document.getElementById('filterModal');
    modal.classList.remove('show');
    setTimeout(() => {
        modal.style.display = 'none';
    }, 300);
}

// Click outside close trigger
document.getElementById('filterModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeFilterModal();
    }
});
</script>
@endsection
