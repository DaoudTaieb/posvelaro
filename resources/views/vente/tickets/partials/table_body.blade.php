@forelse($tickets as $ticket)
<tr class="ticket-row" data-id="{{ $ticket->cticketid }}">
    <td class="text-muted">{{ isset($ticket->cticketdate) ? \Carbon\Carbon::parse($ticket->cticketdate)->format('d/m/Y') : '' }}</td>
    <td class="font-medium">{{ $ticket->numerointerne ?? $ticket->cticketnumero ?? '' }}</td>
    <td>
        @php
            $libelle = $ticket->statut_libelle;
            if (empty($libelle)) {
                if ($ticket->netapayer <= 0 && $ticket->totalttc > 0) {
                    $libelle = 'Payé';
                } elseif ($ticket->acompte > 0) {
                    $libelle = 'Acompte';
                } else {
                    $libelle = 'Non Payé';
                }
            }
        @endphp
        @if($libelle)
            @php
                $badgeClass = 'badge-default';
                $libLower = strtolower($libelle);
                if (str_contains($libLower, 'non payé')) {
                    $badgeClass = 'badge-danger';
                } elseif (str_contains($libLower, 'payé')) {
                    $badgeClass = 'badge-success';
                } elseif (str_contains($libLower, 'acompte')) {
                    $badgeClass = 'badge-warning';
                } elseif (str_contains($libLower, 'retour')) {
                    $badgeClass = 'badge-purple';
                } elseif (str_contains($libLower, 'annulé')) {
                    $badgeClass = 'badge-default';
                } elseif (str_contains($libLower, 'vide')) {
                    $badgeClass = 'badge-info';
                }
            @endphp
            <span class="modern-badge {{ $badgeClass }}">
                <span class="badge-dot"></span>
                {{ $libelle }}
            </span>
        @endif
    </td>
    <td class="text-muted">{{ $ticket->client_code ?? '' }}</td>
    <td>
        <div class="truncate-text font-medium" style="max-width: 150px;" title="{{ $ticket->client_nom ?? '' }}">
            {{ $ticket->client_nom ?? '' }}
        </div>
    </td>
    <td class="text-muted">{{ $ticket->vendeur_code ?? '' }}</td>
    <td>
        <div class="truncate-text" style="max-width: 120px;" title="{{ $ticket->vendeur_nom ?? '' }} {{ $ticket->vendeur_prenom ?? '' }}">
            {{ $ticket->vendeur_nom ?? '' }}
            @if(($ticket->vendeur_prenom ?? '') && ($ticket->vendeur_prenom ?? '') !== ($ticket->vendeur_nom ?? ''))
                {{ $ticket->vendeur_prenom }}
            @endif
        </div>
    </td>
    <td class="amount-cell">{{ number_format($ticket->totalqte ?? 0, 0, ',', ' ') }}</td>
    <td class="amount-cell text-muted">{{ (float)($ticket->totalbrutht ?? 0) == 0 ? '0' : number_format($ticket->totalbrutht ?? 0, 3, ',', ' ') }}</td>
    <td class="amount-cell text-muted">{{ (float)($ticket->remise ?? 0) == 0 ? '0' : number_format($ticket->remise ?? 0, 3, ',', ' ') }}</td>
    <td class="amount-cell text-muted">{{ (float)($ticket->totalnetht ?? 0) == 0 ? '0' : number_format($ticket->totalnetht ?? 0, 3, ',', ' ') }}</td>
    <td class="amount-cell text-muted">{{ (float)($ticket->totaltva ?? 0) == 0 ? '0' : number_format($ticket->totaltva ?? 0, 3, ',', ' ') }}</td>
    <td class="amount-cell font-bold text-primary">{{ (float)($ticket->totalttc ?? 0) == 0 ? '0' : number_format($ticket->totalttc ?? 0, 3, ',', ' ') }}</td>
    <td class="amount-cell text-muted">{{ (float)($ticket->totalttc ?? 0) == 0 ? '0' : number_format($ticket->totalttc ?? 0, 3, ',', ' ') }}</td>
    <td class="amount-cell text-warning">{{ (float)($ticket->acompte ?? 0) == 0 ? '0' : number_format($ticket->acompte ?? 0, 3, ',', ' ') }}</td>
    <td class="amount-cell text-danger">{{ (float)($ticket->netapayer ?? 0) == 0 ? '0' : number_format($ticket->netapayer ?? 0, 3, ',', ' ') }}</td>
</tr>
@empty
<tr>
    <td colspan="16" class="empty-state">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
            <line x1="9" y1="9" x2="15" y2="15"></line>
            <line x1="15" y1="9" x2="9" y2="15"></line>
        </svg>
        <p>Aucun ticket trouvé</p>
        <span class="text-muted">Ajustez vos filtres pour voir plus de résultats.</span>
    </td>
</tr>
@endforelse
