@extends('layouts.app')

@section('title', 'Etat De Stock')

@section('styles')
<style>
    .pagination { display: flex; list-style: none; padding: 0; margin: 0; gap: 4px; align-items: center; }
    .pagination li a, .pagination li span { display: block; padding: 6px 12px; border: 1px solid var(--border); border-radius: 4px; color: var(--text); text-decoration: none; background: white; font-size: 12px; }
    .pagination li.active span { background: #0284c7; color: white; border-color: #0284c7; }
    .pagination li.disabled span { opacity: 0.5; background: #f1f5f9; cursor: not-allowed; }
    .pagination li a:hover { background: #f8fafc; }
    
    .filter-bar {
        display: flex;
        align-items: center;
        background: white;
        padding: 10px 15px;
        border: 1px solid var(--border);
        border-radius: 8px;
        box-shadow: var(--shadow-sm);
        margin-bottom: 20px;
        gap: 15px;
    }

    .text-input {
        padding: 6px 10px;
        border: 1px solid var(--border);
        border-radius: 4px;
        font-size: 13px;
        outline: none;
        width: 250px;
    }
    
    .btn-action {
        background: white;
        border: 1px solid var(--border);
        padding: 6px 12px;
        border-radius: 4px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 32px;
        color: var(--text);
    }
    
    .btn-action:hover {
        background: #f8fafc;
    }

    /* Modal styles */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }

    .modal-content {
        background: white;
        border-radius: 8px;
        width: 600px;
        max-width: 90%;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    }

    .modal-header {
        padding: 15px 20px;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f8fafc;
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
    }

    .modal-title {
        font-weight: 700;
        font-size: 16px;
    }

    .modal-close {
        cursor: pointer;
        background: none;
        border: none;
        font-size: 20px;
        color: var(--text-muted);
    }

    .modal-body {
        padding: 20px;
    }

    .modal-footer {
        padding: 15px 20px;
        border-top: 1px solid var(--border);
        text-align: center;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 15px;
        margin-bottom: 15px;
    }

    .filter-group label {
        display: block;
        font-size: 12px;
        color: var(--text-secondary);
        margin-bottom: 5px;
    }

    .filter-group select {
        width: 100%;
        padding: 8px;
        border: 1px solid var(--border);
        border-radius: 4px;
        font-size: 13px;
    }

    .search-input {
        width: 100%;
        padding: 4px 8px;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        font-size: 11px;
        margin-top: 4px;
        box-sizing: border-box;
    }
</style>
@endsection

@section('content')
<div class="main-content-inner full-width" style="padding: 0 20px;">
    
    <form method="GET" action="{{ route('stock.etat.index') }}" id="filterForm">
        <!-- Barre du haut -->
        <div class="filter-bar">
            <div style="font-size: 16px; font-weight: 700; color: var(--text); display: flex; align-items: center; margin-right: 15px;">
                Etat De Stock
            </div>
            
            <input type="text" name="search" class="text-input" placeholder="" value="{{ request('search') }}">

            <div style="display: flex; align-items: center; gap: 8px; margin-left: auto;">
                <button type="submit" class="btn-action">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </button>
                <button type="button" class="btn-action" onclick="document.getElementById('filterModal').style.display='flex'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                    </svg>
                </button>
                <button type="button" class="btn-action" onclick="window.print()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                        <rect x="6" y="14" width="12" height="8"></rect>
                    </svg>
                </button>
                <a href="{{ route('stock.etat.index') }}" class="btn-action">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </a>
            </div>
        </div>

        <!-- Modal Filtre -->
        <div id="filterModal" class="modal-overlay">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="modal-title">Filtre</span>
                    <button type="button" class="modal-close" onclick="document.getElementById('filterModal').style.display='none'">×</button>
                </div>
                <div class="modal-body">
                    <div class="filter-grid">
                        <div class="filter-group">
                            <label>Sous Famille</label>
                            <select name="sousfamilleid">
                                <option value="">Select Sous Famille...</option>
                                @foreach($sousFamilles as $sf)
                                    <option value="{{ $sf->sousfamilleid }}" {{ request('sousfamilleid') == $sf->sousfamilleid ? 'selected' : '' }}>
                                        {{ $sf->sousfamillelibelle }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Famille</label>
                            <select name="familleid">
                                <option value="">Select Famille...</option>
                                @foreach($familles as $f)
                                    <option value="{{ $f->familleid }}" {{ request('familleid') == $f->familleid ? 'selected' : '' }}>
                                        {{ $f->famillelibelle }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Saison</label>
                            <select name="saisonid">
                                <option value="">Select Saison...</option>
                                @foreach($saisons as $s)
                                    <option value="{{ $s->category4id }}" {{ request('saisonid') == $s->category4id ? 'selected' : '' }}>
                                        {{ $s->category4libelle }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="filter-grid" style="grid-template-columns: 1fr; width: 33%;">
                        <div class="filter-group">
                            <label>Rayon</label>
                            <select name="rayonid">
                                <option value="">Select Rayon...</option>
                                @foreach($rayons as $r)
                                    <option value="{{ $r->categoryid }}" {{ request('rayonid') == $r->categoryid ? 'selected' : '' }}>
                                        {{ $r->categorylibelle }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn-action" style="padding: 8px 30px; border-color: var(--border);">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </form>

    <div style="background: white; border: 1px solid var(--border); border-radius: 12px; box-shadow: var(--shadow-sm); overflow: hidden; display: flex; flex-direction: column; min-height: 600px;">
        <div style="overflow-x: auto; flex: 1;">
            <table id="dataTable" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 12px;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 1px solid var(--border);">
                        <th style="padding: 8px; font-weight: 600; color: var(--text-secondary); border-right: 1px solid var(--border); width: 10%;">
                            Code <input type="text" class="search-input" onkeyup="filterTable(0, this.value)">
                        </th>
                        <th style="padding: 8px; font-weight: 600; color: var(--text-secondary); border-right: 1px solid var(--border); width: 10%;">
                            Couleur <input type="text" class="search-input" onkeyup="filterTable(1, this.value)">
                        </th>
                        <th style="padding: 8px; font-weight: 600; color: var(--text-secondary); border-right: 1px solid var(--border); width: 8%;">
                            Taille <input type="text" class="search-input" onkeyup="filterTable(2, this.value)">
                        </th>
                        <th style="padding: 8px; font-weight: 600; color: var(--text-secondary); border-right: 1px solid var(--border); text-align: right; width: 6%;">
                            Entrer <input type="text" class="search-input" onkeyup="filterTable(3, this.value)">
                        </th>
                        <th style="padding: 8px; font-weight: 600; color: var(--text-secondary); border-right: 1px solid var(--border); text-align: right; width: 6%;">
                            Sortie <input type="text" class="search-input" onkeyup="filterTable(4, this.value)">
                        </th>
                        <th style="padding: 8px; font-weight: 600; color: var(--text-secondary); border-right: 1px solid var(--border); text-align: right; width: 6%;">
                            Achat <input type="text" class="search-input" onkeyup="filterTable(5, this.value)">
                        </th>
                        <th style="padding: 8px; font-weight: 600; color: var(--text-secondary); border-right: 1px solid var(--border); text-align: right; width: 6%;">
                            Ret.Achat <input type="text" class="search-input" onkeyup="filterTable(6, this.value)">
                        </th>
                        <th style="padding: 8px; font-weight: 600; color: var(--text-secondary); border-right: 1px solid var(--border); text-align: right; width: 6%;">
                            Vente <input type="text" class="search-input" onkeyup="filterTable(7, this.value)">
                        </th>
                        <th style="padding: 8px; font-weight: 600; color: var(--text-secondary); border-right: 1px solid var(--border); text-align: right; width: 6%;">
                            Ret.Vente <input type="text" class="search-input" onkeyup="filterTable(8, this.value)">
                        </th>
                        <th style="padding: 8px; font-weight: 600; color: var(--text-secondary); border-right: 1px solid var(--border); text-align: right; width: 6%;">
                            Dispo <input type="text" class="search-input" onkeyup="filterTable(9, this.value)">
                        </th>
                        <th style="padding: 8px; font-weight: 600; color: var(--text-secondary); border-right: 1px solid var(--border); text-align: right; width: 8%;">
                            PV.TTC <input type="text" class="search-input" onkeyup="filterTable(10, this.value)">
                        </th>
                        <th style="padding: 8px; font-weight: 600; color: var(--text-secondary); text-align: right; width: 8%;">
                            Val Au (PV) <input type="text" class="search-input" onkeyup="filterTable(11, this.value)">
                        </th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @php
                        $sumEntrer = 0;
                        $sumSortie = 0;
                        $sumAchat = 0;
                        $sumRetAchat = 0;
                        $sumVente = 0;
                        $sumRetVente = 0;
                        $sumDispo = 0;
                        $sumPv = 0;
                        $sumValPv = 0;
                    @endphp
                    @forelse($etatStocks as $item)
                    @php
                        $entrer = $item->total_entrer ?? 0;
                        $sortie = $item->total_sortie ?? 0;
                        $achat = $item->total_achat ?? 0;
                        $ret_achat = $item->total_ret_achat ?? 0;
                        $vente = $item->total_vente ?? 0;
                        $ret_vente = $item->total_ret_vente ?? 0;
                        $dispo = $item->dispo ?? 0;
                        $pv = $item->pv_ttc ?? 0;
                        $val_pv = $item->val_au_pv ?? 0;

                        $sumEntrer += $entrer;
                        $sumSortie += $sortie;
                        $sumAchat += $achat;
                        $sumRetAchat += $ret_achat;
                        $sumVente += $vente;
                        $sumRetVente += $ret_vente;
                        $sumDispo += $dispo;
                        $sumPv += $pv;
                        $sumValPv += $val_pv;
                    @endphp
                    <tr style="border-bottom: 1px solid var(--border); transition: background 0.2s;" class="data-row" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 6px 8px; color: var(--text); border-right: 1px solid var(--border);">{{ $item->produitcode }}</td>
                        <td style="padding: 6px 8px; color: var(--text); border-right: 1px solid var(--border);">{{ $item->couleurlibelle }}</td>
                        <td style="padding: 6px 8px; color: var(--text); border-right: 1px solid var(--border);">{{ $item->taillelibelle }}</td>
                        <td style="padding: 6px 8px; color: var(--text); border-right: 1px solid var(--border); text-align: right;">{{ $entrer != 0 ? number_format($entrer, 0, '', '') : '0' }}</td>
                        <td style="padding: 6px 8px; color: var(--text); border-right: 1px solid var(--border); text-align: right;">{{ $sortie != 0 ? number_format($sortie, 0, '', '') : '0' }}</td>
                        <td style="padding: 6px 8px; color: var(--text); border-right: 1px solid var(--border); text-align: right;">{{ $achat != 0 ? number_format($achat, 0, '', '') : '0' }}</td>
                        <td style="padding: 6px 8px; color: var(--text); border-right: 1px solid var(--border); text-align: right;">{{ $ret_achat != 0 ? number_format($ret_achat, 0, '', '') : '0' }}</td>
                        <td style="padding: 6px 8px; color: var(--text); border-right: 1px solid var(--border); text-align: right;">{{ $vente != 0 ? number_format($vente, 0, '', '') : '0' }}</td>
                        <td style="padding: 6px 8px; color: var(--text); border-right: 1px solid var(--border); text-align: right;">{{ $ret_vente != 0 ? number_format($ret_vente, 0, '', '') : '0' }}</td>
                        <td style="padding: 6px 8px; color: {{ $dispo < 0 ? '#ef4444' : ($dispo > 0 ? '#22c55e' : 'var(--text)') }}; font-weight: 600; border-right: 1px solid var(--border); text-align: right;">{{ $dispo != 0 ? number_format($dispo, 0, '', '') : '0' }}</td>
                        <td style="padding: 6px 8px; color: var(--text); border-right: 1px solid var(--border); text-align: right;">{{ number_format($pv, 3, '.', '') }}</td>
                        <td style="padding: 6px 8px; font-weight: 600; color: {{ $val_pv < 0 ? '#ef4444' : 'var(--text)' }}; text-align: right;">{{ number_format($val_pv, 3, '.', '') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" style="padding: 50px; text-align: center; color: var(--text-muted); font-size: 16px; font-weight: 600;">
                            No data to display
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr style="background: #f1f5f9; font-weight: 700; border-top: 2px solid var(--border);">
                        <td colspan="3" style="padding: 10px 8px; border-right: 1px solid var(--border);">Nombre {{ $etatStocks->total() }}</td>
                        <td style="padding: 10px 8px; text-align: right; border-right: 1px solid var(--border);">{{ $sumEntrer != 0 ? number_format($sumEntrer, 0, '', '') : '0' }}</td>
                        <td style="padding: 10px 8px; text-align: right; border-right: 1px solid var(--border);">{{ $sumSortie != 0 ? number_format($sumSortie, 0, '', '') : '0' }}</td>
                        <td style="padding: 10px 8px; text-align: right; border-right: 1px solid var(--border);">{{ $sumAchat != 0 ? number_format($sumAchat, 0, '', '') : '0' }}</td>
                        <td style="padding: 10px 8px; text-align: right; border-right: 1px solid var(--border);">{{ $sumRetAchat != 0 ? number_format($sumRetAchat, 0, '', '') : '0' }}</td>
                        <td style="padding: 10px 8px; text-align: right; border-right: 1px solid var(--border);">{{ $sumVente != 0 ? number_format($sumVente, 0, '', '') : '0' }}</td>
                        <td style="padding: 10px 8px; text-align: right; border-right: 1px solid var(--border);">{{ $sumRetVente != 0 ? number_format($sumRetVente, 0, '', '') : '0' }}</td>
                        <td style="padding: 10px 8px; text-align: right; border-right: 1px solid var(--border); color: {{ $sumDispo < 0 ? '#ef4444' : 'inherit' }}">{{ $sumDispo != 0 ? number_format($sumDispo, 0, '', '') : '0' }}</td>
                        <td style="padding: 10px 8px; text-align: right; border-right: 1px solid var(--border);">{{ number_format($sumPv, 3, '.', '') }}</td>
                        <td style="padding: 10px 8px; text-align: right; color: {{ $sumValPv < 0 ? '#ef4444' : 'inherit' }}">{{ number_format($sumValPv, 3, '.', '') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <div style="padding: 10px 15px; border-top: 1px solid var(--border); background: white; display: flex; justify-content: flex-end; align-items: center;">
            <div>
                {{ $etatStocks->appends(request()->query())->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>

<script>
function filterTable(colIndex, value) {
    const table = document.getElementById("dataTable");
    const rows = table.getElementsByClassName("data-row");
    const filter = value.toUpperCase();

    for (let i = 0; i < rows.length; i++) {
        const td = rows[i].getElementsByTagName("td")[colIndex];
        if (td) {
            const textValue = td.textContent || td.innerText;
            if (textValue.toUpperCase().indexOf(filter) > -1) {
                rows[i].style.display = "";
            } else {
                rows[i].style.display = "none";
            }
        }
    }
}
</script>
@endsection
