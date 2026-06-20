@extends('layouts.app')

@section('title', 'Mouvements Des Produits')

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

    .date-label {
        font-size: 13px;
        font-weight: 600;
        color: var(--text-secondary);
    }

    .date-input, .text-input {
        padding: 6px 10px;
        border: 1px solid var(--border);
        border-radius: 4px;
        font-size: 13px;
        outline: none;
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
    }
</style>
@endsection

@section('content')
<div class="main-content-inner full-width" style="padding: 0 20px;">
    
    <form method="GET" action="{{ route('stock.mouvements.index') }}" id="filterForm">
        <!-- Barre du haut -->
        <div class="filter-bar">
            <div style="font-size: 16px; font-weight: 700; color: var(--text); display: flex; align-items: center;">
                Mouvements Des Produits
            </div>
            
            <div style="display: flex; align-items: center; gap: 8px; margin-left: 20px;">
                <span class="date-label">DU</span>
                <input type="date" name="date_du" class="date-input" value="{{ $dateDu }}">
                
                <span class="date-label" style="margin-left: 10px;">AU</span>
                <input type="date" name="date_au" class="date-input" value="{{ $dateAu }}">
            </div>

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
                <a href="{{ route('stock.mouvements.index') }}" class="btn-action">
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
                        <th style="padding: 8px 10px; font-weight: 600; color: var(--text-secondary); border-right: 1px solid var(--border);">
                            Code <input type="text" class="search-input" onkeyup="filterTable(0, this.value)">
                        </th>
                        <th style="padding: 8px 10px; font-weight: 600; color: var(--text-secondary); border-right: 1px solid var(--border);">
                            Couleur <input type="text" class="search-input" onkeyup="filterTable(1, this.value)">
                        </th>
                        <th style="padding: 8px 10px; font-weight: 600; color: var(--text-secondary); border-right: 1px solid var(--border);">
                            Taille <input type="text" class="search-input" onkeyup="filterTable(2, this.value)">
                        </th>
                        <th style="padding: 8px 10px; font-weight: 600; color: var(--text-secondary); border-right: 1px solid var(--border); text-align: right;">Achat</th>
                        <th style="padding: 8px 10px; font-weight: 600; color: var(--text-secondary); border-right: 1px solid var(--border); text-align: right;">Entrer</th>
                        <th style="padding: 8px 10px; font-weight: 600; color: var(--text-secondary); border-right: 1px solid var(--border); text-align: right;">Sortie</th>
                        <th style="padding: 8px 10px; font-weight: 600; color: var(--text-secondary); border-right: 1px solid var(--border); text-align: right;">Vente</th>
                        <th style="padding: 8px 10px; font-weight: 600; color: var(--text-secondary); border-right: 1px solid var(--border);">
                            N° Piece <input type="text" class="search-input" onkeyup="filterTable(7, this.value)">
                        </th>
                        <th style="padding: 8px 10px; font-weight: 600; color: var(--text-secondary); border-right: 1px solid var(--border);">
                            Date <input type="text" class="search-input" onkeyup="filterTable(8, this.value)">
                        </th>
                        <th style="padding: 8px 10px; font-weight: 600; color: var(--text-secondary); border-right: 1px solid var(--border);">
                            Heure <input type="text" class="search-input" onkeyup="filterTable(9, this.value)">
                        </th>
                        <th style="padding: 8px 10px; font-weight: 600; color: var(--text-secondary); border-right: 1px solid var(--border);">
                            Intitule <input type="text" class="search-input" onkeyup="filterTable(10, this.value)">
                        </th>
                        <th style="padding: 8px 10px; font-weight: 600; color: var(--text-secondary);">
                            Site <input type="text" class="search-input" onkeyup="filterTable(11, this.value)">
                        </th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @php
                        $sumAchat = 0;
                        $sumEntrer = 0;
                        $sumSortie = 0;
                        $sumVente = 0;
                        
                        $groupedItems = collect($mouvements->items())->groupBy('reference');
                    @endphp
                    @forelse($groupedItems as $reference => $items)
                        @php
                            $grpCount = count($items);
                            $grpAchat = 0;
                            $grpEntrer = 0;
                            $grpSortie = 0;
                            $grpVente = 0;
                            
                            foreach($items as $m) {
                                $a = $m->qteachat ?? 0;
                                $v = $m->qtevente ?? 0;
                                $adj = ($m->qtetransfert ?? 0) + ($m->qteinout ?? 0) + ($m->qteecart ?? 0);
                                $e = $adj > 0 ? $adj : 0;
                                $s = $adj < 0 ? abs($adj) : 0;
                                
                                $grpAchat += $a;
                                $grpEntrer += $e;
                                $grpSortie += $s;
                                $grpVente += $v;
                            }
                            
                            $sumAchat += $grpAchat;
                            $sumEntrer += $grpEntrer;
                            $sumSortie += $grpSortie;
                            $sumVente += $grpVente;
                        @endphp
                        
                        <!-- Ligne de groupe -->
                        <tr class="group-header" onclick="toggleGroup('group-{{ Str::slug($reference ?? 'sans-ref') }}-{{ $loop->index }}')" style="background: #f8fafc; cursor: pointer; border-bottom: 1px solid var(--border);">
                            <td colspan="12" style="padding: 6px 10px; font-weight: 700; color: var(--text);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 5px;">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                                Référence: {{ $reference }} (Nombre {{ $grpCount }}, Achat {{ $grpAchat != 0 ? number_format($grpAchat, 0, '', '') : '0' }}, Entrer {{ $grpEntrer != 0 ? number_format($grpEntrer, 0, '', '') : '0' }}, Sortie {{ $grpSortie != 0 ? number_format($grpSortie, 0, '', '') : '0' }}, Vente {{ $grpVente != 0 ? number_format($grpVente, 0, '', '') : '0' }})
                            </td>
                        </tr>
                        
                        <!-- Lignes de détail du groupe -->
                        @foreach($items as $mvt)
                            @php
                                $achat = $mvt->qteachat ?? 0;
                                $vente = $mvt->qtevente ?? 0;
                                $adj = ($mvt->qtetransfert ?? 0) + ($mvt->qteinout ?? 0) + ($mvt->qteecart ?? 0);
                                $entrer = $adj > 0 ? $adj : 0;
                                $sortie = $adj < 0 ? abs($adj) : 0;
                                $dt = \Carbon\Carbon::parse($mvt->dateoperation);
                            @endphp
                            <tr class="data-row group-{{ Str::slug($reference ?? 'sans-ref') }}-{{ $loop->parent->index }}" style="border-bottom: 1px solid var(--border); transition: background 0.2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                                <td style="padding: 6px 10px; color: var(--text); border-right: 1px solid var(--border);">{{ $mvt->produitcode }}</td>
                                <td style="padding: 6px 10px; color: var(--text); border-right: 1px solid var(--border);">{{ $mvt->couleurlibelle }}</td>
                                <td style="padding: 6px 10px; color: var(--text); border-right: 1px solid var(--border);">{{ $mvt->taillelibelle }}</td>
                                <td style="padding: 6px 10px; color: var(--text); border-right: 1px solid var(--border); text-align: right;">{{ $achat != 0 ? number_format($achat, 0, '', '') : '' }}</td>
                                <td style="padding: 6px 10px; color: var(--text); border-right: 1px solid var(--border); text-align: right;">{{ $entrer != 0 ? number_format($entrer, 0, '', '') : '' }}</td>
                                <td style="padding: 6px 10px; color: var(--text); border-right: 1px solid var(--border); text-align: right;">{{ $sortie != 0 ? number_format($sortie, 0, '', '') : '' }}</td>
                                <td style="padding: 6px 10px; color: var(--text); border-right: 1px solid var(--border); text-align: right;">{{ $vente != 0 ? number_format($vente, 0, '', '') : '' }}</td>
                                <td style="padding: 6px 10px; color: var(--text); border-right: 1px solid var(--border);">{{ $mvt->docid }}</td>
                                <td style="padding: 6px 10px; color: var(--text); border-right: 1px solid var(--border);">{{ $dt->format('d/m/Y') }}</td>
                                <td style="padding: 6px 10px; color: var(--text); border-right: 1px solid var(--border);">{{ $dt->format('H:i') }}</td>
                                <td style="padding: 6px 10px; color: var(--text); border-right: 1px solid var(--border);">{{ $mvt->doclibelle }}</td>
                                <td style="padding: 6px 10px; color: var(--text);">{{ $mvt->sitelibelle }}</td>
                            </tr>
                        @endforeach
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
                        <td colspan="3" style="padding: 10px 12px; border-right: 1px solid var(--border);">Nombre {{ $mouvements->total() }}</td>
                        <td style="padding: 10px 12px; text-align: right; border-right: 1px solid var(--border);">{{ $sumAchat != 0 ? number_format($sumAchat, 0, '', '') : '0' }}</td>
                        <td style="padding: 10px 12px; text-align: right; border-right: 1px solid var(--border);">{{ $sumEntrer != 0 ? number_format($sumEntrer, 0, '', '') : '0' }}</td>
                        <td style="padding: 10px 12px; text-align: right; border-right: 1px solid var(--border);">{{ $sumSortie != 0 ? number_format($sumSortie, 0, '', '') : '0' }}</td>
                        <td style="padding: 10px 12px; text-align: right; border-right: 1px solid var(--border);">{{ $sumVente != 0 ? number_format($sumVente, 0, '', '') : '0' }}</td>
                        <td colspan="5"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <div style="padding: 10px 15px; border-top: 1px solid var(--border); background: white; display: flex; justify-content: flex-end; align-items: center;">
            <div>
                {{ $mouvements->appends(request()->query())->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>

<script>
function toggleGroup(className) {
    const rows = document.getElementsByClassName(className);
    for(let i=0; i<rows.length; i++) {
        if (rows[i].style.display === 'none') {
            rows[i].style.display = '';
        } else {
            rows[i].style.display = 'none';
        }
    }
}

function filterTable(colIndex, value) {
    // Note : Le filtre simple est désactivé ou à adapter pour la vue groupée
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
