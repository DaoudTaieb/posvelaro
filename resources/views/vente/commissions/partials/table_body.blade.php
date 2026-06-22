@forelse($commissions as $comm)
<tr class="ticket-row">
    <td>{{ isset($comm->cticketdate) ? \Carbon\Carbon::parse($comm->cticketdate)->format('d/m/Y') : '' }}</td>
    <td>{{ $comm->numerointerne ?? $comm->cticketnumero ?? '' }}</td>
    <td>{{ $comm->vendeur_nom ?? '' }} {{ $comm->vendeur_prenom ?? '' }}</td>
    <td class="amount-cell">{{ (float)($comm->totalnetht ?? 0) == 0 ? '0' : number_format($comm->totalnetht ?? 0, 3, ',', ' ') }}</td>
    <td class="amount-cell">{{ number_format($comm->tauxcommission ?? 0, 2, ',', ' ') }} %</td>
    <td class="amount-cell" style="color: var(--success); font-weight: 600;">{{ (float)($comm->montant_commission ?? 0) == 0 ? '0' : number_format($comm->montant_commission ?? 0, 3, ',', ' ') }}</td>
</tr>
@empty
<tr>
    <td colspan="6" class="empty-state">
        <div style="display: flex; flex-direction: column; align-items: center; gap: 12px; padding: 32px;">
            <div style="padding: 16px; background: var(--background); border-radius: 50%;">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </div>
            <div>
                <p style="margin: 0; font-weight: 600; color: #475569; font-size: 14px;">Aucune commission trouvée</p>
                <p style="margin: 4px 0 0; font-size: 13px; color: var(--text-muted);">Veuillez utiliser les filtres (Date ou Vendeur) pour afficher les commissions.</p>
            </div>
        </div>
    </td>
</tr>
@endforelse
