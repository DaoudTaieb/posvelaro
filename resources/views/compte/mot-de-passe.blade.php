@extends('layouts.app')
@section('title', 'Mon mot de passe')

@section('content')
<div class="pos-container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Changer mon mot de passe</h1>
            <p class="page-subtitle">Compte {{ Auth::user()->login }}. Le nouveau mot de passe sert aussi pour le logiciel de bureau.</p>
        </div>
    </div>

    <div class="content-card" style="max-width: 520px;">
        <form method="POST" action="{{ route('compte.mot_de_passe.update') }}" style="padding: 24px; display: flex; flex-direction: column; gap: 16px;">
            @csrf
            <div class="form-group">
                <label class="form-label" for="actuel">Mot de passe actuel</label>
                <input type="password" id="actuel" name="actuel" class="form-control" required autocomplete="current-password">
            </div>
            <div class="form-group">
                <label class="form-label" for="nouveau">Nouveau mot de passe</label>
                <input type="password" id="nouveau" name="nouveau" class="form-control" required minlength="{{ $longueurMin }}" autocomplete="new-password">
                <span style="font-size: 12px; color: var(--text-muted);">Au moins {{ $longueurMin }} caractères.</span>
            </div>
            <div class="form-group">
                <label class="form-label" for="nouveau_confirmation">Confirmer le nouveau mot de passe</label>
                <input type="password" id="nouveau_confirmation" name="nouveau_confirmation" class="form-control" required minlength="{{ $longueurMin }}" autocomplete="new-password">
            </div>
            <div>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endsection
