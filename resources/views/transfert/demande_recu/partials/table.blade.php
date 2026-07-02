@forelse($demandes as $dem)
<tr class="hover-row">
    <td style="text-align: center;">
        <label class="checkbox-wrapper">
            <input type="checkbox" name="selected_demandes[]" value="{{ $dem->detdemandetransfertid }}">
            <span class="checkmark"></span>
        </label>
    </td>
    <td>{{ $dem->demandetransfertnumero }}</td>
    <td>{{ \Carbon\Carbon::parse($dem->demandetransfertdate)->format('d/m/Y') }}</td>
    <td>{{ $dem->demandeur }}</td>
    <td>{{ $dem->reference }}</td>
    <td>{{ $dem->couleur }}</td>
    <td>{{ $dem->taille }}</td>
    <td style="text-align: center; font-weight: 600;">{{ (int) $dem->qte_demandee }}</td>
    <td style="text-align: center; color: var(--text-muted);">{{ $dem->stock }}</td>
    <td>
        @if(strtolower($dem->etat) === 'brouillon')
            <span class="status-badge status-draft">{{ $dem->etat }}</span>
        @elseif(strtolower($dem->etat) === 'envoyé' || strtolower($dem->etat) === 'en attente')
            <span class="status-badge status-pending">{{ $dem->etat }}</span>
        @elseif(strtolower($dem->etat) === 'validé' || strtolower($dem->etat) === 'pointé')
            <span class="status-badge status-paid">{{ $dem->etat }}</span>
        @else
            <span class="status-badge status-draft">{{ $dem->etat }}</span>
        @endif
    </td>
    <!-- Pointage Fields -->
    <td style="padding: 4px;">
        <input type="text" name="pointage[{{ $dem->detdemandetransfertid }}][cause]" class="form-control" style="font-size: 12px; padding: 4px 8px; width: 100%; box-sizing: border-box;" value="{{ $dem->cause }}" placeholder="Observation">
    </td>
    <td style="padding: 4px; text-align: center;">
        <input type="number" name="pointage[{{ $dem->detdemandetransfertid }}][qte_validee]" class="form-control" style="font-size: 12px; padding: 4px 8px; width: 80px; text-align: center; margin: 0 auto;" value="{{ $dem->qte_validee ?? 0 }}" min="0" max="{{ $dem->stock }}">
    </td>
</tr>
@empty
<tr>
    <td colspan="12" style="padding: 60px; text-align: center; color: var(--text-muted);">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 16px; opacity: 0.5;">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
            <line x1="16" y1="2" x2="16" y2="6"></line>
            <line x1="8" y1="2" x2="8" y2="6"></line>
            <line x1="3" y1="10" x2="21" y2="10"></line>
        </svg>
        <div style="font-weight: 600; font-size: 15px; margin-bottom: 8px;">Aucune demande à pointer</div>
        <div style="font-size: 13px;">Essayez de modifier vos filtres de recherche.</div>
    </td>
</tr>
@endforelse
