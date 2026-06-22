@forelse($bontransferts as $bon)
<tr class="hover-row">
    <td>
        <a href="#" class="font-medium" style="color: var(--primary); text-decoration: none;">
            {{ $bon->numero }}
        </a>
    </td>
    <td>{{ \Carbon\Carbon::parse($bon->date)->format('d/m/Y') }}</td>
    <td>{{ $bon->emetteur }}</td>
    <td style="text-align: center; font-weight: 600;">{{ $bon->qte }}</td>
    <td>{{ $bon->trajet }}</td>
    <td>{{ $bon->vehicule }} {{ $bon->matricule ? '('.$bon->matricule.')' : '' }}</td>
    <td>{{ $bon->description }}</td>
    <td>
        @if(strtolower($bon->etat) === 'brouillon' || $bon->etatbontransfertid == 1)
            <span class="status-badge status-draft">{{ $bon->etat ?? 'Brouillon' }}</span>
        @elseif(strtolower($bon->etat) === 'envoyé' || strtolower($bon->etat) === 'en attente' || $bon->etatbontransfertid == 2)
            <span class="status-badge status-pending">{{ $bon->etat ?? 'En Attente' }}</span>
        @elseif(strtolower($bon->etat) === 'validé' || strtolower($bon->etat) === 'reçu' || $bon->etatbontransfertid == 3)
            <span class="status-badge status-paid">{{ $bon->etat ?? 'Reçu' }}</span>
        @else
            <span class="status-badge status-draft">{{ $bon->etat ?? 'Inconnu' }}</span>
        @endif
    </td>
    <td style="text-align: center;">
        <div class="action-dropdown" style="position: relative; display: inline-block;">
            <button type="button" class="btn btn-outline" style="padding: 4px 8px; height: auto;" onclick="this.nextElementSibling.classList.toggle('show')">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="1"></circle>
                    <circle cx="12" cy="5" r="1"></circle>
                    <circle cx="12" cy="19" r="1"></circle>
                </svg>
            </button>
            <div class="dropdown-menu" style="display: none; position: absolute; right: 0; top: 100%; background: white; border: 1px solid var(--border); border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); z-index: 10; min-width: 120px;">
                <a href="{{ route('transfert.recu.receptionner', $bon->bontransfertid) }}" style="display: block; padding: 8px 12px; text-decoration: none; color: var(--text-main); font-size: 13px; text-align: left;">Réceptionner</a>
                <a href="#" style="display: block; padding: 8px 12px; text-decoration: none; color: var(--primary); font-size: 13px; text-align: left;">Imprimer</a>
            </div>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="9" style="padding: 60px; text-align: center; color: var(--text-muted);">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 16px; opacity: 0.5;">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
            <line x1="16" y1="2" x2="16" y2="6"></line>
            <line x1="8" y1="2" x2="8" y2="6"></line>
            <line x1="3" y1="10" x2="21" y2="10"></line>
        </svg>
        <div style="font-weight: 600; font-size: 15px; margin-bottom: 8px;">Aucun bon de transfert reçu trouvé</div>
        <div style="font-size: 13px;">Essayez de modifier vos filtres de recherche.</div>
    </td>
</tr>
@endforelse
