@forelse($clients as $client)
<tr class="client-row">
    <td class="text-center">
        <button class="btn-icon btn-edit" title="Modifier" 
            data-id="{{ $client->clientid ?? '' }}"
            data-nom="{{ $client->nom ?? '' }}"
            data-mf="{{ $client->mf ?? '' }}"
            data-tel="{{ $client->tel ?? '' }}"
            data-email="{{ $client->email ?? '' }}"
            data-ville="{{ $client->ville ?? '' }}"
            data-adresse="{{ $client->adressefacturation ?? '' }}"
            data-credit="{{ isset($client->credit) && !$client->credit ? '1' : '0' }}"
            data-fidelite="{{ isset($client->fidelite) && $client->fidelite ? '1' : '0' }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
        </button>
        <button class="btn-icon btn-delete" title="Supprimer" data-id="{{ $client->clientid ?? '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--danger)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="3 6 5 6 21 6"></polyline>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
            </svg>
        </button>
    </td>
    <td>{{ $client->clientcode ?? $client->code ?? '' }}</td>
    <td><div class="truncate-text" style="max-width: 150px;" title="{{ $client->nom ?? '' }}">{{ $client->nom ?? '' }}</div></td>
    <td>{{ $client->tarif ?? '' }}</td>
    <td>
        @if(isset($client->credit) && !$client->credit)
            <span class="badge badge-danger">Bloqué</span>
        @else
            <span class="badge badge-success">Actif</span>
        @endif
    </td>
    <td>{{ (float)($client->remise ?? 0) }} %</td>
    <td>
        @if(isset($client->fidelite) && $client->fidelite)
            <span class="badge badge-success">Oui</span>
        @else
            <span class="badge badge-secondary">Non</span>
        @endif
    </td>
    <td>{{ $client->mf ?? '' }}</td>
    <td class="amount-cell">{{ (float)($client->solde ?? 0) == 0 ? '0' : number_format($client->solde, 3, ',', ' ') }}</td>
    <td class="amount-cell">{{ (float)($client->soldeinitial ?? 0) == 0 ? '0' : number_format($client->soldeinitial, 3, ',', ' ') }}</td>
    <td>{{ isset($client->datesoldeinitial) ? \Carbon\Carbon::parse($client->datesoldeinitial)->format('d/m/Y') : '' }}</td>
    <td>{{ $client->ville ?? '' }}</td>
    <td><div class="truncate-text" style="max-width: 150px;" title="{{ $client->adressefacturation ?? '' }}">{{ $client->adressefacturation ?? '' }}</div></td>
    <td><div class="truncate-text" style="max-width: 150px;" title="{{ $client->adresselivraison ?? '' }}">{{ $client->adresselivraison ?? '' }}</div></td>
    <td>{{ $client->tel ?? '' }}</td>
    <td>{{ $client->rc ?? '' }}</td>
    <td>{{ $client->fax ?? '' }}</td>
    <td>{{ $client->email ?? '' }}</td>
    <td>{{ $client->clientid ?? '' }}</td>
    <td class="amount-cell">{{ (float)($client->soldefidelite ?? 0) == 0 ? '0' : number_format($client->soldefidelite, 3, ',', ' ') }}</td>
    <td class="amount-cell">{{ (float)($client->cumulfidelite ?? 0) == 0 ? '0' : number_format($client->cumulfidelite, 3, ',', ' ') }}</td>
    <td class="amount-cell">{{ (float)($client->pointfidelite ?? 0) == 0 ? '0' : number_format($client->pointfidelite, 3, ',', ' ') }}</td>
</tr>
@empty
<tr>
    <td colspan="22" class="text-center" style="padding: 30px; color: var(--text-muted);">Aucun client trouvé.</td>
</tr>
@endforelse


