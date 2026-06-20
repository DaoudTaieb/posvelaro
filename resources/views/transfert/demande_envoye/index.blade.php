@extends('layouts.app')

@section('title', 'Consultation Demandes des Transferts Envoyée Velaro')

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
        background: white;
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

    .text-input, .date-input, .select-input {
        padding: 6px 10px;
        border: 1px solid var(--border);
        border-radius: 4px;
        font-size: 13px;
        outline: none;
    }

    .date-input {
        width: 130px;
    }

    .select-input {
        min-width: 150px;
    }
    
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
        font-weight: 500;
        font-size: 13px;
    }
    
    .btn-action:hover {
        background: #f8fafc;
    }

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
        border-bottom: 1px solid var(--border);
        overflow-x: auto;
    }

    .badge {
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        color: white;
    }
</style>
@endsection

@section('content')
<div class="main-content-inner full-width" style="padding: 0;">
    
    <!-- En-tête de page -->
    <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 20px; background: white; border-bottom: 1px solid var(--border);">
        <h1 style="font-size: 16px; font-weight: 700; color: var(--text); margin: 0;">Consultation Demandes des Transferts Envoyée Velaro</h1>
        <div style="display: flex; gap: 5px;">
            <a href="{{ route('transfert.demande_envoye.create') }}" class="btn-action" style="padding: 6px 10px; text-decoration: none;" title="Nouveau">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
            </a>
            <button class="btn-action" style="padding: 6px 10px;" title="Fermer">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
    </div>

    <!-- Barre de filtres principale -->
    <form method="GET" action="{{ route('transfert.demande_envoye.index') }}" id="filterForm">
        <div style="padding: 10px 20px; font-size: 13px; color: var(--text-secondary); background: #f8fafc; border-bottom: 1px solid var(--border);">
            Filtre
        </div>
        <div class="filter-bar">
            <div class="filter-group">
                <label>Période</label>
                <input type="date" name="datedebut" class="date-input" value="{{ $defaultDateDebut }}">
                <input type="date" name="datefin" class="date-input" value="{{ $defaultDateFin }}">
            </div>
            
            <div class="filter-group">
                <label>Récepteur</label>
                <select name="siterecepteurid" class="select-input" style="width: 200px;">
                    <option value="">Select Récepteur...</option>
                    @foreach($sites as $site)
                        <option value="{{ $site->siteid }}" {{ request('siterecepteurid') == $site->siteid ? 'selected' : '' }}>
                            {{ $site->libelle }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
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
            
            <div style="margin-left: auto; width: 250px;">
                <div style="position: relative;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 10px; top: 10px;">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" class="text-input" style="width: 100%; padding-left: 30px;" placeholder="Enter text to search...">
                </div>
            </div>
        </div>
    </form>

    <div class="table-container">
        <table id="dataTable" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 12px;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 1px solid var(--border);">
                    <th style="padding: 8px; font-weight: 600; color: var(--text-secondary); border-right: 1px solid var(--border); width: 60px;">
                        <div style="text-align: center; margin-top: 18px;">
                            <a href="#" style="color: #0284c7; text-decoration: none;">Clear</a>
                        </div>
                    </th>
                    <th style="padding: 8px; font-weight: 600; color: var(--text-secondary); border-right: 1px solid var(--border); width: 60px;">
                        <div style="text-align: center; margin-top: 18px;">
                            <a href="#" style="color: #0284c7; text-decoration: none;">Clear</a>
                        </div>
                    </th>
                    <th style="padding: 8px; font-weight: 600; color: var(--text-secondary); border-right: 1px solid var(--border); width: 12%;">
                        Émetteur <input type="text" class="search-input" onkeyup="filterTable(2, this.value)">
                    </th>
                    <th style="padding: 8px; font-weight: 600; color: var(--text-secondary); border-right: 1px solid var(--border); width: 12%;">
                        Récepteur <input type="text" class="search-input" onkeyup="filterTable(3, this.value)">
                    </th>
                    <th style="padding: 8px; font-weight: 600; color: var(--text-secondary); border-right: 1px solid var(--border); width: 10%;">
                        Numéro <input type="text" class="search-input" onkeyup="filterTable(4, this.value)">
                    </th>
                    <th style="padding: 8px; font-weight: 600; color: var(--text-secondary); border-right: 1px solid var(--border); width: 10%;">
                        Date <input type="date" class="search-input" onchange="filterTable(5, this.value)">
                    </th>
                    <th style="padding: 8px; font-weight: 600; color: var(--text-secondary); border-right: 1px solid var(--border); width: 10%;">
                        Etat <input type="text" class="search-input" onkeyup="filterTable(6, this.value)">
                    </th>
                    <th style="padding: 8px; font-weight: 600; color: var(--text-secondary); border-right: 1px solid var(--border); width: 10%;">
                        Trajet <input type="text" class="search-input" onkeyup="filterTable(7, this.value)">
                    </th>
                    <th style="padding: 8px; font-weight: 600; color: var(--text-secondary); border-right: 1px solid var(--border); width: 10%;">
                        Vehicule <input type="text" class="search-input" onkeyup="filterTable(8, this.value)">
                    </th>
                    <th style="padding: 8px; font-weight: 600; color: var(--text-secondary); border-right: 1px solid var(--border); width: 10%;">
                        Matricule <input type="text" class="search-input" onkeyup="filterTable(9, this.value)">
                    </th>
                    <th style="padding: 8px; font-weight: 600; color: var(--text-secondary);">
                        Description <input type="text" class="search-input" onkeyup="filterTable(10, this.value)">
                    </th>
                </tr>
            </thead>
            <tbody id="tableBody">
                @forelse($demandes as $demande)
                <tr style="border-bottom: 1px solid var(--border); transition: background 0.2s;" class="data-row" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                    <td style="padding: 6px 8px; border-right: 1px solid var(--border); text-align: center;">
                        <a href="{{ route('transfert.demande_envoye.edit', $demande->demandetransfertid) }}" style="color: var(--text-muted);"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg></a>
                    </td>
                    <td style="padding: 6px 8px; border-right: 1px solid var(--border); text-align: center;">
                        <form action="{{ route('transfert.demande_envoye.destroy', $demande->demandetransfertid) }}" method="POST" style="display:inline;" onsubmit="return confirm('Voulez-vous vraiment supprimer cette demande ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background:none; border:none; padding:0; cursor:pointer; color: var(--text-muted);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                            </button>
                        </form>
                    </td>
                    <td style="padding: 6px 8px; color: var(--text); border-right: 1px solid var(--border);">{{ $demande->site }}</td>
                    <td style="padding: 6px 8px; color: var(--text); border-right: 1px solid var(--border);">{{ $demande->siterecepteur }}</td>
                    <td style="padding: 6px 8px; color: var(--text); border-right: 1px solid var(--border);">{{ $demande->demandetransfertnumero }}</td>
                    <td style="padding: 6px 8px; color: var(--text); border-right: 1px solid var(--border);">{{ \Carbon\Carbon::parse($demande->demandetransfertdate)->format('d/m/Y') }}</td>
                    <td style="padding: 6px 8px; border-right: 1px solid var(--border);">
                        @if($demande->etatlibelle)
                            <span class="badge" style="background-color: {{ $demande->etatcouleur ?? '#94a3b8' }};">
                                {{ $demande->etatlibelle }}
                            </span>
                        @endif
                    </td>
                    <td style="padding: 6px 8px; color: var(--text); border-right: 1px solid var(--border);">{{ $demande->trajet }}</td>
                    <td style="padding: 6px 8px; color: var(--text); border-right: 1px solid var(--border);">{{ $demande->vehicule }}</td>
                    <td style="padding: 6px 8px; color: var(--text); border-right: 1px solid var(--border);">{{ $demande->matricule }}</td>
                    <td style="padding: 6px 8px; color: var(--text);">{{ $demande->description }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" style="padding: 50px; text-align: center; color: var(--text-muted); font-size: 14px; font-weight: 600;">
                        No data to display
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div style="padding: 10px 15px; background: #f8fafc; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border);">
        <div>
            {{ $demandes->appends(request()->query())->links('pagination::bootstrap-4') }}
        </div>
        <div style="display: flex; align-items: center; gap: 10px; font-size: 12px; color: var(--text-secondary);">
            Page Size: 
            <select style="padding: 4px; border: 1px solid var(--border); border-radius: 4px;">
                <option>20</option>
                <option>50</option>
                <option>100</option>
            </select>
        </div>
    </div>
</div>

<script>
function filterTable(colIndex, value) {
    const table = document.getElementById("dataTable");
    const rows = table.getElementsByClassName("data-row");
    const filter = value.toUpperCase();

    for (let i = 0; i < rows.length; i++) {
        const td = rows[i].getElementsByTagName("td")[colIndex];
        if (td) {
            const textValue = td.textContent || td.innerText;
            if (textValue.toUpperCase().indexOf(filter) > -1) {
                rows[i].style.display = "";
            } else {
                rows[i].style.display = "none";
            }
        }
    }
}
</script>
@endsection
