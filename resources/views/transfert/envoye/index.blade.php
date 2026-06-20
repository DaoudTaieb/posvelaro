@extends('layouts.app')

@section('title', 'Bons de Transferts Envoyés')

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
    
    .btn-icon {
        background: white;
        border: 1px solid var(--border);
        padding: 4px 8px;
        border-radius: 4px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--text);
    }
</style>
@endsection

@section('content')
<div class="main-content-inner full-width" style="padding: 0;">
    
    <div style="padding: 12px 20px; background: white; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
        <h1 style="font-size: 16px; font-weight: 700; color: var(--text); margin: 0;">Bons de Transferts Envoyés {{ $siteLibelle }}</h1>
        <div style="display: flex; gap: 5px;">
            <a href="{{ route('transfert.envoye.create') }}" class="btn-icon" title="Nouveau" style="text-decoration: none;">+</a>
            <button class="btn-icon" title="Imprimer">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            </button>
            <button class="btn-icon" title="Imprimer Tout">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            </button>
            <a href="{{ route('transfert.envoye.impression_multiple') }}" class="btn-action" style="font-size: 13px; text-decoration: none;">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 5px;"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                Impression Multiple
            </a>
            <button class="btn-icon" title="Fermer">x</button>
        </div>
    </div>

    <!-- Barre de filtres principale -->
    <div style="background: #f8fafc; padding: 10px 20px; font-weight: 600; font-size: 13px; color: var(--text); border-bottom: 1px solid var(--border);">
        Filtre
    </div>
    <form method="GET" action="{{ route('transfert.envoye.index') }}" id="filterForm">
        <div class="filter-bar">
            <div class="filter-group">
                <label>Periode</label>
                <input type="date" name="datedebut" class="date-input" value="{{ $defaultDateDebut }}">
                <input type="date" name="datefin" class="date-input" value="{{ $defaultDateFin }}">
            </div>
            
            <div class="filter-group" style="margin-left: 10px;">
                <label>Récepteur</label>
                <select name="siterecepteurid" class="select-input">
                    <option value=""></option>
                    @foreach($sites as $s)
                        <option value="{{ $s->siteid }}" {{ request('siterecepteurid') == $s->siteid ? 'selected' : '' }}>
                            {{ $s->libelle }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group" style="margin-left: 10px;">
                <label>Etat</label>
                <select name="etatid" class="select-input">
                    <option value="tous">Tous</option>
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
        </div>
    </form>

    <div class="table-container">
        <div style="padding: 5px 10px; display: flex; justify-content: flex-end;">
            <div style="position: relative; width: 250px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 10px; top: 8px;">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" id="globalSearch" class="search-input" style="padding-left: 30px; margin-top: 0; padding-top: 6px; padding-bottom: 6px;" placeholder="Enter text to search...">
            </div>
        </div>
        
        <table id="dataTable">
            <thead>
                <tr>
                    <th style="width: 5%;"><div style="text-align: center; margin-top:14px;"><a href="#" style="color: #cbd5e1; text-decoration: none;">Clear</a></div></th>
                    <th style="width: 10%;">Émetteur <input type="text" class="search-input"></th>
                    <th style="width: 10%;">Récepteur <input type="text" class="search-input"></th>
                    <th style="width: 10%;">Numéro <input type="text" class="search-input"></th>
                    <th style="width: 10%;">Date <input type="date" class="search-input"></th>
                    <th style="width: 8%;">QTE <input type="text" class="search-input"></th>
                    <th style="width: 10%;">Etat <input type="text" class="search-input"></th>
                    <th style="width: 10%;">Trajet <input type="text" class="search-input"></th>
                    <th style="width: 10%;">Vehicule <input type="text" class="search-input"></th>
                    <th style="width: 10%;">Matricule <input type="text" class="search-input"></th>
                    <th style="width: 15%;">Description <input type="text" class="search-input"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($bontransferts as $bon)
                <tr class="data-row" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                    <td style="text-align: center;"><input type="checkbox"></td>
                    <td>{{ $bon->emetteur }}</td>
                    <td>{{ $bon->recepteur }}</td>
                    <td>{{ $bon->numero }}</td>
                    <td>{{ \Carbon\Carbon::parse($bon->date)->format('d/m/Y') }}</td>
                    <td style="text-align: right;">{{ $bon->qte }}</td>
                    <td>{{ $bon->etatbontransfertid }}</td>
                    <td>{{ $bon->trajet }}</td>
                    <td></td>
                    <td></td>
                    <td>{{ $bon->description }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" style="padding: 40px; text-align: center; color: var(--text-muted); font-size: 14px; font-weight: 600;">
                        No data to display
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        <div style="padding: 10px 15px; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: white;">
            <div style="background: #7e22ce; color: white; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; border-radius: 4px; font-weight: bold; font-size: 12px;">
                1
            </div>
            <div style="display: flex; align-items: center; gap: 10px; color: var(--text-secondary); font-size: 12px;">
                <span>Page Size:</span>
                <select class="select-input" style="padding: 4px; min-width: 60px;">
                    <option>20</option>
                    <option>50</option>
                    <option>100</option>
                </select>
            </div>
        </div>
    </div>
</div>
@endsection
