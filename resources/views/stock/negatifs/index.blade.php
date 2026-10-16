@extends('layouts.app')
@section('title', 'Stocks négatifs')

@section('content')
<div class="pos-container">

    <div class="page-header">
        <div>
            <h1 class="page-title">Stocks négatifs</h1>
            <p class="page-subtitle">Articles du site {{ $site }} dont le stock calculé est inférieur à zéro. À régulariser par une entrée de stock, une réception de transfert ou un inventaire.</p>
        </div>
        <div class="header-actions">
            <button class="btn btn-outline" onclick="window.location.reload()">Actualiser</button>
        </div>
    </div>

    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-info">
                <span class="kpi-label">Articles en stock négatif</span>
                <span class="kpi-value" style="color: {{ $totaux->lignes > 0 ? 'var(--danger)' : 'var(--success)' }};">{{ number_format($totaux->lignes, 0, ',', ' ') }}</span>
            </div>
        </div>
        <div class="kpi-card">
            <div class="kpi-info">
                <span class="kpi-label">Quantité manquante</span>
                <span class="kpi-value">{{ number_format(abs($totaux->quantite), 0, ',', ' ') }}</span>
            </div>
        </div>
    </div>

    <div class="content-card">
        <form method="GET" action="{{ route('stock.negatifs.index') }}" style="padding: 16px 20px; display: flex; gap: 10px; align-items: center;">
            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Référence, code ou désignation" style="max-width: 360px;">
            <button type="submit" class="btn btn-primary">Rechercher</button>
            @if(request('q'))<a href="{{ route('stock.negatifs.index') }}" class="btn btn-outline">Effacer</a>@endif
        </form>

        <div class="table-responsive" style="min-height: auto;">
            <table class="data-table" style="font-size: 13px;">
                <thead>
                    <tr>
                        <th style="width: 120px;">Code</th>
                        <th>Référence</th>
                        <th>Désignation</th>
                        <th>Sous-famille</th>
                        <th>Couleur</th>
                        <th style="width: 90px;">Taille</th>
                        <th class="text-right" style="width: 110px;">Stock</th>
                        <th class="text-right" style="width: 110px;">PV TTC</th>
                        <th style="width: 150px;">Dernière vente</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stocks as $s)
                    <tr>
                        <td class="font-medium">{{ $s->produitcode }}</td>
                        <td>{{ $s->reference }}</td>
                        <td>{{ $s->produitlibelle }}</td>
                        <td>{{ $s->sousfamillelibelle }}</td>
                        <td>{{ $s->couleurlibelle }}</td>
                        <td>{{ $s->taillelibelle }}</td>
                        <td class="amount-cell font-bold" style="color: var(--danger);">{{ number_format($s->qtestock, 0, ',', ' ') }}</td>
                        <td class="amount-cell">{{ number_format($s->ttc_vente, 3, ',', ' ') }}</td>
                        <td>{{ $s->derniere_vente ? \Carbon\Carbon::parse($s->derniere_vente)->format('d/m/Y H:i') : '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9">
                            <div class="empty-state">
                                <p>Aucun article en stock négatif sur ce site.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($stocks->hasPages())
        <div class="pagination-wrapper">
            {{ $stocks->links('pagination::bootstrap-4') }}
        </div>
        @endif
    </div>
</div>
@endsection
