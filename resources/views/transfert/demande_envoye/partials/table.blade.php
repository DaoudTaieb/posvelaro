@forelse($demandes as $demande)
<tr style="border-bottom: 1px solid var(--border); transition: background 0.2s;" class="data-row" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
    <td style="padding: 6px 8px; border-right: 1px solid var(--border); text-align: center;">
        <a href="{{ route('transfert.demande_envoye.edit', $demande->demandetransfertid) }}" style="color: var(--text-muted);" title="Modifier">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
        </a>
    </td>
    <td style="padding: 6px 8px; border-right: 1px solid var(--border); text-align: center;">
        <form action="{{ route('transfert.demande_envoye.destroy', $demande->demandetransfertid) }}" method="POST" style="display:inline;" onsubmit="return confirm('Voulez-vous vraiment supprimer cette demande ?');">
            @csrf
            @method('DELETE')
            <button type="submit" style="background:none; border:none; padding:0; cursor:pointer; color: var(--text-muted);" title="Supprimer">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
            </button>
        </form>
    </td>
    <td style="padding: 6px 8px; color: var(--text-main); border-right: 1px solid var(--border);">{{ $demande->site }}</td>
    <td style="padding: 6px 8px; color: var(--text-main); border-right: 1px solid var(--border);">{{ $demande->siterecepteur }}</td>
    <td style="padding: 6px 8px; color: var(--text-main); border-right: 1px solid var(--border);">{{ $demande->demandetransfertnumero }}</td>
    <td style="padding: 6px 8px; color: var(--text-main); border-right: 1px solid var(--border);">{{ \Carbon\Carbon::parse($demande->demandetransfertdate)->format('d/m/Y') }}</td>
    <td style="padding: 6px 8px; border-right: 1px solid var(--border);">
        @if($demande->etatlibelle)
            <span class="badge" style="background-color: {{ $demande->etatcouleur ?? '#94a3b8' }}; padding: 3px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; color: white;">
                {{ $demande->etatlibelle }}
            </span>
        @endif
    </td>
    <td style="padding: 6px 8px; color: var(--text-main); border-right: 1px solid var(--border);">{{ $demande->trajet }}</td>
    <td style="padding: 6px 8px; color: var(--text-main); border-right: 1px solid var(--border);">{{ $demande->vehicule }}</td>
    <td style="padding: 6px 8px; color: var(--text-main); border-right: 1px solid var(--border);">{{ $demande->matricule }}</td>
    <td style="padding: 6px 8px; color: var(--text-main);">{{ $demande->description }}</td>
</tr>
@empty
<tr>
    <td colspan="11" style="padding: 50px; text-align: center; color: var(--text-muted); font-size: 14px; font-weight: 600;">
        <div style="display: flex; flex-direction: column; align-items: center; gap: 10px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" style="color: #cbd5e1;">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            Aucune demande trouvée pour ces critères
        </div>
    </td>
</tr>
@endforelse
