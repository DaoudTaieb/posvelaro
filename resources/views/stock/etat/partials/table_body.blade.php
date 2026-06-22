@forelse($etatStocks as $item)
    @php
        $entrer = $item->total_entrer ?? 0;
        $sortie = $item->total_sortie ?? 0;
        $achat = $item->total_achat ?? 0;
        $ret_achat = $item->total_ret_achat ?? 0;
        $vente = $item->total_vente ?? 0;
        $ret_vente = $item->total_ret_vente ?? 0;
        $dispo = $item->dispo ?? 0;
        $pv = $item->pv_ttc ?? 0;
        $val_pv = $item->val_au_pv ?? 0;
    @endphp
    <tr style="border-bottom: 1px solid var(--border); transition: background 0.15s;" class="data-row" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
        <td style="padding: 8px 12px; color: var(--text); border-right: 1px solid var(--border);" class="font-medium">{{ $item->produitcode }}</td>
        <td style="padding: 8px 12px; color: var(--text-secondary); border-right: 1px solid var(--border);">{{ $item->couleurlibelle }}</td>
        <td style="padding: 8px 12px; color: var(--text-secondary); border-right: 1px solid var(--border);">{{ $item->taillelibelle }}</td>
        <td style="padding: 8px 12px; border-right: 1px solid var(--border);" class="amount-cell text-right">{{ $entrer != 0 ? number_format($entrer, 0, '', ' ') : '0' }}</td>
        <td style="padding: 8px 12px; border-right: 1px solid var(--border);" class="amount-cell text-right">{{ $sortie != 0 ? number_format($sortie, 0, '', ' ') : '0' }}</td>
        <td style="padding: 8px 12px; border-right: 1px solid var(--border);" class="amount-cell text-right">{{ $achat != 0 ? number_format($achat, 0, '', ' ') : '0' }}</td>
        <td style="padding: 8px 12px; border-right: 1px solid var(--border);" class="amount-cell text-right text-muted">{{ $ret_achat != 0 ? number_format($ret_achat, 0, '', ' ') : '0' }}</td>
        <td style="padding: 8px 12px; border-right: 1px solid var(--border);" class="amount-cell text-right text-primary">{{ $vente != 0 ? number_format($vente, 0, '', ' ') : '0' }}</td>
        <td style="padding: 8px 12px; border-right: 1px solid var(--border);" class="amount-cell text-right text-muted">{{ $ret_vente != 0 ? number_format($ret_vente, 0, '', ' ') : '0' }}</td>
        <td style="padding: 8px 12px; border-right: 1px solid var(--border); font-weight: 600; color: {{ $dispo < 0 ? 'var(--danger)' : ($dispo > 0 ? 'var(--success)' : 'var(--text-main)') }};" class="amount-cell text-right">
            {{ $dispo != 0 ? number_format($dispo, 0, '', ' ') : '0' }}
        </td>
        <td style="padding: 8px 12px; border-right: 1px solid var(--border);" class="amount-cell text-right">{{ number_format($pv, 3, ',', ' ') }}</td>
        <td style="padding: 8px 12px; font-weight: 600; color: {{ $val_pv < 0 ? 'var(--danger)' : 'var(--text-main)' }};" class="amount-cell text-right">
            {{ number_format($val_pv, 3, ',', ' ') }}
        </td>
    </tr>
@empty
<tr>
    <td colspan="12" style="padding: 60px 24px; text-align: center;">
        <div class="empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--text-muted); margin-bottom: 12px;">
                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
            </svg>
            <p style="font-size: 15px; font-weight: 600; color: var(--text-main); margin: 0 0 6px 0;">Aucune donnée chargée</p>
            <span style="font-size: 13px; color: var(--text-muted);">Veuillez cliquer sur le bouton "Filtres" ci-dessus ou saisir une recherche pour charger le stock.</span>
        </div>
    </td>
</tr>
@endforelse
