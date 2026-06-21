@extends('layouts.app')

@section('title', 'Employees')

@section('styles')
<style>
    .page-header {
        padding: 10px 20px;
        background: white;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .page-title {
        font-size: 14px;
        font-weight: bold;
        color: var(--text);
        margin: 0;
    }

    .btn-close {
        font-size: 24px;
        line-height: 1;
        cursor: pointer;
        color: var(--text);
        text-decoration: none;
        padding: 0px 8px;
        border: 1px solid #e2e8f0;
        background: white;
        border-radius: 2px;
        font-weight: 300;
    }

    .table-container {
        padding: 0;
        background: white;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }

    th {
        background: #f8fafc;
        padding: 8px 12px;
        text-align: left;
        font-weight: bold;
        color: var(--text);
        border: 1px solid #e2e8f0;
    }

    td {
        padding: 8px 12px;
        border: 1px solid #e2e8f0;
        color: var(--text);
        vertical-align: middle;
    }

    .btn-icon {
        background: transparent;
        border: none;
        cursor: pointer;
        color: var(--text);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 2px;
    }
    
    .btn-icon:hover {
        opacity: 0.7;
    }

    .form-row td {
        background: white;
        padding: 15px 12px;
    }

    .input-field {
        width: 400px;
        padding: 6px 8px;
        border: 1px solid #b794f6; /* light purple border */
        border-radius: 4px;
        font-size: 12px;
        outline: none;
    }

    .input-field:focus {
        box-shadow: 0 0 0 1px #9333ea;
    }

    .form-group-inline {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-save {
        background: #6b21a8; /* purple */
        color: white;
        border: none;
        padding: 6px 16px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
    }

    .btn-cancel {
        background: white;
        color: var(--text);
        border: 1px solid #e2e8f0;
        padding: 6px 16px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
    }

    .alert-success {
        background: #dcfce7;
        color: #166534;
        padding: 10px 20px;
        border-bottom: 1px solid #bbf7d0;
        font-size: 13px;
    }
</style>
@endsection

@section('content')
<div class="main-content-inner full-width" style="padding: 0; background: white; min-height: 100vh;">
    
    <div class="page-header">
        <h1 class="page-title">Employees</h1>
        <a href="{{ url('/') }}" class="btn-close">&times;</a>
    </div>

    @if(session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th style="width: 25%;">Code</th>
                    <th style="width: 45%;">Nom</th>
                    <th style="width: 25%;">Bloque</th>
                    <th style="width: 5%; text-align: center;">
                        <button class="btn-icon" onclick="showAddForm()" style="font-weight: 900; font-size: 18px; color: #0f172a;">
                            +
                        </button>
                    </th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <!-- Ligne d'ajout (cachée par défaut) -->
                <tr id="addRow" class="form-row" style="display: none;">
                    <td colspan="4">
                        <form action="{{ route('parametre.vendeur.store') }}" method="POST" style="margin: 0; display: flex; align-items: center; justify-content: space-between; width: 100%;">
                            @csrf
                            <div style="display: flex; gap: 40px; align-items: center;">
                                <div class="form-group-inline">
                                    <label style="color: var(--text); font-size: 12px;">Nom :</label>
                                    <input type="text" name="nom" class="input-field" required>
                                </div>
                                <div class="form-group-inline">
                                    <label style="color: var(--text); font-size: 12px;">Bloque :</label>
                                    <input type="checkbox" name="bloque" value="1" style="width: 14px; height: 14px; margin-top: 2px;">
                                </div>
                            </div>
                            <div style="display: flex; gap: 8px;">
                                <button type="submit" class="btn-save">Save</button>
                                <button type="button" class="btn-cancel" onclick="hideAddForm()">Cancel</button>
                            </div>
                        </form>
                    </td>
                </tr>

                @foreach($vendeurs as $vendeur)
                <!-- Ligne d'affichage -->
                <tr id="row-{{ $vendeur->employeeid }}">
                    <td>{{ str_pad($vendeur->employeeid, 3, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ $vendeur->nom }}</td>
                    <td>
                        <input type="checkbox" disabled {{ $vendeur->bloque ? 'checked' : '' }} style="width: 14px; height: 14px;">
                    </td>
                    <td style="text-align: center;">
                        <button class="btn-icon" onclick="showEditForm('{{ $vendeur->employeeid }}')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 20h9"></path>
                                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                            </svg>
                        </button>
                    </td>
                </tr>

                <!-- Ligne d'édition (cachée par défaut) -->
                <tr id="editRow-{{ $vendeur->employeeid }}" class="form-row" style="display: none;">
                    <td colspan="4">
                        <form action="{{ route('parametre.vendeur.update', $vendeur->employeeid) }}" method="POST" style="margin: 0; display: flex; align-items: center; justify-content: space-between; width: 100%;">
                            @csrf
                            @method('PUT')
                            <div style="display: flex; gap: 40px; align-items: center;">
                                <div class="form-group-inline">
                                    <label style="color: var(--text); font-size: 12px;">Nom :</label>
                                    <input type="text" name="nom" class="input-field" value="{{ $vendeur->nom }}" required>
                                </div>
                                <div class="form-group-inline">
                                    <label style="color: var(--text); font-size: 12px;">Bloque :</label>
                                    <input type="checkbox" name="bloque" value="1" {{ $vendeur->bloque ? 'checked' : '' }} style="width: 14px; height: 14px; margin-top: 2px;">
                                </div>
                            </div>
                            <div style="display: flex; gap: 8px;">
                                <button type="submit" class="btn-save">Save</button>
                                <button type="button" class="btn-cancel" onclick="hideEditForm('{{ $vendeur->employeeid }}')">Cancel</button>
                            </div>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>

@endsection

@section('scripts')
<script>
    function showAddForm() {
        document.getElementById('addRow').style.display = 'table-row';
        // Hide all edit forms
        document.querySelectorAll('[id^="editRow-"]').forEach(el => el.style.display = 'none');
        document.querySelectorAll('[id^="row-"]').forEach(el => el.style.display = 'table-row');
    }

    function hideAddForm() {
        document.getElementById('addRow').style.display = 'none';
    }

    function showEditForm(id) {
        // Hide add form
        hideAddForm();
        // Hide all other edit forms
        document.querySelectorAll('[id^="editRow-"]').forEach(el => el.style.display = 'none');
        // Show all display rows
        document.querySelectorAll('[id^="row-"]').forEach(el => el.style.display = 'table-row');

        // Hide current display row and show edit row
        document.getElementById('row-' + id).style.display = 'none';
        document.getElementById('editRow-' + id).style.display = 'table-row';
    }

    function hideEditForm(id) {
        document.getElementById('editRow-' + id).style.display = 'none';
        document.getElementById('row-' + id).style.display = 'table-row';
    }
</script>
@endsection
