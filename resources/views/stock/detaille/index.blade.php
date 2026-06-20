@extends('layouts.app')

@section('title', 'Stock Détaillé')

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
        justify-content: space-between;
        background: white;
        padding: 10px 15px;
        border: 1px solid var(--border);
        border-radius: 8px;
        box-shadow: var(--shadow-sm);
        margin-bottom: 20px;
    }

    .search-input {
        width: 100%;
        padding: 4px 8px;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        font-size: 12px;
        margin-top: 4px;
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

    .btn-filter-submit {
        background: white;
        border: 1px solid var(--border);
        padding: 8px 30px;
        border-radius: 4px;
        cursor: pointer;
    }

    .btn-filter-submit:hover {
        background: #f8fafc;
    }
</style>
@endsection

@section('content')
<div class="main-content-inner full-width" style="padding: 0 20px;">
    
    <div class="filter-bar">
        <div style="font-size: 16px; font-weight: 700; color: var(--text); display: flex; align-items: center; gap: 10px;">
            Stock détaillé
        </div>
        
        <div style="display: flex; gap: 8px;">
            <button class="btn-action" onclick="document.getElementById('filterModal').style.display='flex'">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                </svg>
            </button>
            <button class="btn-action" onclick="window.print()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <rect x="6" y="14" width="12" height="8"></rect>
                </svg>
            </button>
        </div>
    </div>

    <!-- Modal Filtre -->
    <div id="filterModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <span class="modal-title">Filtre</span>
                <button class="modal-close" onclick="document.getElementById('filterModal').style.display='none'">×</button>
            </div>
            <form method="GET" action="{{ route('stock.detaille.index') }}">
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
                    <button type="submit" class="btn-filter-submit">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div style="background: white; border: 1px solid var(--border); border-radius: 12px; box-shadow: var(--shadow-sm); overflow: hidden; display: flex; flex-direction: column; min-height: 600px;">
        <div style="overflow-x: auto; flex: 1;">
            <table id="dataTable" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 1px solid var(--border);">
                        <th style="padding: 12px; font-weight: 600; color: var(--text-secondary); border-right: 1px solid var(--border);">
                            Référence
                            <input type="text" class="search-input" onkeyup="filterTable(0, this.value)">
                        </th>
                        <th style="padding: 12px; font-weight: 600; color: var(--text-secondary); border-right: 1px solid var(--border);">
                            Code
                            <input type="text" class="search-input" onkeyup="filterTable(1, this.value)">
                        </th>
                        <th style="padding: 12px; font-weight: 600; color: var(--text-secondary); border-right: 1px solid var(--border);">
                            Libelle
                            <input type="text" class="search-input" onkeyup="filterTable(2, this.value)">
                        </th>
                        <th style="padding: 12px; font-weight: 600; color: var(--text-secondary); border-right: 1px solid var(--border); width: 80px; text-align: right;">Achat</th>
                        <th style="padding: 12px; font-weight: 600; color: var(--text-secondary); border-right: 1px solid var(--border); width: 80px; text-align: right;">Transfert</th>
                        <th style="padding: 12px; font-weight: 600; color: var(--text-secondary); border-right: 1px solid var(--border); width: 80px; text-align: right;">Vente</th>
                        <th style="padding: 12px; font-weight: 600; color: var(--text-secondary); border-right: 1px solid var(--border); width: 80px; text-align: right;">ES</th>
                        <th style="padding: 12px; font-weight: 600; color: var(--text-secondary); border-right: 1px solid var(--border); width: 80px; text-align: right;">Stock</th>
                        <th style="padding: 12px; font-weight: 600; color: var(--text-secondary); width: 80px; text-align: right;">Ecart</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $sumAchat = 0;
                        $sumTransfert = 0;
                        $sumVente = 0;
                        $sumEs = 0;
                        $sumStock = 0;
                        $sumEcart = 0;
                    @endphp
                    @forelse($articles as $article)
                    @php
                        $sumAchat += $article->total_achat;
                        $sumTransfert += $article->total_transfert;
                        $sumVente += $article->total_vente;
                        $sumEs += $article->total_es;
                        $sumStock += $article->total_stock;
                        $sumEcart += $article->total_ecart;
                    @endphp
                    <tr style="border-bottom: 1px solid var(--border); transition: background 0.2s;" class="data-row" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 8px 12px; color: var(--text); border-right: 1px solid var(--border);">{{ $article->reference }}</td>
                        <td style="padding: 8px 12px; color: var(--text); border-right: 1px solid var(--border);">{{ $article->produitcode }}</td>
                        <td style="padding: 8px 12px; color: var(--text); border-right: 1px solid var(--border);">{{ $article->produitlibelle }}</td>
                        <td style="padding: 8px 12px; color: var(--text); border-right: 1px solid var(--border); text-align: right;">{{ number_format($article->total_achat, 0, ',', ' ') }}</td>
                        <td style="padding: 8px 12px; color: var(--text); border-right: 1px solid var(--border); text-align: right;">{{ number_format($article->total_transfert, 0, ',', ' ') }}</td>
                        <td style="padding: 8px 12px; color: var(--text); border-right: 1px solid var(--border); text-align: right;">{{ number_format($article->total_vente, 0, ',', ' ') }}</td>
                        <td style="padding: 8px 12px; color: var(--text); border-right: 1px solid var(--border); text-align: right;">{{ number_format($article->total_es, 0, ',', ' ') }}</td>
                        <td style="padding: 8px 12px; color: var(--text); border-right: 1px solid var(--border); text-align: right; font-weight: 600;">{{ number_format($article->total_stock, 0, ',', ' ') }}</td>
                        <td style="padding: 8px 12px; color: var(--text); text-align: right;">{{ number_format($article->total_ecart, 0, ',', ' ') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" style="padding: 50px; text-align: center; color: var(--text-muted); font-size: 16px; font-weight: 600;">
                            No data to display
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr style="background: #f1f5f9; font-weight: 700; border-top: 2px solid var(--border);">
                        <td colspan="3" style="padding: 10px 12px; border-right: 1px solid var(--border);">Nombre {{ $articles->total() }}</td>
                        <td style="padding: 10px 12px; text-align: right; border-right: 1px solid var(--border);">{{ number_format($sumAchat, 0, ',', ' ') }}</td>
                        <td style="padding: 10px 12px; text-align: right; border-right: 1px solid var(--border);">{{ number_format($sumTransfert, 0, ',', ' ') }}</td>
                        <td style="padding: 10px 12px; text-align: right; border-right: 1px solid var(--border);">{{ number_format($sumVente, 0, ',', ' ') }}</td>
                        <td style="padding: 10px 12px; text-align: right; border-right: 1px solid var(--border);">{{ number_format($sumEs, 0, ',', ' ') }}</td>
                        <td style="padding: 10px 12px; text-align: right; border-right: 1px solid var(--border);">{{ number_format($sumStock, 0, ',', ' ') }}</td>
                        <td style="padding: 10px 12px; text-align: right;">{{ number_format($sumEcart, 0, ',', ' ') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <div style="padding: 10px 15px; border-top: 1px solid var(--border); background: white; display: flex; justify-content: flex-end; align-items: center;">
            <div>
                {{ $articles->appends(request()->query())->links('pagination::bootstrap-4') }}
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
