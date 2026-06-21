@extends('layouts.app')

@section('title', 'Gestion des droits')

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
        border-top: 1px solid #d1d5db; /* Add top border since padding is 0 */
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

    .layout-container {
        display: flex;
        height: calc(100vh - 40px);
        background: white;
        overflow: hidden;
    }

    .left-pane {
        width: 30%;
        border-right: 1px solid #d1d5db;
        display: flex;
        flex-direction: column;
    }

    .right-pane {
        width: 70%;
        display: flex;
        flex-direction: column;
    }

    .pane-toolbar {
        display: flex;
        justify-content: flex-end;
        padding: 4px 10px;
        border-bottom: 1px solid #d1d5db;
        min-height: 35px;
        align-items: center;
        gap: 5px;
    }

    .search-container {
        padding: 8px 10px;
        border-bottom: 1px solid #d1d5db;
        display: flex;
        justify-content: flex-end;
    }

    .search-input {
        width: 300px;
        padding: 4px 8px 4px 28px;
        border: 1px solid #d1d5db;
        border-radius: 2px;
        font-size: 11px;
        background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="%239ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>') no-repeat 8px center;
    }

    .search-input:focus {
        outline: none;
        border-color: #9ca3af;
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

    /* Small square buttons with border for actions */
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
    
    .btn-action.active {
        background: #e5e7eb;
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

    .row-active {
        background: #e5e7eb;
    }

    .no-data {
        text-align: center;
        padding: 40px;
        color: #111827;
        font-weight: bold;
        font-size: 12px;
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
        <h1 class="page-title">Gestion des droits</h1>
        <a href="{{ url('/') }}" class="btn-close">&times;</a>
    </div>

    @if(session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="layout-container">
        <!-- LEFT PANE: NIVEAUX (ROLES) -->
        <div class="left-pane">
            <div class="table-container">
                <table style="border-top: none;">
                    <thead>
                        <tr>
                            <th style="width: 75%; border-top: none; border-left: none;">Niveau</th>
                            <th style="width: 25%; text-align: center; border-top: none; padding: 2px;">
                                <button class="btn-action" style="font-weight: bold; font-size: 14px;" onclick="showAddForm()">
                                    +
                                </button>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Ligne d'ajout cachée -->
                        <tr id="addRoleRow" style="display: none;">
                            <td style="padding: 2px;">
                                <form id="addForm" action="{{ route('parametre.droit.role.store') }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <input type="text" name="libelle" class="input-field" required>
                                </form>
                            </td>
                            <td style="padding: 2px;">
                                <div class="row-actions">
                                    <button type="button" class="btn-action" onclick="document.getElementById('addForm').submit()" title="Save">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><polyline points="19 12 12 19 5 12"></polyline></svg>
                                    </button>
                                    <button type="button" class="btn-action" onclick="hideAddForm()" title="Cancel">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        @foreach($roles as $role)
                        <tr class="{{ $selectedRoleId == $role->userdroitid ? 'row-active' : '' }}" id="row-{{ $role->userdroitid }}">
                            <td style="cursor: pointer; border-left: none;" onclick="window.location='{{ route('parametre.droit.index', ['id' => $role->userdroitid]) }}'">
                                {{ $role->libelle }}
                            </td>
                            <td style="padding: 2px;">
                                <div class="row-actions">
                                    <button class="btn-action" onclick="showEditForm('{{ $role->userdroitid }}')">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                                    </button>
                                    <form action="{{ route('parametre.droit.role.destroy', $role->userdroitid) }}" method="POST" style="margin: 0;" onsubmit="return confirm('Êtes-vous sûr ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-action">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <!-- Formulaire d'édition caché -->
                        <tr id="editRow-{{ $role->userdroitid }}" style="display: none;">
                            <td style="padding: 2px; border-left: none;">
                                <form id="editForm-{{ $role->userdroitid }}" action="{{ route('parametre.droit.role.update', $role->userdroitid) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="libelle" class="input-field" value="{{ $role->libelle }}" required>
                                </form>
                            </td>
                            <td style="padding: 2px;">
                                <div class="row-actions">
                                    <button type="button" class="btn-action" onclick="document.getElementById('editForm-{{ $role->userdroitid }}').submit()" title="Save">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><polyline points="19 12 12 19 5 12"></polyline></svg>
                                    </button>
                                    <button type="button" class="btn-action" onclick="hideEditForm('{{ $role->userdroitid }}')" title="Cancel">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- RIGHT PANE: PERMISSIONS -->
        <div class="right-pane">
            <form id="permissionsForm" action="{{ $selectedRoleId ? route('parametre.droit.permissions.update', $selectedRoleId) : '#' }}" method="POST" style="margin: 0; display: flex; flex-direction: column; height: 100%;">
                @csrf
                <div class="pane-toolbar">
                    <button type="button" class="btn-action" onclick="window.location.reload();" title="Actualiser">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path><polyline points="3 3 3 8 8 8"></polyline></svg>
                    </button>
                    <button type="submit" class="btn-action" title="Enregistrer" {{ !$selectedRoleId ? 'disabled' : '' }}>
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                    </button>
                </div>
                
                <div class="search-container">
                    <input type="text" id="searchInput" class="search-input" placeholder="Enter text to search..." onkeyup="filterPermissions()">
                </div>

                <div class="table-container">
                    @if(!$selectedRoleId)
                        <div class="no-data">No data to display</div>
                    @else
                        <table id="permissionsTable" style="border-top: none;">
                            <thead>
                                <tr>
                                    <th style="width: 50%; border-top: none; border-left: none;">Libelle</th>
                                    <th style="width: 25%; text-align: center; border-top: none;">Bloque</th>
                                    <th style="width: 25%; text-align: center; border-top: none; border-right: none;">Badge</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($permissions as $perm)
                                <tr class="perm-row">
                                    <td style="border-left: none;">{{ $perm->libelle }}</td>
                                    <td style="text-align: center;">
                                        <input type="checkbox" name="permissions[{{ $perm->typedroitid }}][bloque]" value="1" {{ $perm->bloque ? 'checked' : '' }} style="margin: 0;">
                                    </td>
                                    <td style="text-align: center; border-right: none;">
                                        <input type="checkbox" name="permissions[{{ $perm->typedroitid }}][badge]" value="1" {{ $perm->badge ? 'checked' : '' }} style="margin: 0;">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    function showAddForm() {
        document.getElementById('addRoleRow').style.display = 'table-row';
        document.getElementById('addRoleRow').querySelector('input').focus();
    }

    function hideAddForm() {
        document.getElementById('addRoleRow').style.display = 'none';
    }

    function showEditForm(id) {
        document.querySelectorAll('[id^="editRow-"]').forEach(el => el.style.display = 'none');
        document.querySelectorAll('[id^="row-"]').forEach(el => el.style.display = 'table-row');
        
        document.getElementById('row-' + id).style.display = 'none';
        document.getElementById('editRow-' + id).style.display = 'table-row';
        document.getElementById('editRow-' + id).querySelector('input').focus();
    }

    function hideEditForm(id) {
        document.getElementById('editRow-' + id).style.display = 'none';
        document.getElementById('row-' + id).style.display = 'table-row';
    }

    function filterPermissions() {
        let input = document.getElementById("searchInput");
        let filter = input.value.toUpperCase();
        let table = document.getElementById("permissionsTable");
        if (!table) return;
        let trs = table.getElementsByClassName("perm-row");

        for (let i = 0; i < trs.length; i++) {
            let td = trs[i].getElementsByTagName("td")[0]; // Libelle
            if (td) {
                let txtValue = td.textContent || td.innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    trs[i].style.display = "";
                } else {
                    trs[i].style.display = "none";
                }
            }
        }
    }
</script>
@endsection
