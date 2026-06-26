@extends('layouts.app')

@section('title', 'Gestion des utilisateurs - Golden Pos')

@section('content')
<div class="pos-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Gestion des Utilisateurs</h1>
            <p class="page-subtitle">Gérez les comptes d'accès au back-office et leurs droits.</p>
        </div>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="showAddForm()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Nouvel Utilisateur
            </button>
        </div>
    </div>

    @if(session('success'))
        <div style="background: var(--success); color: white; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; display: flex; align-items: center; gap: 8px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="content-card">
        <div class="table-responsive">
            <table class="data-table" id="utilisateursTable">
                <thead>
                    <tr>
                        <th style="width: 45%;">Login</th>
                        <th style="width: 45%;">Droit (Rôle)</th>
                        <th style="width: 10%; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Ligne d'ajout cachée -->
                    <tr id="addUtilisateurRow" style="display: none; background: #f8fafc;">
                        <td colspan="3" style="padding: 10px;">
                            <form id="addForm" action="{{ route('parametre.utilisateur.store') }}" method="POST" style="margin: 0; display: flex; width: 100%; gap: 10px; align-items: center;">
                                @csrf
                                <div style="flex: 1;">
                                    <input type="text" name="login" class="form-control" placeholder="Login" required>
                                </div>
                                <div style="flex: 1;">
                                    <input type="password" name="password" class="form-control" placeholder="Mot de passe" required>
                                </div>
                                <div style="flex: 1;">
                                    <select name="userdroitid" class="form-control" required>
                                        <option value="">-- Sélectionnez un droit --</option>
                                        @foreach($droits as $droit)
                                            <option value="{{ $droit->userdroitid }}">{{ $droit->libelle }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div style="width: 10%; display: flex; gap: 4px; justify-content: center;">
                                    <button type="button" class="btn btn-primary" style="padding: 6px; height: auto;" onclick="document.getElementById('addForm').submit()" title="Enregistrer">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    </button>
                                    <button type="button" class="btn btn-outline" style="padding: 6px; height: auto;" onclick="hideAddForm()" title="Annuler">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                    </button>
                                </div>
                            </form>
                        </td>
                    </tr>

                    @forelse($utilisateurs as $user)
                    <tr id="row-{{ $user->userid }}" class="hover-row">
                        <td class="font-medium" style="color: var(--primary);">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--bg-color); display: flex; align-items: center; justify-content: center; color: var(--primary); font-weight: 600; font-size: 14px;">
                                    {{ strtoupper(substr($user->login, 0, 1)) }}
                                </div>
                                {{ $user->login }}
                            </div>
                        </td>
                        <td>
                            <span class="status-badge status-pending" style="background: #e0e7ff; color: #4338ca; border-color: #c7d2fe;">{{ $user->droit_libelle }}</span>
                        </td>
                        <td style="text-align: center;">
                            <div style="display: flex; gap: 4px; justify-content: center;">
                                <button class="btn btn-outline" style="padding: 4px 8px; height: auto;" onclick="showEditForm('{{ $user->userid }}')" title="Modifier">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                                </button>
                                <form action="{{ route('parametre.utilisateur.destroy', $user->userid) }}" method="POST" style="margin: 0;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline" style="padding: 4px 8px; height: auto; color: #dc2626; border-color: #fecaca; background: #fef2f2;" title="Supprimer">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Formulaire d'édition caché -->
                    <tr id="editRow-{{ $user->userid }}" style="display: none; background: #f8fafc;">
                        <td colspan="3" style="padding: 10px;">
                            <form id="editForm-{{ $user->userid }}" action="{{ route('parametre.utilisateur.update', $user->userid) }}" method="POST" style="margin: 0; display: flex; width: 100%; gap: 10px; align-items: center;">
                                @csrf
                                @method('PUT')
                                <div style="flex: 1;">
                                    <input type="text" name="login" class="form-control" value="{{ $user->login }}" required>
                                </div>
                                <div style="flex: 1;">
                                    <input type="password" name="password" class="form-control" placeholder="Nouveau mdp (laisser vide pour garder)">
                                </div>
                                <div style="flex: 1;">
                                    <select name="userdroitid" class="form-control" required>
                                        @foreach($droits as $droit)
                                            <option value="{{ $droit->userdroitid }}" {{ $user->userdroitid == $droit->userdroitid ? 'selected' : '' }}>
                                                {{ $droit->libelle }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div style="width: 10%; display: flex; gap: 4px; justify-content: center;">
                                    <button type="button" class="btn btn-primary" style="padding: 6px; height: auto;" onclick="document.getElementById('editForm-{{ $user->userid }}').submit()" title="Enregistrer">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    </button>
                                    <button type="button" class="btn btn-outline" style="padding: 6px; height: auto;" onclick="hideEditForm('{{ $user->userid }}')" title="Annuler">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                    </button>
                                </div>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" style="padding: 40px; text-align: center; color: var(--text-muted);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 16px; opacity: 0.5;">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                            <div style="font-weight: 600; font-size: 15px; margin-bottom: 8px;">Aucun utilisateur trouvé</div>
                        </td>
                    </tr>
                    @endforelse
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
