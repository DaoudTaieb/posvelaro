@extends('layouts.app')

@section('title', 'Impression multiple de Bon de Transferts')

@section('styles')
<style>
    .imp-header {
        padding: 10px 20px;
        background: white;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .imp-filters {
        padding: 10px 20px;
        background: white;
        border-bottom: 1px solid var(--border);
    }
    .filter-row {
        display: flex;
        gap: 15px;
        align-items: center;
        margin-bottom: 8px;
        flex-wrap: wrap;
    }
    .filter-group {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .filter-group label {
        font-size: 12px;
        color: var(--text-secondary);
        white-space: nowrap;
    }
    .filter-input {
        padding: 5px 8px;
        border: 1px solid var(--border);
        border-radius: 4px;
        font-size: 12px;
    }
    .filter-select {
        padding: 5px 8px;
        border: 1px solid var(--border);
        border-radius: 4px;
        font-size: 12px;
        min-width: 150px;
    }
    .btn-filter {
        background: white;
        border: 1px solid #0284c7;
        color: #0284c7;
        padding: 5px 12px;
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .btn-filter:hover {
        background: #f0f9ff;
    }
    .btn-validate {
        background: white;
        border: 1px solid #22c55e;
        color: #22c55e;
        padding: 5px 12px;
        border-radius: 4px;
        cursor: pointer;
    }
    .btn-validate:hover {
        background: #f0fdf4;
    }
    .btn-close-page {
        background: none;
        border: none;
        font-size: 20px;
        cursor: pointer;
        color: #ef4444;
        padding: 2px 8px;
    }
    .imp-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }
    .imp-table th {
        padding: 8px 10px;
        font-weight: 600;
        color: var(--text-secondary);
        background: #f8fafc;
        border: 1px solid var(--border);
        text-align: left;
    }
    .imp-table td {
        padding: 6px 10px;
        border: 1px solid var(--border);
        color: var(--text);
    }
    .imp-table tr:hover {
        background: #f1f5f9;
    }
    .imp-table .col-search {
        width: 100%;
        border: 1px solid var(--border);
        padding: 3px 5px;
        border-radius: 2px;
        font-size: 11px;
        display: block;
        margin-top: 4px;
    }
    .imp-table input[type="checkbox"] {
        cursor: pointer;
    }
    .selected-row {
        background: #eff6ff !important;
    }
</style>
@endsection

@section('content')
<div class="main-content-inner full-width" style="padding: 0;">

    <!-- En-tête -->
    <div class="imp-header">
        <h1 style="font-size: 15px; font-weight: 700; margin: 0;">Impression multiple de Bon de Transferts</h1>
        <div style="display: flex; align-items: center; gap: 10px;">
            <a href="{{ route('transfert.envoye.index') }}" class="btn-close-page" title="Fermer">&times;</a>
        </div>
    </div>

    <!-- Filtres -->
    <form method="GET" action="{{ route('transfert.envoye.impression_multiple') }}">
        <div class="imp-filters">
            <div class="filter-row">
                <div class="filter-group">
                    <label>Date impression</label>
                    <input type="date" class="filter-input" value="{{ now()->format('Y-m-d') }}">
                </div>
                <div class="filter-group">
                    <label>Numéro</label>
                    <input type="text" name="numero" class="filter-input" value="{{ request('numero') }}">
                </div>
                <div class="filter-group">
                    <label>Véhicule</label>
                    <input type="text" class="filter-input">
                </div>
                <div class="filter-group">
                    <label>Chauffeur</label>
                    <input type="text" class="filter-input">
                </div>
                <button type="button" class="btn-filter" title="Imprimer" onclick="printSelected()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                </button>
            </div>
            <div class="filter-row">
                <div class="filter-group">
                    <label>DU</label>
                    <input type="date" name="datedebut" class="filter-input" value="{{ $defaultDateDebut }}">
                </div>
                <div class="filter-group">
                    <label>AU</label>
                    <input type="date" name="datefin" class="filter-input" value="{{ $defaultDateFin }}">
                </div>
                <div class="filter-group">
                    <label>Expéditeur</label>
                    <select class="filter-select" disabled>
                        <option>{{ $siteExpediteur->libelle ?? 'Velaro' }}</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Récepteur</label>
                    <select name="siterecepteurid" class="filter-select">
                        <option value=""></option>
                        @foreach($sites as $s)
                            <option value="{{ $s->siteid }}" {{ request('siterecepteurid') == $s->siteid ? 'selected' : '' }}>{{ $s->libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn-validate" title="Filtrer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </button>
            </div>
        </div>
    </form>

    <!-- Tableau + Recherche -->
    <div style="display: flex; background: white;">
        <!-- Tableau -->
        <div style="flex: 1; overflow-x: auto;">
            <div style="padding: 8px 20px; display: flex; justify-content: flex-end;">
                <div style="position: relative; width: 250px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 8px; top: 9px;">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" id="tableSearch" class="filter-input" style="width: 100%; padding-left: 25px;" placeholder="Enter text to search..." oninput="filterTable()">
                </div>
            </div>
            <table class="imp-table">
                <thead>
                    <tr>
                        <th style="width: 30px;">
                            <input type="checkbox" id="selectAll" onclick="toggleAll()">
                        </th>
                        <th>
                            Numéro
                            <input type="text" class="col-search" oninput="filterTable()">
                        </th>
                        <th>
                            Date
                            <input type="date" class="col-search" oninput="filterTable()">
                        </th>
                        <th>
                            Etat
                            <input type="text" class="col-search" oninput="filterTable()">
                        </th>
                        <th>
                            Expéditeur
                            <input type="text" class="col-search" oninput="filterTable()">
                        </th>
                        <th style="color: #ef4444;">
                            Récepteur
                            <input type="text" class="col-search" oninput="filterTable()">
                        </th>
                        <th>
                            Qte
                            <input type="number" class="col-search" oninput="filterTable()">
                        </th>
                    </tr>
                </thead>
                <tbody id="bonsTableBody">
                    @forelse($bons as $bon)
                        <tr class="bon-row" onclick="toggleRow(this)">
                            <td><input type="checkbox" class="bon-check" value="{{ $bon->bontransfertid }}" onclick="event.stopPropagation()"></td>
                            <td>{{ $bon->numero }}</td>
                            <td>{{ \Carbon\Carbon::parse($bon->date)->format('d/m/Y') }}</td>
                            <td>{{ $bon->etat ?? '' }}</td>
                            <td>{{ $bon->expediteur }}</td>
                            <td>{{ $bon->recepteur }}</td>
                            <td>{{ (int)$bon->qte }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding: 60px; text-align: center; color: var(--text-muted); font-weight: 600; font-size: 14px;">
                                No data to display
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function toggleAll() {
        const checked = document.getElementById('selectAll').checked;
        document.querySelectorAll('.bon-check').forEach(cb => {
            cb.checked = checked;
            cb.closest('tr').classList.toggle('selected-row', checked);
        });
    }

    function toggleRow(tr) {
        const cb = tr.querySelector('.bon-check');
        cb.checked = !cb.checked;
        tr.classList.toggle('selected-row', cb.checked);
    }

    function filterTable() {
        const searchText = document.getElementById('tableSearch').value.toLowerCase();
        document.querySelectorAll('.bon-row').forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchText) ? '' : 'none';
        });
    }

    function printSelected() {
        const selected = [];
        document.querySelectorAll('.bon-check:checked').forEach(cb => {
            selected.push(cb.value);
        });
        
        if (selected.length === 0) {
            alert('Veuillez sélectionner au moins un bon de transfert.');
            return;
        }
        
        alert('Impression de ' + selected.length + ' bon(s) de transfert sélectionné(s).');
        // TODO: Implement actual print logic
    }
</script>
@endsection
