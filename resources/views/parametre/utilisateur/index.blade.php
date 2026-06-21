@extends('layouts.app')

@section('title', 'Gestion des utilisateurs')

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

    .layout-container {
        display: flex;
        height: calc(100vh - 40px);
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
        <h1 class="page-title">Utilisateurs</h1>
        <a href="{{ url('/') }}" class="btn-close">&times;</a>
    </div>

    @if(session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="layout-container">
        <div class="table-container">
            <table style="border-top: none; border-left: none; border-right: none;">
                <thead>
                    <tr>
                        <th style="width: 45%; border-top: none; border-left: none;">Login</th>
                        <th style="width: 45%; border-top: none;">Droit</th>
                        <th style="width: 10%; text-align: center; border-top: none; border-right: none; padding: 2px;">
                            <button class="btn-action" style="font-weight: bold; font-size: 14px;" onclick="showAddForm()">
                                +
                            </button>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Ligne d'ajout cachée -->
                    <tr id="addUtilisateurRow" style="display: none;">
                        <td colspan="3" style="padding: 2px; border-left: none; border-right: none;">
                            <form id="addForm" action="{{ route('parametre.utilisateur.store') }}" method="POST" style="margin: 0; display: flex; width: 100%; gap: 10px;">
                                @csrf
                                <div style="flex: 1;">
                                    <input type="text" name="login" class="input-field" placeholder="Login" required>
                                </div>
                                <div style="flex: 1;">
                                    <input type="password" name="password" class="input-field" placeholder="Mot de passe" required>
                                </div>
                                <div style="flex: 1;">
                                    <select name="userdroitid" class="input-field" required>
                                        <option value="">-- Sélectionnez un droit --</option>
                                        @foreach($droits as $droit)
                                            <option value="{{ $droit->userdroitid }}">{{ $droit->libelle }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="row-actions" style="width: 10%; padding: 0 10px;">
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

                    @foreach($utilisateurs as $user)
                    <tr id="row-{{ $user->userid }}">
                        <td style="border-left: none;">{{ $user->login }}</td>
                        <td>{{ $user->droit_libelle }}</td>
                        <td style="padding: 2px; border-right: none;">
                            <div class="row-actions">
                                <button class="btn-action" onclick="showEditForm('{{ $user->userid }}')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                                </button>
                                <form action="{{ route('parametre.utilisateur.destroy', $user->userid) }}" method="POST" style="margin: 0;" onsubmit="return confirm('Êtes-vous sûr ?');">
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
                    <tr id="editRow-{{ $user->userid }}" style="display: none;">
                        <td colspan="3" style="padding: 2px; border-left: none; border-right: none;">
                            <form id="editForm-{{ $user->userid }}" action="{{ route('parametre.utilisateur.update', $user->userid) }}" method="POST" style="margin: 0; display: flex; width: 100%; gap: 10px;">
                                @csrf
                                @method('PUT')
                                <div style="flex: 1;">
                                    <input type="text" name="login" class="input-field" value="{{ $user->login }}" required>
                                </div>
                                <div style="flex: 1;">
                                    <input type="password" name="password" class="input-field" placeholder="Nouveau mdp (laisser vide pour garder l'actuel)">
                                </div>
                                <div style="flex: 1;">
                                    <select name="userdroitid" class="input-field" required>
                                        @foreach($droits as $droit)
                                            <option value="{{ $droit->userdroitid }}" {{ $user->userdroitid == $droit->userdroitid ? 'selected' : '' }}>
                                                {{ $droit->libelle }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="row-actions" style="width: 10%; padding: 0 10px;">
                                    <button type="button" class="btn-action" onclick="document.getElementById('editForm-{{ $user->userid }}').submit()" title="Save">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><polyline points="19 12 12 19 5 12"></polyline></svg>
                                    </button>
                                    <button type="button" class="btn-action" onclick="hideEditForm('{{ $user->userid }}')" title="Cancel">
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
        document.getElementById('addUtilisateurRow').style.display = 'table-row';
        document.getElementById('addUtilisateurRow').querySelector('input[name="login"]').focus();
    }

    function hideAddForm() {
        document.getElementById('addUtilisateurRow').style.display = 'none';
    }

    function showEditForm(id) {
        document.querySelectorAll('[id^="editRow-"]').forEach(el => el.style.display = 'none');
        document.querySelectorAll('[id^="row-"]').forEach(el => el.style.display = 'table-row');
        
        document.getElementById('row-' + id).style.display = 'none';
        document.getElementById('editRow-' + id).style.display = 'table-row';
        document.getElementById('editRow-' + id).querySelector('input[name="login"]').focus();
    }

    function hideEditForm(id) {
        document.getElementById('editRow-' + id).style.display = 'none';
        document.getElementById('row-' + id).style.display = 'table-row';
    }
</script>
@endsection
