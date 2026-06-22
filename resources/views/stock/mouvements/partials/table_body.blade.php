@php
    $groupedItems = collect($mouvements->items())->groupBy('reference');
@endphp
@forelse($groupedItems as $reference => $items)
    @php
        $grpCount = count($items);
        $grpAchat = 0;
        $grpEntrer = 0;
        $grpSortie = 0;
        $grpVente = 0;
        
        foreach($items as $m) {
            $a = $m->qteachat ?? 0;
            $v = $m->qtevente ?? 0;
            $adj = ($m->qtetransfert ?? 0) + ($m->qteinout ?? 0) + ($m->qteecart ?? 0);
            $e = $adj > 0 ? $adj : 0;
            $s = $adj < 0 ? abs($adj) : 0;
            
            $grpAchat += $a;
            $grpEntrer += $e;
            $grpSortie += $s;
            $grpVente += $v;
        }
        $groupId = 'group-' . Str::slug($reference ?? 'sans-ref') . '-' . $loop->index;
    @endphp
    
    <!-- Ligne de groupe header -->
    <tr class="group-header" onclick="toggleGroup('{{ $groupId }}')" style="background: rgba(99, 102, 241, 0.04); cursor: pointer; border-bottom: 1px solid var(--border);">
        <td colspan="12" style="padding: 10px 16px; font-weight: 700; color: var(--primary);">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="chevron-icon" id="icon-{{ $groupId }}" style="transition: transform 0.2s;">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                    Référence : <span style="color: var(--text-main); margin-left: 4px;">{{ $reference ?: 'Sans Référence' }}</span>
                    <span class="modern-badge badge-purple" style="font-size: 11px; padding: 2px 8px; margin-left: 12px;">{{ $grpCount }} mouvements</span>
                </div>
                <div style="display: flex; gap: 24px; font-family: monospace; font-size: 11px; color: var(--text-secondary);">
                    <span>Achat: <strong style="color: var(--success);">{{ $grpAchat }}</strong></span>
                    <span>Entrée: <strong>{{ $grpEntrer }}</strong></span>
                    <span>Sortie: <strong style="color: var(--danger);">{{ $grpSortie }}</strong></span>
                    <span>Vente: <strong style="color: var(--primary);">{{ $grpVente }}</strong></span>
                </div>
            </div>
        </td>
    </tr>
    
    <!-- Lignes de détail du groupe -->
    @foreach($items as $mvt)
        @php
            $achat = $mvt->qteachat ?? 0;
            $vente = $mvt->qtevente ?? 0;
            $adj = ($mvt->qtetransfert ?? 0) + ($mvt->qteinout ?? 0) + ($mvt->qteecart ?? 0);
            $entrer = $adj > 0 ? $adj : 0;
            $sortie = $adj < 0 ? abs($adj) : 0;
            $dt = \Carbon\Carbon::parse($mvt->dateoperation);
        @endphp
        <tr class="data-row {{ $groupId }}" style="border-bottom: 1px solid var(--border); transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
            <td style="padding: 8px 12px; color: var(--text); border-right: 1px solid var(--border);" class="font-medium">{{ $mvt->produitcode }}</td>
            <td style="padding: 8px 12px; color: var(--text-secondary); border-right: 1px solid var(--border);">{{ $mvt->couleurlibelle }}</td>
            <td style="padding: 8px 12px; color: var(--text-secondary); border-right: 1px solid var(--border);">{{ $mvt->taillelibelle }}</td>
            <td style="padding: 8px 12px; border-right: 1px solid var(--border);" class="amount-cell text-success font-medium">{{ $achat != 0 ? number_format($achat, 0, '', '') : '' }}</td>
            <td style="padding: 8px 12px; border-right: 1px solid var(--border);" class="amount-cell font-medium">{{ $entrer != 0 ? number_format($entrer, 0, '', '') : '' }}</td>
            <td style="padding: 8px 12px; border-right: 1px solid var(--border);" class="amount-cell text-danger font-medium">{{ $sortie != 0 ? number_format($sortie, 0, '', '') : '' }}</td>
            <td style="padding: 8px 12px; border-right: 1px solid var(--border);" class="amount-cell text-primary font-bold">{{ $vente != 0 ? number_format($vente, 0, '', '') : '' }}</td>
            <td style="padding: 8px 12px; color: var(--text-secondary); border-right: 1px solid var(--border); font-family: monospace;">{{ $mvt->docid }}</td>
            <td style="padding: 8px 12px; color: var(--text-secondary); border-right: 1px solid var(--border); text-align: center;">{{ $dt->format('d/m/Y') }}</td>
            <td style="padding: 8px 12px; color: var(--text-secondary); border-right: 1px solid var(--border); text-align: center;">{{ $dt->format('H:i') }}</td>
            <td style="padding: 8px 12px; color: var(--text-secondary); border-right: 1px solid var(--border);">{{ $mvt->doclibelle }}</td>
            <td style="padding: 8px 12px; color: var(--text-secondary);">{{ $mvt->sitelibelle }}</td>
        </tr>
    @endforeach
@empty
<tr>
    <td colspan="12">
        <div class="empty-state" style="padding: 60px 24px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
            </svg>
            <p>Aucun mouvement de stock enregistré</p>
            <span style="font-size: 13px; color: var(--text-muted);">Essayez de modifier la plage de dates ou d'ajouter d'autres filtres.</span>
        </div>
    </td>
</tr>
@endforelse
