{{-- Ticket Receipt - loaded via AJAX into modal --}}
<div class="receipt" style="
    width: 300px;
    background: #fff;
    font-family: 'Courier New', Courier, monospace;
    font-size: 12px;
    color: #000;
    border: 3px solid #d4a017;
    padding: 15px 12px;
    line-height: 1.5;
">
    {{-- Header --}}
    <div style="text-align: center; margin-bottom: 8px;">
        <div style="font-size: 26px; font-weight: 900; letter-spacing: 3px;">{{ $societe->raison ?? 'VELARO' }}</div>
        <div style="font-size: 11px; margin-top: 2px;">Velaro</div>
        <div style="font-size: 11px; margin-top: 6px; font-weight: 700;">BIENVENUE DANS VOTRE MAGASIN</div>
    </div>

    {{-- Ticket Number & Date --}}
    <div style="border-top: 1px dashed #000; padding: 6px 0; margin: 6px 0;">
        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 11px; gap: 4px;">
            <span style="letter-spacing: 1px; white-space: nowrap;">T I C K E T N°</span>
            <span style="font-weight: 700; white-space: nowrap;">{{ $ticket->cticketnumero }}</span>
            <span style="white-space: nowrap;">{{ \Carbon\Carbon::parse($ticket->cticketdate)->format('d/m/Y') }}</span>
        </div>

        {{-- Barcode (generated server-side with PHP divs) --}}
        <div style="text-align: center; margin: 6px 0; height: 40px; display: flex; justify-content: center; align-items: center; gap: 0;">
            @php
                $code = (string)($ticket->cticketnumero ?? '');
                for ($i = 0; $i < strlen($code); $i++) {
                    $c = ord($code[$i]);
                    $widths = [($c % 3) + 1, ($c % 2) + 1, (($c * 7) % 3) + 1, ($c % 2) + 1];
                    foreach ($widths as $j => $w) {
                        $bg = ($j % 2 === 0) ? '#000' : '#fff';
                        echo "<div style=\"height:35px;width:{$w}px;background:{$bg};display:inline-block;\"></div>";
                    }
                }
            @endphp
        </div>

        <div style="text-align: right; font-size: 11px; color: green;">{{ \Carbon\Carbon::parse($ticket->cticketdate)->format('H:i:s') }}</div>
    </div>

    {{-- Caisse / Caissier / Vendeur / Client --}}
    <div style="text-align: center; margin: 4px 0; border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 6px 0;">
        <div style="font-size: 11px;">Caisse {{ $caisse->siteid ?? '' }} - Caissier {{ $caissier_nom }} - Vendeur {{ $vendeur_nom }}</div>
        <div style="font-weight: 700;">Client: {{ $client_nom }}</div>
        <div style="font-size: 10px; color: #555;">Editée le, {{ \Carbon\Carbon::now()->locale('fr')->translatedFormat('l d F Y') }} A {{ \Carbon\Carbon::now()->format('H:i:s') }}</div>
    </div>

    {{-- Articles --}}
    <div style="border-bottom: 1px dashed #000; padding: 4px 0;">
    @foreach($lignes as $ligne)
        @php
            $remisePct = (float)($ligne->remise ?? 0);
            $lineTotal = (float)($ligne->totalttcnet ?? $ligne->totalttc ?? 0);
            $unitPriceNet = (float)($ligne->puttcnet ?? $lineTotal);
            $qte = (float)($ligne->qte ?? 1);
            // Couleur + Taille for variant display
            $variant = trim(($ligne->couleur_libelle ?? '') . ' ' . ($ligne->taille_libelle ?? ''));
        @endphp
        <div style="margin: 6px 0;">
            {{-- Product code --}}
            <div style="font-size: 10px; color: #555;">{{ $ligne->produit_code ?? '' }}</div>
            {{-- Product name + reference --}}
            <div style="display: flex; justify-content: space-between; font-weight: 700; text-transform: uppercase;">
                <span>{{ $ligne->produit_libelle ?? 'Article' }}</span>
                <span style="font-size: 11px;">{{ $ligne->produit_ref ?? '' }}</span>
            </div>
            {{-- Variant (couleur + taille) --}}
            @if($variant)
            <div style="text-align: right; font-size: 11px;">{{ $variant }}</div>
            @endif
            {{-- Prix unitaire + Remise % --}}
            <div style="display: flex; justify-content: center; gap: 30px; margin-top: 2px;">
                <span>{{ number_format((float)($ligne->ht ?? 0), 3, '.', '') }}</span>
                @if($remisePct > 0)
                <span style="color: green;">{{ number_format($remisePct, 0) }} %</span>
                @endif
            </div>
            {{-- Qte x Prix net = Total --}}
            <div style="display: flex; justify-content: space-between;">
                <span>{{ number_format($qte, 0) }}</span>
                <span>X</span>
                <span>{{ number_format($unitPriceNet, 3, '.', '') }}</span>
                <span style="font-weight: 700;">{{ number_format($lineTotal, 3, '.', '') }}</span>
            </div>
        </div>
    @endforeach
    </div>

    {{-- Totals --}}
    <div style="border-bottom: 2px solid #000; padding: 8px 0; margin-top: 4px;">
        <div style="display: flex; justify-content: space-between; font-weight: 700; font-size: 11px;">
            <span>TOTAL BRUT</span>
            <span>REMISE</span>
            <span>TOTAL TTC</span>
        </div>
        <div style="display: flex; justify-content: space-between; font-weight: 700;">
            <span>{{ number_format($ticket->totalbrutht ?? 0, 3, '.', '') }}</span>
            <span>{{ number_format($remise_montant, 3, '.', '') }}</span>
            <span>{{ number_format($ticket->totalttc ?? 0, 3, '.', '') }}</span>
        </div>
    </div>

    {{-- Total Ticket --}}
    <div style="text-align: center; font-weight: 900; font-size: 13px; padding: 8px 0; border-bottom: 2px solid #000;">
        TOTAL TICKET &nbsp; {{ $total_articles }} article(s) &nbsp;&nbsp; {{ number_format($ticket->totalttc ?? 0, 3, '.', '') }} DT
    </div>

    {{-- Règlement --}}
    <div style="text-align: center; margin: 8px 0 4px; border-bottom: 1px dashed #000; padding-bottom: 6px;">
        <div style="font-weight: 700; text-decoration: underline; margin-bottom: 6px;">Reglement Reçu</div>
        @foreach($reglements as $reg)
        <div style="display: flex; justify-content: space-between; font-size: 11px;">
            <span>{{ $reg->mode_libelle ?? 'Espèce' }} {{ \Carbon\Carbon::parse($reg->date)->format('d/m/Y H:i:s') }}</span>
            <span>{{ number_format($reg->montant ?? 0, 3, '.', '') }}</span>
        </div>
        @endforeach
    </div>

    {{-- Footer --}}
    <div style="text-align: center; margin-top: 8px; font-size: 11px; font-weight: 700;">
        CONSERVEZ VOTRE TICKET POUR ECHANGE<br>
        DANS 48H SAUF PERIODE SOLDES<br>
        MERCI DE VOTRE VISITE<br>
        A BIENTOT
    </div>

    {{-- Address --}}
    @if($societe->adresse ?? false)
    <div style="text-align: center; margin-top: 12px; font-size: 10px; color: #333;">
        {{ $societe->adresse }}
    </div>
    @endif
</div>

