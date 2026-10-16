@extends('layouts.app')
@section('title', 'Contrôle des données')

@section('content')
<div class="pos-container">

    <div class="page-header">
        <div>
            <h1 class="page-title">Contrôle des données</h1>
            <p class="page-subtitle">Incohérences détectées sur le site {{ $site }} (tickets, règlements, journées, stock). Lecture seule : les corrections se font dans la caisse ou le logiciel de bureau.</p>
        </div>
        <div class="header-actions">
            <button class="btn btn-outline" onclick="window.location.reload()">Actualiser</button>
        </div>
    </div>

    @php
        $couleurs = ['error' => 'var(--danger)', 'warning' => '#d97706', 'info' => 'var(--primary)'];
    @endphp

    <div class="kpi-grid">
        @foreach($controles as $c)
        <div class="kpi-card">
            <div class="kpi-info">
                <span class="kpi-label">{{ $c['titre'] }}</span>
                <span class="kpi-value" style="color: {{ $c['total'] > 0 ? $couleurs[$c['gravite']] : 'var(--success)' }};">{{ $c['total'] }}</span>
            </div>
        </div>
        @endforeach
        <div class="kpi-card">
            <div class="kpi-info">
                <span class="kpi-label">Articles en stock négatif</span>
                <span class="kpi-value" style="color: {{ $stockNegatif > 0 ? 'var(--danger)' : 'var(--success)' }};">
                    @peut('cons.stock')<a href="{{ route('stock.negatifs.index') }}" style="color: inherit;">{{ $stockNegatif }}</a>@else{{ $stockNegatif }}@endpeut
                </span>
            </div>
        </div>
    </div>

    @foreach($controles as $c)
        @if($c['total'] > 0)
        <div class="content-card" style="margin-top: 20px;">
            <div style="padding: 16px 20px; border-bottom: 1px solid var(--border);">
                <h2 style="margin: 0; font-size: 15px; font-weight: 600; color: {{ $couleurs[$c['gravite']] }};">{{ $c['titre'] }} ({{ $c['total'] }})</h2>
                <p style="margin: 4px 0 0; font-size: 12px; color: var(--text-muted);">{{ $c['aide'] }}</p>
            </div>
            <div class="table-responsive" style="min-height: auto;">
                <table class="data-table" style="font-size: 13px;">
                    <thead>
                        <tr>@foreach($c['colonnes'] as $col)<th>{{ $col }}</th>@endforeach</tr>
                    </thead>
                    <tbody>
                        @foreach($c['lignes'] as $ligne)
                        <tr>@foreach($ligne as $cellule)<td>{{ $cellule }}</td>@endforeach</tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($c['total'] > $limite)
            <p style="padding: 10px 20px; margin: 0; font-size: 12px; color: var(--text-muted);">Affichage des {{ $limite }} plus récents sur {{ $c['total'] }}.</p>
            @endif
        </div>
        @endif
    @endforeach
</div>
@endsection
