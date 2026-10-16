@extends('layouts.app')
@section('title', 'Impression des tickets')

@section('content')
<div class="pos-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Impression des tickets</h1>
            <p class="page-subtitle">Imprimante thermique de chaque caisse. En mode Windows ou réseau, le ticket part directement à l'imprimante à la validation, sans fenêtre d'impression.</p>
        </div>
    </div>

    @forelse($caisses as $caisse)
        @php $c = $configs[$caisse->caisseid]; @endphp
        <div class="content-card" style="margin-bottom: 20px;">
            <div style="padding: 16px 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                <h2 style="margin: 0; font-size: 15px; font-weight: 600;">{{ $caisse->libelle }} <span style="color: var(--text-muted); font-weight: 400;">(caisse n° {{ $caisse->numero }})</span></h2>
                <form method="POST" action="{{ route('parametre.impression.test', $caisse->caisseid) }}" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn btn-outline" {{ $c['mode'] === 'navigateur' ? 'disabled' : '' }}>Imprimer un ticket de test</button>
                </form>
            </div>
            <form method="POST" action="{{ route('parametre.impression.update', $caisse->caisseid) }}" class="impression-form" style="padding: 20px; display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px; align-items: end;">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label class="form-label">Mode d'impression</label>
                    <select name="mode" class="form-control">
                        @foreach($modes as $valeur => $libelle)
                            <option value="{{ $valeur }}" {{ $c['mode'] === $valeur ? 'selected' : '' }}>{{ $libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Imprimante Windows (nom exact)</label>
                    <input type="text" name="imprimante" value="{{ $c['imprimante'] }}" class="form-control" placeholder="EPSON TM-T20II Receipt">
                    <span style="font-size: 11px; color: var(--text-muted);">Réglage partagé avec le logiciel de bureau.</span>
                </div>
                <div class="form-group">
                    <label class="form-label">Imprimante réseau (IP:port)</label>
                    <input type="text" name="reseau" value="{{ $c['reseau'] }}" class="form-control" placeholder="192.168.1.50:9100">
                </div>
                <div class="form-group">
                    <label class="form-label">Caractères par ligne</label>
                    <select name="colonnes" class="form-control">
                        @foreach([48 => '48 (papier 80 mm)', 42 => '42 (80 mm, police large)', 32 => '32 (papier 58 mm)'] as $n => $libelle)
                            <option value="{{ $n }}" {{ (int) $c['colonnes'] === $n ? 'selected' : '' }}>{{ $libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <label style="display: flex; gap: 8px; align-items: center; font-size: 13px;">
                    <input type="checkbox" name="accents" value="1" {{ $c['accents'] ? 'checked' : '' }}> Imprimer les accents (page de code PC858)
                </label>
                <label style="display: flex; gap: 8px; align-items: center; font-size: 13px;">
                    <input type="checkbox" name="tiroir" value="1" {{ $c['tiroir'] ? 'checked' : '' }}> Ouvrir le tiroir-caisse à l'encaissement
                </label>
                <div>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    @empty
        <div class="content-card"><p style="padding: 20px;">Aucune caisse sur ce site.</p></div>
    @endforelse

    <p style="font-size: 12px; color: var(--text-muted);">
        Mode Windows : l'imprimante doit être installée sur le poste qui fait tourner Golden Pos (raccourci « Golden Pos » ou serveur Apache local).
        Si le ticket de test sort sans accents lisibles, décochez « Imprimer les accents ».
    </p>
</div>
@endsection
