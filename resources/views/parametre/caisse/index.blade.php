@extends('layouts.app')

@section('title', 'Configuration des caisses')

@section('styles')
<style>
    /* Override layout padding for this view to match screenshot exactly */
    .main-content {
        padding: 0 !important;
    }

    .page-header {
        padding: 5px 10px;
        background: white;
        border-bottom: 1px solid #d1d5db;
        border-top: 1px solid #d1d5db;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .page-title {
        font-size: 13px;
        font-weight: bold;
        color: #111827;
        margin: 0;
    }

    .btn-close {
        font-size: 20px;
        line-height: 1;
        cursor: pointer;
        color: #111827;
        text-decoration: none;
        padding: 0 8px;
        border: 1px solid #d1d5db;
        background: white;
        font-weight: 300;
    }

    .toolbar {
        padding: 5px 10px;
        background: white;
        border-bottom: 1px solid #d1d5db;
        display: flex;
        justify-content: flex-end;
    }

    .search-input {
        padding: 4px 8px 4px 24px;
        border: 1px solid #d1d5db;
        border-radius: 2px;
        font-size: 11px;
        width: 200px;
        outline: none;
        background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="%239ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>') no-repeat 6px center;
    }

    .layout-container {
        display: flex;
        height: calc(100vh - 72px);
        background: white;
        overflow: hidden;
        flex-direction: column;
    }

    .table-container {
        flex: 1;
        overflow-y: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
    }

    th {
        background: #f3f4f6;
        padding: 6px 10px;
        text-align: left;
        font-weight: bold;
        color: #374151;
        border: 1px solid #d1d5db;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    td {
        padding: 6px 10px;
        border: 1px solid #d1d5db;
        color: #374151;
        vertical-align: middle;
    }

    .btn-action {
        background: white;
        border: 1px solid #d1d5db;
        cursor: pointer;
        color: #374151;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 4px;
        width: 24px;
        height: 24px;
        border-radius: 2px;
    }
    
    .btn-action:hover {
        background: #f3f4f6;
    }
    
    .row-actions {
        display: flex;
        justify-content: center;
        gap: 3px;
    }

    .input-field {
        width: 100%;
        padding: 4px 6px;
        border: 1px solid #d1d5db;
        border-radius: 2px;
        font-size: 11px;
        outline: none;
    }

    .input-field:focus {
        border-color: #9ca3af;
    }
    
    .input-display {
        display: block;
        padding: 4px 6px;
        border: 1px solid #d1d5db;
        border-radius: 2px;
        background: white;
        color: #374151;
        width: 100%;
    }

    .alert-success {
        background: #dcfce7;
        color: #166534;
        padding: 10px 20px;
        border-bottom: 1px solid #bbf7d0;
        font-size: 12px;
        position: absolute;
        top: 35px;
        left: 0;
        right: 0;
        z-index: 100;
        animation: fadeOut 3s forwards;
        animation-delay: 2s;
    }

    @keyframes fadeOut {
        to { opacity: 0; visibility: hidden; }
    }
</style>
@endsection

@section('content')
<div class="main-content-inner full-width" style="padding: 0; background: white; height: 100vh; overflow: hidden; position: relative;">
    
    <div class="page-header">
        <h1 class="page-title">Configuration des caisses</h1>
        <a href="{{ url('/') }}" class="btn-close">&times;</a>
    </div>

    <div class="toolbar">
        <input type="text" id="searchInput" class="search-input" placeholder="Enter text to search..." onkeyup="filterTable()">
    </div>

    @if(session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="layout-container">
        <div class="table-container">
            <table style="border-top: none; border-left: none; border-right: none;" id="caissesTable">
                <thead>
                    <tr>
                        <th style="border-top: none; border-left: none;">Libelle</th>
                        <th style="border-top: none;">Compteur</th>
                        <th style="border-top: none;">Numéro</th>
                        <th style="border-top: none;">Site</th>
                        <th style="border-top: none;">Agence</th>
                        <th style="border-top: none;">Client</th>
                        <th style="border-top: none;">Station</th>
                        <th style="border-top: none; text-align: center;">Bloque</th>
                        <th style="width: 80px; text-align: center; border-top: none; border-right: none; padding: 2px;">
                            <button class="btn-action" style="font-weight: bold; font-size: 14px;" onclick="showAddForm()">
                                +
                            </button>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Ligne d'ajout cachée -->
                    <tr id="addCaisseRow" style="display: none;">
                        <td colspan="9" style="padding: 2px; border-left: none; border-right: none;">
                            <form id="addForm" action="{{ route('parametre.caisse.store') }}" method="POST" style="margin: 0; display: flex; width: 100%; gap: 5px;">
                                @csrf
                                <div style="flex: 1;"><input type="text" name="libelle" class="input-field" placeholder="Libelle" required></div>
                                <div style="width: 80px;"><input type="number" name="compteur" class="input-field" placeholder="Compteur" required></div>
                                <div style="width: 80px;"><input type="number" name="numero" class="input-field" placeholder="Numéro" required></div>
                                
                                <div style="flex: 1;">
                                    <select name="siteid" class="input-field" required>
                                        <option value="">Sélectionnez</option>
                                        @foreach($sites as $s)
                                            <option value="{{ $s->siteid }}">{{ $s->libelle }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div style="flex: 1;">
                                    <select name="agencebid" class="input-field">
                                        <option value="">Sélectionnez</option>
                                        @foreach($agences as $a)
                                            <option value="{{ $a->agencebid }}">{{ $a->libelle }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div style="flex: 1;">
                                    <select name="clientid" class="input-field">
                                        <option value="">Sélectionnez</option>
                                        @foreach($clients as $c)
                                            <option value="{{ $c->clientid }}">{{ $c->nom }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div style="flex: 1;">
                                    <select name="machineid" class="input-field">
                                        <option value="">Sélectionnez</option>
                                        @foreach($stations as $st)
                                            <option value="{{ $st->stationid }}">{{ $st->libelle }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div style="width: 50px; text-align: center; display: flex; align-items: center; justify-content: center;">
                                    <input type="checkbox" name="bloque" value="1">
                                </div>
                                <div class="row-actions" style="width: 80px; padding: 0 5px;">
                                    <button type="button" class="btn-action" onclick="document.getElementById('addForm').submit()" title="Save">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><polyline points="19 12 12 19 5 12"></polyline></svg>
                                    </button>
                                    <button type="button" class="btn-action" onclick="hideAddForm()" title="Cancel">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                    </button>
                                </div>
                            </form>
                        </td>
                    </tr>

                    @foreach($caisses as $caisse)
                    <tr id="row-{{ $caisse->caisseid }}" class="data-row">
                        <td style="border-left: none;" class="search-cell">{{ $caisse->libelle }}</td>
                        <td class="search-cell" style="text-align: right;">{{ number_format($caisse->compteur, 0, '', ' ') }}</td>
                        <td class="search-cell" style="text-align: right;">{{ $caisse->numero }}</td>
                        <td class="search-cell">
                            <div class="input-display">{{ $caisse->site_libelle }}</div>
                        </td>
                        <td class="search-cell">
                            <div class="input-display">{{ $caisse->agence_libelle }}</div>
                        </td>
                        <td class="search-cell">
                            <div class="input-display">{{ $caisse->client_nom }}</div>
                        </td>
                        <td class="search-cell">
                            <div class="input-display">{{ $caisse->station_libelle }}</div>
                        </td>
                        <td style="text-align: center;">
                            <input type="checkbox" disabled {{ $caisse->bloque ? 'checked' : '' }}>
                        </td>
                        <td style="padding: 2px; border-right: none;">
                            <div class="row-actions">
                                <button class="btn-action" onclick="showEditForm('{{ $caisse->caisseid }}')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                                </button>
                                <form action="{{ route('parametre.caisse.destroy', $caisse->caisseid) }}" method="POST" style="margin: 0;" onsubmit="return confirm('Êtes-vous sûr ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                    </button>
                                </form>
                                <button class="btn-action" title="Paramètres" onclick="alert('Paramètres avancés pour cette caisse')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Formulaire d'édition caché -->
                    <tr id="editRow-{{ $caisse->caisseid }}" style="display: none;">
                        <td colspan="9" style="padding: 2px; border-left: none; border-right: none;">
                            <form id="editForm-{{ $caisse->caisseid }}" action="{{ route('parametre.caisse.update', $caisse->caisseid) }}" method="POST" style="margin: 0; display: flex; width: 100%; gap: 5px;">
                                @csrf
                                @method('PUT')
                                <div style="flex: 1;"><input type="text" name="libelle" class="input-field" value="{{ $caisse->libelle }}" required></div>
                                <div style="width: 80px;"><input type="number" name="compteur" class="input-field" value="{{ $caisse->compteur }}" required></div>
                                <div style="width: 80px;"><input type="number" name="numero" class="input-field" value="{{ $caisse->numero }}" required></div>
                                
                                <div style="flex: 1;">
                                    <select name="siteid" class="input-field" required>
                                        <option value="">Sélectionnez</option>
                                        @foreach($sites as $s)
                                            <option value="{{ $s->siteid }}" {{ $caisse->siteid == $s->siteid ? 'selected' : '' }}>{{ $s->libelle }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div style="flex: 1;">
                                    <select name="agencebid" class="input-field">
                                        <option value="">Sélectionnez</option>
                                        @foreach($agences as $a)
                                            <option value="{{ $a->agencebid }}" {{ $caisse->agencebid == $a->agencebid ? 'selected' : '' }}>{{ $a->libelle }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div style="flex: 1;">
                                    <select name="clientid" class="input-field">
                                        <option value="">Sélectionnez</option>
                                        @foreach($clients as $c)
                                            <option value="{{ $c->clientid }}" {{ $caisse->clientid == $c->clientid ? 'selected' : '' }}>{{ $c->nom }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div style="flex: 1;">
                                    <select name="machineid" class="input-field">
                                        <option value="">Sélectionnez</option>
                                        @foreach($stations as $st)
                                            <option value="{{ $st->stationid }}" {{ $caisse->machineid == $st->stationid ? 'selected' : '' }}>{{ $st->libelle }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div style="width: 50px; text-align: center; display: flex; align-items: center; justify-content: center;">
                                    <input type="checkbox" name="bloque" value="1" {{ $caisse->bloque ? 'checked' : '' }}>
                                </div>
                                <div class="row-actions" style="width: 80px; padding: 0 5px;">
                                    <button type="button" class="btn-action" onclick="document.getElementById('editForm-{{ $caisse->caisseid }}').submit()" title="Save">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><polyline points="19 12 12 19 5 12"></polyline></svg>
                                    </button>
                                    <button type="button" class="btn-action" onclick="hideEditForm('{{ $caisse->caisseid }}')" title="Cancel">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                    </button>
                                </div>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    function showAddForm() {
        document.getElementById('addCaisseRow').style.display = 'table-row';
        document.getElementById('addCaisseRow').querySelector('input[name="libelle"]').focus();
    }

    function hideAddForm() {
        document.getElementById('addCaisseRow').style.display = 'none';
    }

    function showEditForm(id) {
        document.querySelectorAll('[id^="editRow-"]').forEach(el => el.style.display = 'none');
        document.querySelectorAll('[id^="row-"]').forEach(el => el.style.display = 'table-row');
        
        document.getElementById('row-' + id).style.display = 'none';
        document.getElementById('editRow-' + id).style.display = 'table-row';
        document.getElementById('editRow-' + id).querySelector('input[name="libelle"]').focus();
    }

    function hideEditForm(id) {
        document.getElementById('editRow-' + id).style.display = 'none';
        document.getElementById('row-' + id).style.display = 'table-row';
    }

    function filterTable() {
        let input = document.getElementById('searchInput');
        let filter = input.value.toUpperCase();
        let rows = document.querySelectorAll('.data-row');

        rows.forEach(row => {
            let cells = row.querySelectorAll('.search-cell');
            let match = false;
            cells.forEach(cell => {
                if (cell.innerText.toUpperCase().indexOf(filter) > -1) {
                    match = true;
                }
            });
            row.style.display = match ? 'table-row' : 'none';
        });
    }
</script>
@endsection
