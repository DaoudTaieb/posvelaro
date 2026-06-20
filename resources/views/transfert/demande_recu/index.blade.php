@extends('layouts.app')

@section('title', 'Pointage Demande de Transfert')

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
        background: #f8fafc;
        padding: 10px 15px;
        border-bottom: 1px solid var(--border);
        gap: 15px;
        flex-wrap: wrap;
    }

    .filter-group {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .filter-group label {
        font-size: 12px;
        color: var(--text-secondary);
    }

    .date-input, .select-input {
        padding: 6px 10px;
        border: 1px solid var(--border);
        border-radius: 4px;
        font-size: 13px;
        outline: none;
    }

    .date-input { width: 130px; }
    .select-input { min-width: 150px; }
    
    .btn-action {
        background: white;
        border: 1px solid var(--border);
        padding: 6px 15px;
        border-radius: 4px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 32px;
        color: var(--text);
    }
    
    .btn-action:hover { background: #f1f5f9; }

    .search-input {
        width: 100%;
        padding: 4px 8px;
        border: 1px solid #cbd5e1;
        border-radius: 4px;
        font-size: 11px;
        margin-top: 4px;
        box-sizing: border-box;
    }

    .table-container {
        background: white; 
        overflow-x: auto;
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
        text-align: left;
    }

    td {
        padding: 6px 8px;
        border: 1px solid var(--border);
        color: var(--text);
    }
    
    .input-cell {
        width: 100%;
        padding: 4px;
        border: 1px solid #cbd5e1;
        border-radius: 3px;
        box-sizing: border-box;
        font-size: 11px;
        outline: none;
    }
</style>
@endsection

@section('content')
<div class="main-content-inner full-width" style="padding: 0;">
    
    <div style="padding: 12px 20px; background: #f1f5f9; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
        <h1 style="font-size: 16px; font-weight: 700; color: var(--text); margin: 0;">Pointage Demande de Transfert</h1>
        <button type="submit" form="pointageForm" class="btn-action" title="Enregistrer le pointage" style="border-color: #0284c7; color: #0284c7;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 5px;">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                <polyline points="7 3 7 8 15 8"></polyline>
            </svg>
            Enregistrer
        </button>
    </div>

    <!-- Barre de filtres principale -->
    <form method="GET" action="{{ route('transfert.demande_recu.index') }}" id="filterForm">
        <div class="filter-bar">
            <div class="filter-group">
                <label>Periode</label>
                <input type="date" name="datedebut" class="date-input" value="{{ $defaultDateDebut }}">
                <input type="date" name="datefin" class="date-input" value="{{ $defaultDateFin }}">
            </div>

            <div class="filter-group">
                <label>Etat</label>
                <select name="etatid" class="select-input">
                    <option value="tous"></option>
                    @foreach($etats as $etat)
                        <option value="{{ $etat->etatdemandetransfertid }}" {{ request('etatid') == $etat->etatdemandetransfertid ? 'selected' : '' }}>
                            {{ $etat->libelle }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn-action" style="padding: 6px 20px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                </svg>
            </button>
            
            <div style="margin-left: auto; width: 250px;">
                <div style="position: relative;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 10px; top: 10px;">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" id="globalSearch" class="text-input" style="width: 100%; padding-left: 30px;" placeholder="Enter text to search...">
                </div>
            </div>
        </div>
    </form>

    <form method="POST" action="{{ route('transfert.demande_recu.pointer') }}" id="pointageForm">
        @csrf
        <div class="table-container">
        <table id="dataTable">
            <thead>
                <tr>
                    <th style="width: 8%;">Numéro <input type="text" class="search-input"></th>
                    <th style="width: 8%;">Date <input type="date" class="search-input"></th>
                    <th style="width: 10%;">Demandeur <input type="text" class="search-input"></th>
                    <th style="width: 12%;">Reference <input type="text" class="search-input"></th>
                    <th style="width: 8%;">Couleur <input type="text" class="search-input"></th>
                    <th style="width: 8%;">Taille <input type="text" class="search-input"></th>
                    <th style="width: 8%;">Qte Demandée <input type="text" class="search-input"></th>
                    <th style="width: 8%;">Stk <input type="text" class="search-input"></th>
                    <th style="width: 10%;">Etat <input type="text" class="search-input"></th>
                    <th style="width: 10%;">Cause <input type="text" class="search-input"></th>
                    <th style="width: 10%;">Qte Validée <div style="text-align: center; margin-top:4px;"><a href="#" style="color: #cbd5e1; text-decoration: none;">Clear</a></div></th>
                </tr>
            </thead>
            <tbody>
                @forelse($demandes as $dem)
                <tr class="data-row" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                    <td>{{ $dem->demandetransfertnumero }}</td>
                    <td>{{ \Carbon\Carbon::parse($dem->demandetransfertdate)->format('d/m/Y') }}</td>
                    <td>{{ $dem->demandeur }}</td>
                    <td>{{ $dem->reference }}</td>
                    <td>{{ $dem->couleur }}</td>
                    <td>{{ $dem->taille }}</td>
                    <td style="text-align: right;">{{ $dem->qte_demandee }}</td>
                    <td style="text-align: right;">{{ $dem->stock }}</td>
                    <td>{{ $dem->etat }}</td>
                    <td><input type="text" name="pointage[{{ $dem->detdemandetransfertid }}][cause]" class="input-cell" value="{{ $dem->cause }}"></td>
                    <td><input type="number" name="pointage[{{ $dem->detdemandetransfertid }}][qte_validee]" class="input-cell" value="{{ $dem->qte_validee ?? 0 }}" min="0" max="{{ $dem->stock }}"></td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" style="padding: 50px; text-align: center; color: var(--text-muted); font-size: 14px; font-weight: 600;">
                        No data to display
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </form>
</div>
@endsection
