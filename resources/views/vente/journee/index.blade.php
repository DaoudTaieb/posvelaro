@extends('layouts.app')
@section('title', 'Consultation Journées')

@section('content')
<div class="main-content-inner" style="max-width: 100%; margin: 20px auto; padding: 0 20px;">
    
    <div style="background: white; border: 1px solid var(--border); border-radius: 12px; box-shadow: var(--shadow-sm); overflow: hidden;">
        
        {{-- En-tête et Filtres --}}
        <div style="padding: 16px 24px; border-bottom: 1px solid var(--border); background: #f8fafc;">
            <div style="font-size: 14px; font-weight: 600; color: var(--text); margin-bottom: 16px;">Filtre</div>
            <form method="GET" action="{{ route('vente.journee.index') }}" style="display: flex; gap: 24px; align-items: center; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <label style="font-size: 12px; font-weight: 600; color: var(--text-secondary); text-transform: uppercase;">DU</label>
                    <input type="date" name="date_du" value="{{ $dateDu }}" style="border: 1px solid var(--border); border-radius: 6px; padding: 8px 12px; outline: none; font-family: inherit; font-size: 14px; color: var(--text);">
                </div>
                
                <div style="display: flex; align-items: center; gap: 12px;">
                    <label style="font-size: 12px; font-weight: 600; color: var(--text-secondary); text-transform: uppercase;">AU</label>
                    <input type="date" name="date_au" value="{{ $dateAu }}" style="border: 1px solid var(--border); border-radius: 6px; padding: 8px 12px; outline: none; font-family: inherit; font-size: 14px; color: var(--text);">
                </div>

                <button type="submit" style="background: white; border: 1px solid var(--border); border-radius: 6px; padding: 8px 24px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='white'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                    </svg>
                </button>
            </form>
        </div>

        {{-- Tableau --}}
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 12px; white-space: nowrap;">
                <thead>
                    <tr style="background: #f1f5f9; border-bottom: 2px solid var(--border);">
                        <th style="text-align: center; padding: 12px 8px; font-weight: 600; color: var(--text); border-right: 1px solid var(--border); width: 40px;"></th>
                        <th style="text-align: left; padding: 12px; font-weight: 600; color: var(--text); border-right: 1px solid var(--border);">ID</th>
                        <th style="text-align: left; padding: 12px; font-weight: 600; color: var(--text); border-right: 1px solid var(--border);">AGENCE</th>
                        <th style="text-align: left; padding: 12px; font-weight: 600; color: var(--text); border-right: 1px solid var(--border);">CAISSIER</th>
                        <th style="text-align: center; padding: 12px; font-weight: 600; color: var(--text); border-right: 1px solid var(--border);">DATE OUV.</th>
                        <th style="text-align: center; padding: 12px; font-weight: 600; color: var(--text); border-right: 1px solid var(--border);">CLÔTURE</th>
                        <th style="text-align: right; padding: 12px; font-weight: 600; color: var(--text); border-right: 1px solid var(--border);">Fond Caisse</th>
                        <th style="text-align: right; padding: 12px; font-weight: 600; color: var(--text); border-right: 1px solid var(--border);">TOTAL VENTES</th>
                        <th style="text-align: right; padding: 12px; font-weight: 600; color: var(--text); border-right: 1px solid var(--border);">RecetteBrut</th>
                        <th style="text-align: right; padding: 12px; font-weight: 600; color: var(--text); border-right: 1px solid var(--border);">RecetteNet</th>
                        <th style="text-align: right; padding: 12px; font-weight: 600; color: var(--text); border-right: 1px solid var(--border);">Recette Phys</th>
                        <th style="text-align: right; padding: 12px; font-weight: 600; color: var(--text); border-right: 1px solid var(--border);">ESPECE BRUT</th>
                        <th style="text-align: right; padding: 12px; font-weight: 600; color: var(--text); border-right: 1px solid var(--border);">Tot Dépenses</th>
                        <th style="text-align: right; padding: 12px; font-weight: 600; color: var(--text); border-right: 1px solid var(--border);">ESPECES NET</th>
                        <th style="text-align: right; padding: 12px; font-weight: 600; color: var(--text); border-right: 1px solid var(--border);">TPE</th>
                        <th style="text-align: right; padding: 12px; font-weight: 600; color: var(--text); border-right: 1px solid var(--border);">CHEQUE</th>
                        <th style="text-align: right; padding: 12px; font-weight: 600; color: var(--text); border-right: 1px solid var(--border);">BON CONVENTION</th>
                        <th style="text-align: right; padding: 12px; font-weight: 600; color: var(--text); border-right: 1px solid var(--border);">BON ACHAT</th>
                        <th style="text-align: right; padding: 12px; font-weight: 600; color: var(--text); border-right: 1px solid var(--border);">CHEQUES CADEAUX</th>
                        <th style="text-align: right; padding: 12px; font-weight: 600; color: var(--text); border-right: 1px solid var(--border);">AUTRES</th>
                        <th style="text-align: right; padding: 12px; font-weight: 600; color: var(--text); border-right: 1px solid var(--border);">CREDIT</th>
                        <th style="text-align: right; padding: 12px; font-weight: 600; color: var(--text); border-right: 1px solid var(--border);">ACOMPTE PERSONNEL</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($journees as $j)
                    <tr style="border-bottom: 1px solid var(--border); transition: background 0.2s; cursor: pointer;" 
                        onmouseover="this.style.background='#f8fafc'" 
                        onmouseout="this.style.background='transparent'"
                        ondblclick="openTicketModal('{{ route('vente.journee.show', $j->journalcaisseid) }}')">
                        <td style="padding: 6px; text-align: center; border-right: 1px solid var(--border);">
                            <button onclick="openTicketModal('{{ route('vente.journee.details', $j->journalcaisseid) }}')" title="Voir les détails de la journée" style="background: white; border: 1px solid #cbd5e1; border-radius: 4px; padding: 4px 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 1px 2px rgba(0,0,0,0.05); color: #0284c7; transition: all 0.2s;" onmouseover="this.style.background='#f0f9ff'; this.style.borderColor='#bae6fd'" onmouseout="this.style.background='white'; this.style.borderColor='#cbd5e1'">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <circle cx="11.5" cy="14.5" r="2.5"></circle>
                                    <line x1="13.27" y1="16.27" x2="16" y2="19"></line>
                                </svg>
                            </button>
                        </td>
                        <td style="padding: 10px 12px; color: var(--text); border-right: 1px solid var(--border);">
                            <span style="background: #6b21a8; color: white; padding: 2px 6px; border-radius: 4px; font-weight: 600; font-size: 11px;">{{ $j->journalcaisseid }}</span>
                        </td>
                        <td style="padding: 10px 12px; color: var(--text); border-right: 1px solid var(--border);">{{ $j->agence_nom }}</td>
                        <td style="padding: 10px 12px; color: var(--text); border-right: 1px solid var(--border);">{{ $j->caissier_nom }}</td>
                        <td style="padding: 10px 12px; text-align: center; color: var(--text); border-right: 1px solid var(--border);">
                            {{ $j->dateouverture ? \Carbon\Carbon::parse($j->dateouverture)->format('d/m/Y H:i') : '' }}
                        </td>
                        <td style="padding: 10px 12px; text-align: center; color: var(--text); border-right: 1px solid var(--border);">
                            {{ $j->datecloture ? \Carbon\Carbon::parse($j->datecloture)->format('d/m/Y H:i') : '' }}
                        </td>
                        <td style="padding: 10px 12px; text-align: right; font-family: 'JetBrains Mono', monospace; color: var(--text); border-right: 1px solid var(--border);">{{ number_format((float)$j->fondcaisse, 3, '.', ' ') }}</td>
                        <td style="padding: 10px 12px; text-align: right; font-family: 'JetBrains Mono', monospace; color: var(--text); border-right: 1px solid var(--border);">{{ number_format((float)($j->ventereglee ?? 0), 3, '.', ' ') }}</td>
                        <td style="padding: 10px 12px; text-align: right; font-family: 'JetBrains Mono', monospace; color: var(--text); border-right: 1px solid var(--border);">{{ number_format((float)($j->recettebrut ?? 0), 3, '.', ' ') }}</td>
                        <td style="padding: 10px 12px; text-align: right; font-family: 'JetBrains Mono', monospace; color: var(--text); border-right: 1px solid var(--border);">{{ number_format((float)($j->recettenet ?? 0), 3, '.', ' ') }}</td>
                        <td style="padding: 10px 12px; text-align: right; font-family: 'JetBrains Mono', monospace; font-weight: 600; color: #0369a1; border-right: 1px solid var(--border);">{{ number_format((float)($j->recettephysique ?? 0), 3, '.', ' ') }}</td>
                        <td style="padding: 10px 12px; text-align: right; font-family: 'JetBrains Mono', monospace; color: var(--text); border-right: 1px solid var(--border);">{{ number_format((float)($j->totalespece ?? 0), 3, '.', ' ') }}</td>
                        <td style="padding: 10px 12px; text-align: right; font-family: 'JetBrains Mono', monospace; color: #dc2626; border-right: 1px solid var(--border);">{{ number_format((float)($j->montantdepense ?? 0), 3, '.', ' ') }}</td>
                        <td style="padding: 10px 12px; text-align: right; font-family: 'JetBrains Mono', monospace; color: var(--text); border-right: 1px solid var(--border);">{{ number_format((float)($j->totalespecenet ?? 0), 3, '.', ' ') }}</td>
                        <td style="padding: 10px 12px; text-align: right; font-family: 'JetBrains Mono', monospace; color: var(--text); border-right: 1px solid var(--border);">{{ number_format((float)($j->totaltpe ?? 0), 3, '.', ' ') }}</td>
                        <td style="padding: 10px 12px; text-align: right; font-family: 'JetBrains Mono', monospace; color: var(--text); border-right: 1px solid var(--border);">{{ number_format((float)($j->totalcheque ?? 0), 3, '.', ' ') }}</td>
                        <td style="padding: 10px 12px; text-align: right; font-family: 'JetBrains Mono', monospace; color: var(--text); border-right: 1px solid var(--border);">{{ number_format((float)($j->totalbonconvention ?? 0), 3, '.', ' ') }}</td>
                        <td style="padding: 10px 12px; text-align: right; font-family: 'JetBrains Mono', monospace; color: var(--text); border-right: 1px solid var(--border);">{{ number_format((float)($j->totalcontrebon ?? 0), 3, '.', ' ') }}</td>
                        <td style="padding: 10px 12px; text-align: right; font-family: 'JetBrains Mono', monospace; color: var(--text); border-right: 1px solid var(--border);">{{ number_format(0, 3, '.', ' ') }}</td>
                        <td style="padding: 10px 12px; text-align: right; font-family: 'JetBrains Mono', monospace; color: var(--text); border-right: 1px solid var(--border);">{{ number_format((float)($j->totalregautre ?? 0), 3, '.', ' ') }}</td>
                        <td style="padding: 10px 12px; text-align: right; font-family: 'JetBrains Mono', monospace; color: var(--text); border-right: 1px solid var(--border);">{{ number_format((float)($j->totalcredit ?? 0), 3, '.', ' ') }}</td>
                        <td style="padding: 10px 12px; text-align: right; font-family: 'JetBrains Mono', monospace; color: var(--text); border-right: 1px solid var(--border);">{{ number_format((float)($j->acomptepersonnel ?? 0), 3, '.', ' ') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="21" style="padding: 24px; text-align: center; color: var(--text-secondary); font-weight: 500;">
                            No data to display
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal pour le Ticket --}}
<div id="ticketModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center; padding: 20px;">
    <div style="background: transparent; position: relative; width: 100%; max-width: 450px; height: 90vh; border-radius: 8px; display: flex; flex-direction: column;">
        <button onclick="closeTicketModal()" style="position: absolute; right: -40px; top: 0; background: white; color: black; border: none; width: 30px; height: 30px; border-radius: 50%; cursor: pointer; font-weight: bold; font-size: 16px; display: flex; justify-content: center; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">X</button>
        <iframe id="ticketIframe" src="" style="width: 100%; height: 100%; border: none; background: transparent; border-radius: 8px;"></iframe>
    </div>
</div>

<script>
    function openTicketModal(url) {
        document.getElementById('ticketIframe').src = url;
        document.getElementById('ticketModal').style.display = 'flex';
        // Empêcher le défilement du body
        document.body.style.overflow = 'hidden';
    }

    function closeTicketModal() {
        document.getElementById('ticketModal').style.display = 'none';
        document.getElementById('ticketIframe').src = '';
        // Réactiver le défilement du body
        document.body.style.overflow = '';
    }

    // Fermer le modal si on clique à l'extérieur
    document.getElementById('ticketModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeTicketModal();
        }
    });
</script>
@endsection
