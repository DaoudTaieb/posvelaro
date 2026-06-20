@extends('layouts.app')

@section('title', 'Consultation Stock')

@section('content')
<style>
    .pagination { display: flex; list-style: none; padding: 0; margin: 0; gap: 4px; align-items: center; }
    .pagination li a, .pagination li span { display: block; padding: 6px 12px; border: 1px solid var(--border); border-radius: 4px; color: var(--text); text-decoration: none; background: white; font-size: 12px; }
    .pagination li.active span { background: #0284c7; color: white; border-color: #0284c7; }
    .pagination li.disabled span { opacity: 0.5; background: #f1f5f9; cursor: not-allowed; }
    .pagination li a:hover { background: #f8fafc; }
    
    .filter-bar {
        display: flex;
        align-items: center;
        gap: 15px;
        background: white;
        padding: 10px 15px;
        border: 1px solid var(--border);
        border-radius: 8px;
        box-shadow: var(--shadow-sm);
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    
    .filter-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    
    .filter-item label {
        font-size: 11px;
        font-weight: 600;
        color: var(--text-secondary);
    }
    
    .filter-input {
        padding: 6px 10px;
        border: 1px solid var(--border);
        border-radius: 4px;
        font-size: 13px;
        outline: none;
        width: 150px;
    }
    
    .filter-input:focus {
        border-color: var(--primary);
    }
    
    .btn-submit {
        background: white;
        border: 1px solid var(--border);
        padding: 6px 12px;
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 32px;
        margin-top: 18px;
    }
    
    .btn-submit:hover {
        background: #f8fafc;
    }
</style>

<div class="main-content-inner full-width" style="padding: 0 20px;">
    
    <form method="GET" action="{{ route('stock.consultation.index') }}" class="filter-bar">
        <div style="font-size: 16px; font-weight: 700; color: var(--text); margin-right: 10px;">
            Consultation Stock
        </div>
        
        <div class="filter-item">
            <label>Référence</label>
            <input type="text" name="reference" class="filter-input" value="{{ request('reference') }}">
        </div>

        <div class="filter-item">
            <label>Rayon</label>
            <select name="rayonid" class="filter-input" style="width: 180px;">
                <option value="">Tous les rayons...</option>
                @foreach($rayons as $rayon)
                    <option value="{{ $rayon->categoryid }}" {{ request('rayonid') == $rayon->categoryid ? 'selected' : '' }}>
                        {{ $rayon->categorylibelle }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="filter-item">
            <label>Couleur</label>
            <input type="text" name="couleur" class="filter-input" value="{{ request('couleur') }}">
        </div>

        <div class="filter-item">
            <label>Taille</label>
            <input type="text" name="taille" class="filter-input" value="{{ request('taille') }}" style="width: 100px;">
        </div>

        <button type="submit" class="btn-submit">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
        </button>

        <div class="filter-item" style="margin-left: auto;">
            <label>Type Stock</label>
            <select name="type_stock" class="filter-input" style="width: 150px; background-color: #f8fafc;" onchange="this.form.submit()">
                @foreach($typesStock as $key => $label)
                    <option value="{{ $key }}" {{ $selectedType == $key ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>
    </form>

    <div style="background: white; border: 1px solid var(--border); border-radius: 12px; box-shadow: var(--shadow-sm); overflow: hidden;">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 2px solid var(--border);">
                        <th style="padding: 12px; font-weight: 600; color: var(--text-secondary); border-right: 1px solid var(--border);">Code</th>
                        <th style="padding: 12px; font-weight: 600; color: var(--text-secondary); border-right: 1px solid var(--border);">Référence</th>
                        <th style="padding: 12px; font-weight: 600; color: var(--text-secondary); border-right: 1px solid var(--border);">Désignation</th>
                        <th style="padding: 12px; font-weight: 600; color: var(--text-secondary); border-right: 1px solid var(--border);">Rayon</th>
                        <th style="padding: 12px; font-weight: 600; color: var(--text-secondary); border-right: 1px solid var(--border);">Couleur</th>
                        <th style="padding: 12px; font-weight: 600; color: var(--text-secondary); border-right: 1px solid var(--border);">Taille</th>
                        <th style="padding: 12px; font-weight: 700; color: var(--text); background: #f1f5f9;">Quantité ({{ $typesStock[$selectedType] }})</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stocks as $stock)
                    <tr style="border-bottom: 1px solid var(--border); transition: background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 10px 12px; color: var(--text); border-right: 1px solid var(--border);">{{ $stock->produitcode }}</td>
                        <td style="padding: 10px 12px; color: var(--text); border-right: 1px solid var(--border);">{{ $stock->reference }}</td>
                        <td style="padding: 10px 12px; color: var(--text); border-right: 1px solid var(--border);">{{ $stock->produitlibelle }}</td>
                        <td style="padding: 10px 12px; color: var(--text); border-right: 1px solid var(--border);">{{ $stock->rayon_nom }}</td>
                        <td style="padding: 10px 12px; color: var(--text); border-right: 1px solid var(--border);">{{ $stock->couleurlibelle }}</td>
                        <td style="padding: 10px 12px; color: var(--text); border-right: 1px solid var(--border);">{{ $stock->taillelibelle }}</td>
                        
                        @php
                            $qty = 0;
                            if($selectedType == 'reel') $qty = $stock->qtestock;
                            elseif($selectedType == 'virtuel') $qty = $stock->stockvirtuel;
                            elseif($selectedType == 'reserve') $qty = $stock->stockreserve;
                        @endphp
                        <td style="padding: 10px 12px; font-weight: 600; color: {{ $qty < 0 ? 'var(--danger)' : ($qty > 0 ? 'var(--success)' : 'var(--text)') }}; background: #fafafa;">
                            {{ number_format($qty, 0, ',', ' ') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="padding: 30px; text-align: center; color: var(--text-muted);">
                            Aucun stock trouvé pour ces critères.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div style="padding: 15px; border-top: 1px solid var(--border); background: white; display: flex; justify-content: space-between; align-items: center;">
            <div style="font-size: 13px; color: var(--text-secondary);">
                Affichage de {{ $stocks->firstItem() ?? 0 }} à {{ $stocks->lastItem() ?? 0 }} sur {{ $stocks->total() }} résultats
            </div>
            <div>
                {{ $stocks->appends(request()->query())->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
@endsection
