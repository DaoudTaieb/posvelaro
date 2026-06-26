@extends('layouts.app')

@section('title', 'Vendeurs / Employés - Golden Pos')

@section('content')
<div class="pos-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Gestion des Vendeurs</h1>
            <p class="page-subtitle">Gérez la liste des employés et vendeurs du magasin.</p>
        </div>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="showAddForm()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Nouveau Vendeur
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
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 15%; text-align: center;">Code</th>
                        <th style="width: 55%;">Nom de l'employé</th>
                        <th style="width: 15%; text-align: center;">Statut</th>
                        <th style="width: 15%; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <!-- Ligne d'ajout (cachée par défaut) -->
                    <tr id="addRow" style="display: none; background: #f8fafc;">
                        <td colspan="4" style="padding: 10px;">
                            <form action="{{ route('parametre.vendeur.store') }}" method="POST" style="margin: 0; display: flex; align-items: center; gap: 15px;">
                                @csrf
                                <div style="flex: 1; display: flex; gap: 15px; align-items: center;">
                                    <div style="flex: 1;">
                                        <input type="text" name="nom" class="form-control" placeholder="Nom du vendeur" required>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 8px; margin-right: 20px;">
                                        <label style="margin: 0; font-size: 13px; font-weight: 500;">Bloqué</label>
                                        <input type="checkbox" name="bloque" value="1" style="width: 16px; height: 16px; accent-color: var(--primary);">
                                    </div>
                                </div>
                                <div style="display: flex; gap: 4px;">
                                    <button type="submit" class="btn btn-primary" style="padding: 6px; height: auto;" title="Enregistrer">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    </button>
                                    <button type="button" class="btn btn-outline" style="padding: 6px; height: auto;" onclick="hideAddForm()" title="Annuler">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                    </button>
                                </div>
                            </form>
                        </td>
                    </tr>

                    @forelse($vendeurs as $vendeur)
                    <!-- Ligne d'affichage -->
                    <tr id="row-{{ $vendeur->employeeid }}" class="hover-row">
                        <td style="text-align: center; font-weight: 600; color: var(--text-muted);">
                            {{ str_pad($vendeur->employeeid, 3, '0', STR_PAD_LEFT) }}
                        </td>
                        <td class="font-medium" style="color: var(--primary);">
                            {{ $vendeur->nom }}
                        </td>
                        <td style="text-align: center;">
                            @if($vendeur->bloque)
                                <span class="status-badge status-draft" style="background: #fee2e2; color: #991b1b;">Bloqué</span>
                            @else
                                <span class="status-badge status-paid">Actif</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <div style="display: flex; gap: 4px; justify-content: center;">
                                <button class="btn btn-outline" style="padding: 4px 8px; height: auto;" onclick="showEditForm('{{ $vendeur->employeeid }}')" title="Modifier">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                                </button>
                                <!-- Optionally, delete logic could go here if it was implemented in the controller -->
                            </div>
                        </td>
                    </tr>

                    <!-- Ligne d'édition (cachée par défaut) -->
                    <tr id="editRow-{{ $vendeur->employeeid }}" style="display: none; background: #f8fafc;">
                        <td colspan="4" style="padding: 10px;">
                            <form action="{{ route('parametre.vendeur.update', $vendeur->employeeid) }}" method="POST" style="margin: 0; display: flex; align-items: center; gap: 15px;">
                                @csrf
                                @method('PUT')
                                <div style="flex: 1; display: flex; gap: 15px; align-items: center;">
                                    <div style="flex: 1;">
                                        <input type="text" name="nom" class="form-control" value="{{ $vendeur->nom }}" required>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 8px; margin-right: 20px;">
                                        <label style="margin: 0; font-size: 13px; font-weight: 500;">Bloqué</label>
                                        <input type="checkbox" name="bloque" value="1" {{ $vendeur->bloque ? 'checked' : '' }} style="width: 16px; height: 16px; accent-color: var(--primary);">
                                    </div>
                                </div>
                                <div style="display: flex; gap: 4px;">
                                    <button type="submit" class="btn btn-primary" style="padding: 6px; height: auto;" title="Enregistrer">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    </button>
                                    <button type="button" class="btn btn-outline" style="padding: 6px; height: auto;" onclick="hideEditForm('{{ $vendeur->employeeid }}')" title="Annuler">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                    </button>
                                </div>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="padding: 40px; text-align: center; color: var(--text-muted);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 16px; opacity: 0.5;">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                            <div style="font-weight: 600; font-size: 15px; margin-bottom: 8px;">Aucun vendeur trouvé</div>
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
        document.getElementById('addRow').style.display = 'table-row';
        // Hide all edit forms
        document.querySelectorAll('[id^="editRow-"]').forEach(el => el.style.display = 'none');
        document.querySelectorAll('[id^="row-"]').forEach(el => el.style.display = 'table-row');
        document.getElementById('addRow').querySelector('input[name="nom"]').focus();
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
        document.getElementById('editRow-' + id).querySelector('input[name="nom"]').focus();
    }

    function hideEditForm(id) {
        document.getElementById('editRow-' + id).style.display = 'none';
        document.getElementById('row-' + id).style.display = 'table-row';
    }
</script>
@endsection
