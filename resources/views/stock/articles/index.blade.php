@extends('layouts.app')
@section('title', 'Consultation des Articles')

@section('content')
<style>
    .pagination { display: flex; list-style: none; padding: 0; margin: 0; gap: 4px; align-items: center; }
    .pagination li a, .pagination li span { display: block; padding: 6px 12px; border: 1px solid var(--border); border-radius: 4px; color: var(--text); text-decoration: none; background: white; font-size: 12px; }
    .pagination li.active span { background: #0284c7; color: white; border-color: #0284c7; }
    .pagination li.disabled span { opacity: 0.5; background: #f1f5f9; cursor: not-allowed; }
    .pagination li a:hover { background: #f8fafc; }
</style>
<div class="main-content-inner" style="max-width: 100%; margin: 20px auto; padding: 0 20px;">
    
    <div style="background: white; border: 1px solid var(--border); border-radius: 12px; box-shadow: var(--shadow-sm); overflow: hidden;">
        
        {{-- En-tête et Filtres --}}
        <div style="padding: 16px 24px; border-bottom: 1px solid var(--border); background: #f8fafc;">
            <div style="font-size: 14px; font-weight: 600; color: var(--text); margin-bottom: 16px;">Filtre</div>
            
            <form method="GET" action="{{ route('stock.articles.index') }}" style="display: flex; gap: 24px; align-items: flex-end; flex-wrap: wrap;">
                
                <div style="display: flex; flex-direction: column; gap: 8px; flex: 1; min-width: 200px;">
                    <label style="font-size: 12px; font-weight: 600; color: var(--text-secondary);">Sous Famille</label>
                    <select name="sousfamilleid" style="border: 1px solid var(--border); border-radius: 6px; padding: 8px 12px; outline: none; font-family: inherit; font-size: 14px; color: var(--text); background: white;">
                        <option value="">Select Sous Famille...</option>
                        @foreach($sousFamilles as $sf)
                            <option value="{{ $sf->sousfamilleid }}" {{ request('sousfamilleid') == $sf->sousfamilleid ? 'selected' : '' }}>{{ $sf->sousfamillelibelle }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="display: flex; flex-direction: column; gap: 8px; flex: 1; min-width: 200px;">
                    <label style="font-size: 12px; font-weight: 600; color: var(--text-secondary);">Famille</label>
                    <select name="familleid" style="border: 1px solid var(--border); border-radius: 6px; padding: 8px 12px; outline: none; font-family: inherit; font-size: 14px; color: var(--text); background: white;">
                        <option value="">Select Famille...</option>
                        @foreach($familles as $f)
                            <option value="{{ $f->familleid }}" {{ request('familleid') == $f->familleid ? 'selected' : '' }}>{{ $f->famillelibelle }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="display: flex; flex-direction: column; gap: 8px; flex: 1; min-width: 200px;">
                    <label style="font-size: 12px; font-weight: 600; color: var(--text-secondary);">Saison</label>
                    <select name="saisonid" style="border: 1px solid var(--border); border-radius: 6px; padding: 8px 12px; outline: none; font-family: inherit; font-size: 14px; color: var(--text); background: white;">
                        <option value="">Select Saison...</option>
                        @foreach($saisons as $s)
                            <option value="{{ $s->category4id }}" {{ request('saisonid') == $s->category4id ? 'selected' : '' }}>{{ $s->category4libelle }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="display: flex; flex-direction: column; gap: 8px; flex: 1; min-width: 200px;">
                    <label style="font-size: 12px; font-weight: 600; color: var(--text-secondary);">Rayon</label>
                    <select name="rayonid" style="border: 1px solid var(--border); border-radius: 6px; padding: 8px 12px; outline: none; font-family: inherit; font-size: 14px; color: var(--text); background: white;">
                        <option value="">Select Rayon...</option>
                        @foreach($rayons as $r)
                            <option value="{{ $r->categoryid }}" {{ request('rayonid') == $r->categoryid ? 'selected' : '' }}>{{ $r->categorylibelle }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" style="background: white; border: 1px solid #000; border-radius: 6px; padding: 8px 24px; cursor: pointer; display: flex; align-items: center; justify-content: center; height: 38px; box-shadow: 2px 2px 0px rgba(0,0,0,1);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                    </svg>
                </button>
                <a href="{{ route('stock.articles.index') }}" style="background: white; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 12px; text-decoration: none; color: var(--text); height: 38px; display: flex; align-items: center; box-sizing: border-box;" title="Réinitialiser les filtres">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                </a>
            </form>
        </div>

        {{-- Tableau --}}
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 12px; white-space: nowrap;" id="articlesTable">
                <thead>
                    <tr style="background: #f1f5f9; border-bottom: 1px solid var(--border);">
                        <th style="text-align: left; padding: 8px 12px; font-weight: 600; color: var(--text); border-right: 1px solid var(--border);">Code</th>
                        <th style="text-align: left; padding: 8px 12px; font-weight: 600; color: var(--text); border-right: 1px solid var(--border);">Référence</th>
                        <th style="text-align: left; padding: 8px 12px; font-weight: 600; color: var(--text); border-right: 1px solid var(--border);">Code à Barre</th>
                        <th style="text-align: left; padding: 8px 12px; font-weight: 600; color: var(--text); border-right: 1px solid var(--border);">Désignation</th>
                        <th style="text-align: left; padding: 8px 12px; font-weight: 600; color: var(--text); border-right: 1px solid var(--border);">Famille</th>
                        <th style="text-align: left; padding: 8px 12px; font-weight: 600; color: var(--text); border-right: 1px solid var(--border);">Sous Famille</th>
                        <th style="text-align: right; padding: 8px 12px; font-weight: 600; color: var(--text); border-right: 1px solid var(--border);">Vente TTC</th>
                        <th style="text-align: center; padding: 8px 12px; font-weight: 600; color: var(--text); border-right: 1px solid var(--border);">Fidélité</th>
                        <th style="text-align: left; padding: 8px 12px; font-weight: 600; color: var(--text); border-right: 1px solid var(--border);">Saison</th>
                        <th style="text-align: left; padding: 8px 12px; font-weight: 600; color: var(--text); border-right: 1px solid var(--border);">Marque</th>
                    </tr>
                    <tr style="background: #f8fafc; border-bottom: 2px solid var(--border);" class="column-filters">
                        <td style="padding: 4px; border-right: 1px solid var(--border);"><input type="text" data-col="0" style="width: 100%; border: 1px solid var(--border); border-radius: 4px; padding: 4px; box-sizing: border-box;"></td>
                        <td style="padding: 4px; border-right: 1px solid var(--border);"><input type="text" data-col="1" style="width: 100%; border: 1px solid var(--border); border-radius: 4px; padding: 4px; box-sizing: border-box;"></td>
                        <td style="padding: 4px; border-right: 1px solid var(--border);"><input type="text" data-col="2" style="width: 100%; border: 1px solid var(--border); border-radius: 4px; padding: 4px; box-sizing: border-box;"></td>
                        <td style="padding: 4px; border-right: 1px solid var(--border);"><input type="text" data-col="3" style="width: 100%; border: 1px solid var(--border); border-radius: 4px; padding: 4px; box-sizing: border-box;"></td>
                        <td style="padding: 4px; border-right: 1px solid var(--border);"><input type="text" data-col="4" style="width: 100%; border: 1px solid var(--border); border-radius: 4px; padding: 4px; box-sizing: border-box;"></td>
                        <td style="padding: 4px; border-right: 1px solid var(--border);"><input type="text" data-col="5" style="width: 100%; border: 1px solid var(--border); border-radius: 4px; padding: 4px; box-sizing: border-box;"></td>
                        <td style="padding: 4px; border-right: 1px solid var(--border);"><input type="text" data-col="6" style="width: 100%; border: 1px solid var(--border); border-radius: 4px; padding: 4px; box-sizing: border-box;"></td>
                        <td style="padding: 4px; border-right: 1px solid var(--border);"><input type="text" data-col="7" style="width: 100%; border: 1px solid var(--border); border-radius: 4px; padding: 4px; box-sizing: border-box;"></td>
                        <td style="padding: 4px; border-right: 1px solid var(--border);"><input type="text" data-col="8" style="width: 100%; border: 1px solid var(--border); border-radius: 4px; padding: 4px; box-sizing: border-box;"></td>
                        <td style="padding: 4px; border-right: 1px solid var(--border);"><input type="text" data-col="9" style="width: 100%; border: 1px solid var(--border); border-radius: 4px; padding: 4px; box-sizing: border-box;"></td>
                    </tr>
                </thead>
                <tbody>
                    @forelse($articles as $article)
                    <tr style="border-bottom: 1px solid var(--border); transition: background 0.2s;" class="data-row" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 10px 12px; color: var(--text); border-right: 1px solid var(--border);">{{ $article->produitcode }}</td>
                        <td style="padding: 10px 12px; color: var(--text); border-right: 1px solid var(--border);">{{ $article->reference }}</td>
                        <td style="padding: 10px 12px; color: var(--text); border-right: 1px solid var(--border);">{{ $article->variant_barcode ?: $article->produit2id }}</td>
                        <td style="padding: 10px 12px; color: var(--text); border-right: 1px solid var(--border);">{{ $article->produitlibelle }}</td>
                        <td style="padding: 10px 12px; color: var(--text); border-right: 1px solid var(--border);">{{ $article->famillelibelle }}</td>
                        <td style="padding: 10px 12px; color: var(--text); border-right: 1px solid var(--border);">{{ $article->sousfamillelibelle }}</td>
                        <td style="padding: 10px 12px; text-align: right; color: var(--text); border-right: 1px solid var(--border); font-family: 'JetBrains Mono', monospace;">{{ number_format((float)($article->ttc_vente ?? 0), 3, '.', ' ') }}</td>
                        <td style="padding: 10px 12px; text-align: center; color: var(--text); border-right: 1px solid var(--border);">
                            @if($article->isfidelite)
                                <span style="background: #dcfce7; color: #166534; padding: 2px 6px; border-radius: 4px; font-size: 11px; font-weight: bold;">OUI</span>
                            @else
                                <span style="background: #f1f5f9; color: #64748b; padding: 2px 6px; border-radius: 4px; font-size: 11px;">NON</span>
                            @endif
                        </td>
                        <td style="padding: 10px 12px; color: var(--text); border-right: 1px solid var(--border);">{{ $article->saison_nom }}</td>
                        <td style="padding: 10px 12px; color: var(--text); border-right: 1px solid var(--border);">{{ $article->marque_nom }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" style="padding: 24px; text-align: center; color: var(--text-secondary); font-weight: 500;" id="emptyState">
                            No data to display
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination (style simple) --}}
        @if($articles->hasPages())
        <div style="padding: 12px 24px; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
            <div>
                {{ $articles->links('pagination::bootstrap-4') }}
            </div>
            <div style="font-size: 12px; color: var(--text-secondary);">
                Page Size: 20
            </div>
        </div>
        @endif

    </div>
</div>

@section('scripts')
<script>
    // JS pour le filtrage instantané sur les colonnes
    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('.column-filters input');
        
        inputs.forEach(input => {
            input.addEventListener('keyup', function() {
                filterTable();
            });
        });

        function filterTable() {
            const rows = document.querySelectorAll('#articlesTable tbody tr.data-row');
            const emptyState = document.getElementById('emptyState');
            let visibleCount = 0;

            rows.forEach(row => {
                let showRow = true;
                const cells = row.querySelectorAll('td');

                inputs.forEach(input => {
                    const filterValue = input.value.toLowerCase().trim();
                    if (filterValue !== '') {
                        const colIndex = parseInt(input.getAttribute('data-col'));
                        const cellText = cells[colIndex].textContent.toLowerCase();
                        if (!cellText.includes(filterValue)) {
                            showRow = false;
                        }
                    }
                });

                if (showRow) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Afficher le message 'No data to display' si toutes les lignes sont cachées
            // et que ce n'est pas déjà un tableau vide nativement
            if (emptyState && visibleCount === 0 && rows.length > 0) {
                // Créer un row no data s'il n'existe pas
                let hiddenNoData = document.getElementById('hiddenNoData');
                if (!hiddenNoData) {
                    const tbody = document.querySelector('#articlesTable tbody');
                    hiddenNoData = document.createElement('tr');
                    hiddenNoData.id = 'hiddenNoData';
                    hiddenNoData.innerHTML = '<td colspan="10" style="padding: 24px; text-align: center; color: var(--text-secondary); font-weight: 500;">No matching data</td>';
                    tbody.appendChild(hiddenNoData);
                }
                hiddenNoData.style.display = '';
            } else if (document.getElementById('hiddenNoData')) {
                document.getElementById('hiddenNoData').style.display = 'none';
            }
        }
    });
</script>
@endsection
@endsection
