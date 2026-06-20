@forelse($tickets as $ticket)
<tr class="ticket-row" data-id="{{ $ticket->cticketid }}" style="cursor: pointer;" title="Double-cliquez pour voir le ticket">
    <td>{{ isset($ticket->cticketdate) ? \Carbon\Carbon::parse($ticket->cticketdate)->format('d/m/Y') : '' }}</td>
    <td>{{ $ticket->numerointerne ?? $ticket->cticketnumero ?? '' }}</td>
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
                $bg = '#64748b'; // Default gray
                $libLower = strtolower($libelle);
                if (str_contains($libLower, 'non payé')) {
                    $bg = '#ef4444'; // Red
                } elseif (str_contains($libLower, 'payé')) {
                    $bg = '#10b981'; // Green
                } elseif (str_contains($libLower, 'acompte')) {
                    $bg = '#f59e0b'; // Amber/Orange
                } elseif (str_contains($libLower, 'retour')) {
                    $bg = '#8b5cf6'; // Purple
                } elseif (str_contains($libLower, 'annulé')) {
                    $bg = '#64748b'; // Slate/Gray
                } elseif (str_contains($libLower, 'vide')) {
                    $bg = '#0ea5e9'; // Blue
                }
            @endphp
            <span class="badge" style="background-color: {{ $bg }}; color: white; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; white-space: nowrap; display: inline-block;">
                {{ $libelle }}
            </span>
        @endif
    </td>
    <td>{{ $ticket->client_code ?? '' }}</td>
    <td><div class="truncate-text" style="max-width: 150px;" title="{{ $ticket->client_nom ?? '' }}">{{ $ticket->client_nom ?? '' }}</div></td>
    <td>{{ $ticket->vendeur_code ?? '' }}</td>
    <td>
        {{ $ticket->vendeur_nom ?? '' }}
        @if(($ticket->vendeur_prenom ?? '') && ($ticket->vendeur_prenom ?? '') !== ($ticket->vendeur_nom ?? ''))
            {{ $ticket->vendeur_prenom }}
        @endif
    </td>
    <td class="amount-cell">{{ number_format($ticket->totalqte ?? 0, 0, ',', ' ') }}</td>
    <td class="amount-cell">{{ (float)($ticket->totalbrutht ?? 0) == 0 ? '0' : number_format($ticket->totalbrutht ?? 0, 3, ',', ' ') }}</td>
    <td class="amount-cell">{{ (float)($ticket->remise ?? 0) == 0 ? '0' : number_format($ticket->remise ?? 0, 3, ',', ' ') }}</td>
    <td class="amount-cell">{{ (float)($ticket->totalnetht ?? 0) == 0 ? '0' : number_format($ticket->totalnetht ?? 0, 3, ',', ' ') }}</td>
    <td class="amount-cell">{{ (float)($ticket->totaltva ?? 0) == 0 ? '0' : number_format($ticket->totaltva ?? 0, 3, ',', ' ') }}</td>
    <td class="amount-cell">{{ (float)($ticket->totalttc ?? 0) == 0 ? '0' : number_format($ticket->totalttc ?? 0, 3, ',', ' ') }}</td>
    <td class="amount-cell">{{ (float)($ticket->totalttc ?? 0) == 0 ? '0' : number_format($ticket->totalttc ?? 0, 3, ',', ' ') }}</td>
    <td class="amount-cell">{{ (float)($ticket->acompte ?? 0) == 0 ? '0' : number_format($ticket->acompte ?? 0, 3, ',', ' ') }}</td>
    <td class="amount-cell">{{ (float)($ticket->netapayer ?? 0) == 0 ? '0' : number_format($ticket->netapayer ?? 0, 3, ',', ' ') }}</td>
</tr>
@empty
<tr>
    <td colspan="16" class="text-center" style="padding: 30px; font-weight: bold; color: var(--text);">No data to display</td>
</tr>
@endforelse
