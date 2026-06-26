@extends('layouts.app')
@section('title', 'Ouverture Journée')

@section('content')
<div class="pos-container" style="max-width: 600px; margin: 0 auto; padding-top: 40px;">
    
    <!-- En-tête -->
    <div class="page-header" style="margin-bottom: 24px; display: block; text-align: center;">
        <h1 class="page-title" style="font-size: 28px; font-weight: 800; text-align: center; margin-bottom: 6px;">Ouverture de Journée</h1>
        <p class="page-subtitle" style="text-align: center; font-size: 14px;">Sélectionnez votre caisse et spécifiez le solde initial d'ouverture.</p>
        <div style="margin-top: 12px; display: inline-flex; align-items: center; gap: 8px; background: #f0f4ff; border: 1px solid #c7d2fe; padding: 8px 18px; border-radius: 999px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
            <span style="font-size: 13px; font-weight: 600; color: #4338ca;">Connecté : {{ Auth::user()->login ?? Auth::user()->name ?? 'Utilisateur' }}</span>
        </div>
    </div>

    <!-- Alertes -->
    @if(session('success'))
    <div class="modern-badge badge-success" style="width: 100%; padding: 14px 18px; border-radius: var(--radius-md); font-size: 14px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; line-height: 1.5; box-shadow: var(--shadow-sm);">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
        </svg>
        <span style="font-weight: 600;">{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="modern-badge badge-danger" style="width: 100%; padding: 14px 18px; border-radius: var(--radius-md); font-size: 14px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; line-height: 1.5; box-shadow: var(--shadow-sm);">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
        <span style="font-weight: 600;">{{ session('error') }}</span>
    </div>
    @endif

    @if(session('warning'))
    <div class="modern-badge badge-warning" style="width: 100%; padding: 14px 18px; border-radius: var(--radius-md); font-size: 14px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; line-height: 1.5; box-shadow: var(--shadow-sm); background-color: #fffbeb; color: #b45309; border: 1px solid #fcd34d;">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
            <line x1="12" y1="9" x2="12" y2="13"></line>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
        </svg>
        <span style="font-weight: 600;">{{ session('warning') }}</span>
    </div>
    @endif

    <!-- Carte principale -->
    <div class="content-card" style="box-shadow: var(--shadow-md);">
        
        <!-- En-tête de la Carte -->
        <div style="padding: 24px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; background: #fdfdfe;">
            <div style="display: flex; align-items: center; gap: 16px;">
                <div class="kpi-icon-wrapper bg-indigo-light" style="width: 44px; height: 44px; border-radius: var(--radius-md); flex-shrink: 0;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                        <line x1="8" y1="21" x2="16" y2="21"></line>
                        <line x1="12" y1="17" x2="12" y2="21"></line>
                    </svg>
                </div>
                <div style="text-align: left;">
                    <h2 style="font-size: 16px; font-weight: 700; color: var(--text-main); margin: 0;">Paramètres de Caisse</h2>
                    <p style="font-size: 12px; color: var(--text-muted); margin: 0;">Veuillez remplir les informations requises</p>
                </div>
            </div>
            <div style="display: inline-flex; align-items: center; gap: 6px; background: #f0f4ff; border: 1px solid #c7d2fe; padding: 6px 14px; border-radius: 999px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6366f1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <span style="font-size: 12px; font-weight: 600; color: #4338ca;">{{ Auth::user()->login ?? Auth::user()->name ?? 'Utilisateur' }}</span>
            </div>
        </div>

        <!-- Corps du Formulaire -->
        <div style="padding: 28px;">
            <form action="{{ route('vente.journee.ouverture.store') }}" method="POST">
                @csrf
                
                <!-- Sélection de la Caisse -->
                <div class="form-group" style="margin-bottom: 24px; text-align: left;">
                    <label for="caisseid" class="form-label" style="font-weight: 600; color: var(--text-main); margin-bottom: 8px;">Caisse de Vente</label>
                    <select name="caisseid" id="caisseid" class="form-control" required style="height: 44px; font-size: 14px; border-radius: var(--radius-md);">
                        @if($caisses->count() > 1)
                            <option value="">Sélectionnez une caisse...</option>
                        @endif
                        @foreach($caisses as $caisse)
                            <option value="{{ $caisse->caisseid }}" {{ $caisses->count() == 1 ? 'selected' : '' }}>{{ str_pad($caisse->numero ?? $caisse->caisseid, 2, '0', STR_PAD_LEFT) }} - {{ $caisse->libelle }}</option>
                        @endforeach
                    </select>
                    @error('caisseid')
                        <div style="color: var(--danger); font-size: 12px; margin-top: 6px; font-weight: 500;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Solde initial / Fond de caisse -->
                <div class="form-group" style="margin-bottom: 32px; text-align: left;">
                    <label for="fondcaisse" class="form-label" style="font-weight: 600; color: var(--text-main); margin-bottom: 8px;">Solde Initial (Fond de caisse)</label>
                    <div style="position: relative; display: flex; align-items: center; width: 100%;">
                        <input type="number" step="0.001" min="0" name="fondcaisse" id="fondcaisse" class="form-control" value="0" required style="height: 44px; font-size: 16px; font-weight: 700; padding-right: 60px; font-family: 'Inter', sans-serif; border-radius: var(--radius-md);">
                        <span style="position: absolute; right: 16px; font-weight: 700; font-size: 13px; color: var(--text-muted); pointer-events: none;">TND</span>
                    </div>
                    @error('fondcaisse')
                        <div style="color: var(--danger); font-size: 12px; margin-top: 6px; font-weight: 500;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Bouton Enregistrer -->
                <div style="display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn btn-primary" style="height: 44px; padding: 0 28px; font-weight: 600; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); display: inline-flex; align-items: center; gap: 8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        Enregistrer l'Ouverture
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
