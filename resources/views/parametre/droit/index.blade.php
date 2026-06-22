@extends('layouts.app')

@section('title', 'Gestion des droits - Velaro')

@section('content')
<div class="pos-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Gestion des Droits</h1>
            <p class="page-subtitle">Configurez les rôles (niveaux) et leurs permissions système.</p>
        </div>
        <div class="header-actions">
            <div class="search-bar" style="position: relative;">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--text-muted);">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" id="searchInput" placeholder="Rechercher une permission..." onkeyup="filterPermissions()" style="padding-left: 32px; height: 36px; border: 1px solid var(--border); border-radius: 6px; font-size: 13px; outline: none; width: 250px;">
            </div>
            <button type="submit" form="permissionsForm" class="btn btn-primary" {{ !$selectedRoleId ? 'disabled' : '' }}>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                Enregistrer les permissions
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

    <div style="display: flex; gap: 20px; height: calc(100vh - 190px);">
        
        <!-- Colonne de gauche : Rôles -->
        <div class="content-card" style="flex: 0 0 350px; display: flex; flex-direction: column; overflow: hidden;">
            <div style="padding: 16px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
                <h3 style="margin: 0; font-size: 14px; font-weight: 600; color: var(--text-main);">Niveaux d'accès</h3>
                <button class="btn btn-outline" style="padding: 4px 8px; height: auto;" onclick="showAddForm()" title="Ajouter un niveau">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                </button>
            </div>
            
            <div class="table-responsive" style="flex: 1; overflow-y: auto;">
                <table class="data-table">
                    <tbody>
                        <!-- Ligne d'ajout cachée -->
                        <tr id="addRoleRow" style="display: none; background: #e0f2fe;">
                            <td colspan="2" style="padding: 10px;">
                                <form id="addForm" action="{{ route('parametre.droit.role.store') }}" method="POST" style="margin: 0; display: flex; gap: 8px;">
                                    @csrf
                                    <input type="text" name="libelle" class="form-control" placeholder="Nom du niveau" required style="flex: 1;">
                                    <button type="button" class="btn btn-primary" style="padding: 6px; height: auto;" onclick="document.getElementById('addForm').submit()">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    </button>
                                    <button type="button" class="btn btn-outline" style="padding: 6px; height: auto;" onclick="hideAddForm()">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>

                        @foreach($roles as $role)
                        <tr class="{{ $selectedRoleId == $role->userdroitid ? 'row-active' : 'hover-row' }}" id="row-{{ $role->userdroitid }}" style="cursor: pointer;" onclick="if(event.target.tagName !== 'BUTTON' && event.target.tagName !== 'svg' && event.target.tagName !== 'polyline' && event.target.tagName !== 'path' && event.target.tagName !== 'line') window.location='{{ route('parametre.droit.index', ['id' => $role->userdroitid]) }}'">
                            <td style="font-weight: {{ $selectedRoleId == $role->userdroitid ? '600' : '500' }}; color: {{ $selectedRoleId == $role->userdroitid ? 'var(--primary)' : 'var(--text-main)' }}; display: flex; align-items: center; gap: 8px;">
                                @if($selectedRoleId == $role->userdroitid)
                                    <div style="width: 6px; height: 6px; border-radius: 50%; background: var(--primary);"></div>
                                @endif
                                {{ $role->libelle }}
                            </td>
                            <td style="width: 80px; text-align: right;">
                                <div style="display: flex; gap: 4px; justify-content: flex-end;">
                                    <button class="btn btn-outline" style="padding: 4px; height: auto;" onclick="showEditForm('{{ $role->userdroitid }}'); event.stopPropagation();">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                                    </button>
                                    <form action="{{ route('parametre.droit.role.destroy', $role->userdroitid) }}" method="POST" style="margin: 0;" onsubmit="return confirm('Êtes-vous sûr ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline" style="padding: 4px; height: auto; color: #dc2626; border-color: #fecaca; background: #fef2f2;" onclick="event.stopPropagation();">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <!-- Formulaire d'édition caché -->
                        <tr id="editRow-{{ $role->userdroitid }}" style="display: none; background: #f8fafc;">
                            <td colspan="2" style="padding: 10px;">
                                <form id="editForm-{{ $role->userdroitid }}" action="{{ route('parametre.droit.role.update', $role->userdroitid) }}" method="POST" style="margin: 0; display: flex; gap: 8px;">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="libelle" class="form-control" value="{{ $role->libelle }}" required style="flex: 1;">
                                    <button type="button" class="btn btn-primary" style="padding: 6px; height: auto;" onclick="document.getElementById('editForm-{{ $role->userdroitid }}').submit()">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    </button>
                                    <button type="button" class="btn btn-outline" style="padding: 6px; height: auto;" onclick="hideEditForm('{{ $role->userdroitid }}')">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Colonne de droite : Permissions -->
        <div class="content-card" style="flex: 1; display: flex; flex-direction: column; overflow: hidden;">
            <form id="permissionsForm" action="{{ $selectedRoleId ? route('parametre.droit.permissions.update', $selectedRoleId) : '#' }}" method="POST" style="margin: 0; display: flex; flex-direction: column; height: 100%;">
                @csrf
                <div style="padding: 16px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: #f8fafc;">
                    <h3 style="margin: 0; font-size: 14px; font-weight: 600; color: var(--text-main);">
                        Permissions associées 
                        @if($selectedRoleId)
                            <span style="color: var(--primary); font-weight: 600;">— {{ $roles->firstWhere('userdroitid', $selectedRoleId)->libelle ?? '' }}</span>
                        @endif
                    </h3>
                    <button type="button" class="btn btn-outline" onclick="window.location.reload();" title="Actualiser la liste">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path><polyline points="3 3 3 8 8 8"></polyline></svg>
                        Rafraîchir
                    </button>
                </div>
                
                <div class="table-responsive" style="flex: 1; overflow-y: auto;">
                    @if(!$selectedRoleId)
                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: var(--text-muted); opacity: 0.7;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 16px;"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path></svg>
                            <span style="font-size: 15px; font-weight: 500;">Sélectionnez un niveau d'accès pour configurer ses permissions</span>
                        </div>
                    @else
                        <table class="data-table" id="permissionsTable">
                            <thead style="position: sticky; top: 0; z-index: 2;">
                                <tr>
                                    <th style="width: 60%;">Libellé de la permission</th>
                                    <th style="width: 20%; text-align: center;">Bloqué</th>
                                    <th style="width: 20%; text-align: center;">Badge (Pin)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($permissions as $perm)
                                <tr class="perm-row hover-row">
                                    <td>{{ $perm->libelle }}</td>
                                    <td style="text-align: center;">
                                        <input type="checkbox" name="permissions[{{ $perm->typedroitid }}][bloque]" value="1" {{ $perm->bloque ? 'checked' : '' }} style="width: 16px; height: 16px; accent-color: var(--primary); cursor: pointer;">
                                    </td>
                                    <td style="text-align: center;">
                                        <input type="checkbox" name="permissions[{{ $perm->typedroitid }}][badge]" value="1" {{ $perm->badge ? 'checked' : '' }} style="width: 16px; height: 16px; accent-color: var(--primary); cursor: pointer;">
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

<style>
    .row-active {
        background-color: #f0fdfa !important; /* light teal indicating active */
    }
</style>
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
