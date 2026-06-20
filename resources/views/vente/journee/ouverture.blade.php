@extends('layouts.app')
@section('title', 'Ouverture Journée')

@section('content')
<div class="main-content-inner" style="max-width: 600px; margin: 40px auto;">
    
    @if(session('success'))
    <div style="background: var(--success-bg); border: 1px solid var(--success-border); color: var(--success); padding: 16px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
        </svg>
        <span style="font-weight: 500;">{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div style="background: var(--danger-bg); border: 1px solid var(--danger-border); color: var(--danger); padding: 16px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
        <span style="font-weight: 500;">{{ session('error') }}</span>
    </div>
    @endif

    <div style="background: white; border: 1px solid var(--border); border-radius: 12px; box-shadow: var(--shadow-sm); overflow: hidden;">
        
        <div style="padding: 20px 24px; border-bottom: 1px solid var(--border); background: #f8fafc;">
            <h2 style="font-size: 18px; font-weight: 700; color: var(--text); display: flex; align-items: center; gap: 10px; margin: 0;">
                <div style="padding: 6px; background: var(--primary-light); color: var(--primary); border-radius: 6px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                </div>
                Ouverture Journée
            </h2>
        </div>

        <div style="padding: 24px;">
            <form action="{{ route('vente.journee.ouverture.store') }}" method="POST">
                @csrf
                
                <div style="margin-bottom: 20px;">
                    <label for="caisseid" style="display: block; font-size: 13px; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px;">Caisse</label>
                    <select name="caisseid" id="caisseid" class="form-control" required style="width: 100%; border-radius: 8px; border: 1px solid var(--border); padding: 10px 14px; font-size: 14px; outline: none; transition: border-color 0.2s;">
                        <option value="">Sélectionnez une caisse</option>
                        @foreach($caisses as $caisse)
                            <option value="{{ $caisse->caisseid }}">{{ str_pad($caisse->numero ?? $caisse->caisseid, 2, '0', STR_PAD_LEFT) }} - {{ $caisse->libelle }}</option>
                        @endforeach
                    </select>
                    @error('caisseid')
                        <div style="color: var(--danger); font-size: 12px; margin-top: 6px;">{{ $message }}</div>
                    @enderror
                </div>

                <div style="margin-bottom: 24px;">
                    <label for="fondcaisse" style="display: block; font-size: 13px; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px;">Solde Caisse</label>
                    <input type="number" step="0.001" min="0" name="fondcaisse" id="fondcaisse" class="form-control" value="0" required style="width: 100%; border-radius: 8px; border: 1px solid var(--border); padding: 10px 14px; font-size: 14px; outline: none; transition: border-color 0.2s; font-family: monospace;">
                    @error('fondcaisse')
                        <div style="color: var(--danger); font-size: 12px; margin-top: 6px;">{{ $message }}</div>
                    @enderror
                </div>

                <div style="display: flex; justify-content: flex-end;">
                    <button type="submit" style="background: var(--primary); color: white; border: none; padding: 10px 24px; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; transition: background 0.2s;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .form-control:focus {
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 3px var(--primary-light) !important;
    }
</style>
@endsection
