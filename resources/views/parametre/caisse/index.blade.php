@extends('layouts.app')

@section('title', 'Configuration des caisses - Golden Pos')

@section('content')
<div class="pos-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Configuration des Caisses</h1>
            <p class="page-subtitle">Gérez les caisses, compteurs et affectations aux sites et stations.</p>
        </div>
        <div class="header-actions">
            <div class="search-bar" style="position: relative;">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--text-muted);">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" id="searchInput" placeholder="Rechercher une caisse..." onkeyup="filterTable()" style="padding-left: 32px; height: 36px; border: 1px solid var(--border); border-radius: 6px; font-size: 13px; outline: none; width: 250px;">
            </div>
            <button class="btn btn-primary" onclick="showAddForm()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Nouvelle Caisse
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
            <table class="data-table" id="caissesTable">
                <thead>
                    <tr>
                        <th style="width: 15%;">Libellé</th>
                        <th style="width: 8%; text-align: center;">Compteur</th>
                        <th style="width: 8%; text-align: center;">Numéro</th>
                        <th style="width: 15%;">Site</th>
                        <th style="width: 15%;">Agence</th>
                        <th style="width: 15%;">Client</th>
                        <th style="width: 10%;">Station</th>
                        <th style="width: 6%; text-align: center;">Bloqué</th>
                        <th style="width: 8%; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Ligne d'ajout cachée -->
                    <tr id="addCaisseRow" style="display: none; background: #f8fafc;">
                        <td colspan="9" style="padding: 10px;">
                            <form id="addForm" action="{{ route('parametre.caisse.store') }}" method="POST" style="margin: 0; display: flex; width: 100%; gap: 10px; align-items: center;">
                                @csrf
                                <div style="flex: 1.5;"><input type="text" name="libelle" class="form-control" placeholder="Libellé" required></div>
                                <div style="width: 8%;"><input type="number" name="compteur" class="form-control" placeholder="Cpt" style="text-align: center;" required></div>
                                <div style="width: 8%;"><input type="number" name="numero" class="form-control" placeholder="N°" style="text-align: center;" required></div>
                                
                                <div style="flex: 1.5;">
                                    <select name="siteid" class="form-control" required>
                                        <option value="">Sélectionnez Site</option>
                                        @foreach($sites as $s)
                                            <option value="{{ $s->siteid }}">{{ $s->libelle }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div style="flex: 1.5;">
                                    <select name="agencebid" class="form-control">
                                        <option value="">Sélectionnez Agence</option>
                                        @foreach($agences as $a)
                                            <option value="{{ $a->agencebid }}">{{ $a->libelle }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div style="flex: 1.5;">
                                    <select name="clientid" class="form-control">
                                        <option value="">Sélectionnez Client</option>
                                        @foreach($clients as $c)
                                            <option value="{{ $c->clientid }}">{{ $c->nom }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div style="flex: 1;">
                                    <select name="machineid" class="form-control">
                                        <option value="">Sélectionnez Station</option>
                                        @foreach($stations as $st)
                                            <option value="{{ $st->stationid }}">{{ $st->libelle }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div style="width: 6%; text-align: center;">
                                    <input type="checkbox" name="bloque" value="1" style="width: 16px; height: 16px; accent-color: var(--primary);">
                                </div>
                                <div style="width: 8%; display: flex; gap: 4px; justify-content: center;">
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

                    @forelse($caisses as $caisse)
                    <tr id="row-{{ $caisse->caisseid }}" class="data-row hover-row">
                        <td class="search-cell font-medium" style="color: var(--primary);">{{ $caisse->libelle }}</td>
                        <td class="search-cell" style="text-align: center;">{{ number_format($caisse->compteur, 0, '', ' ') }}</td>
                        <td class="search-cell" style="text-align: center;">{{ $caisse->numero }}</td>
                        <td class="search-cell">{{ $caisse->site_libelle }}</td>
                        <td class="search-cell">{{ $caisse->agence_libelle }}</td>
                        <td class="search-cell">{{ $caisse->client_nom }}</td>
                        <td class="search-cell">{{ $caisse->station_libelle }}</td>
                        <td style="text-align: center;">
                            @if($caisse->bloque)
                                <span class="status-badge status-draft" style="background: #fee2e2; color: #991b1b;">Oui</span>
                            @else
                                <span class="status-badge status-paid">Non</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <div style="display: flex; gap: 4px; justify-content: center;">
                                <button class="btn btn-outline" style="padding: 4px 8px; height: auto;" onclick="showEditForm('{{ $caisse->caisseid }}')" title="Modifier">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                                </button>
                                <form action="{{ route('parametre.caisse.destroy', $caisse->caisseid) }}" method="POST" style="margin: 0;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette caisse ?');">
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
                    <tr id="editRow-{{ $caisse->caisseid }}" style="display: none; background: #f8fafc;">
                        <td colspan="9" style="padding: 10px;">
                            <form id="editForm-{{ $caisse->caisseid }}" action="{{ route('parametre.caisse.update', $caisse->caisseid) }}" method="POST" style="margin: 0; display: flex; width: 100%; gap: 10px; align-items: center;">
                                @csrf
                                @method('PUT')
                                <div style="flex: 1.5;"><input type="text" name="libelle" class="form-control" value="{{ $caisse->libelle }}" required></div>
                                <div style="width: 8%;"><input type="number" name="compteur" class="form-control" style="text-align: center;" value="{{ $caisse->compteur }}" required></div>
                                <div style="width: 8%;"><input type="number" name="numero" class="form-control" style="text-align: center;" value="{{ $caisse->numero }}" required></div>
                                
                                <div style="flex: 1.5;">
                                    <select name="siteid" class="form-control" required>
                                        <option value="">Sélectionnez Site</option>
                                        @foreach($sites as $s)
                                            <option value="{{ $s->siteid }}" {{ $caisse->siteid == $s->siteid ? 'selected' : '' }}>{{ $s->libelle }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div style="flex: 1.5;">
                                    <select name="agencebid" class="form-control">
                                        <option value="">Sélectionnez Agence</option>
                                        @foreach($agences as $a)
                                            <option value="{{ $a->agencebid }}" {{ $caisse->agencebid == $a->agencebid ? 'selected' : '' }}>{{ $a->libelle }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div style="flex: 1.5;">
                                    <select name="clientid" class="form-control">
                                        <option value="">Sélectionnez Client</option>
                                        @foreach($clients as $c)
                                            <option value="{{ $c->clientid }}" {{ $caisse->clientid == $c->clientid ? 'selected' : '' }}>{{ $c->nom }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div style="flex: 1;">
                                    <select name="machineid" class="form-control">
                                        <option value="">Sélectionnez Station</option>
                                        @foreach($stations as $st)
                                            <option value="{{ $st->stationid }}" {{ $caisse->machineid == $st->stationid ? 'selected' : '' }}>{{ $st->libelle }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div style="width: 6%; text-align: center;">
                                    <input type="checkbox" name="bloque" value="1" {{ $caisse->bloque ? 'checked' : '' }} style="width: 16px; height: 16px; accent-color: var(--primary);">
                                </div>
                                <div style="width: 8%; display: flex; gap: 4px; justify-content: center;">
                                    <button type="button" class="btn btn-primary" style="padding: 6px; height: auto;" onclick="document.getElementById('editForm-{{ $caisse->caisseid }}').submit()" title="Enregistrer">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    </button>
                                    <button type="button" class="btn btn-outline" style="padding: 6px; height: auto;" onclick="hideEditForm('{{ $caisse->caisseid }}')" title="Annuler">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                    </button>
                                </div>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" style="padding: 40px; text-align: center; color: var(--text-muted);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 16px; opacity: 0.5;">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="9" y1="3" x2="9" y2="21"></line>
                            </svg>
                            <div style="font-weight: 600; font-size: 15px; margin-bottom: 8px;">Aucune caisse configurée</div>
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
