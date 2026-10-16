@extends('layouts.app')

@section('title', 'Configuration Générale - Golden Pos')

@section('content')
<div class="pos-container">
    
    <!-- Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Configuration Générale</h1>
            <p class="page-subtitle">Paramétrage global et préférences du système Golden Pos.</p>
        </div>
        <div class="header-actions">
            <button class="btn btn-outline" onclick="window.history.back()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
                Fermer
            </button>
            <button type="submit" form="configForm" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                    <polyline points="7 3 7 8 15 8"></polyline>
                </svg>
                Enregistrer les modifications
            </button>
        </div>
    </div>

    <div class="content-card">
        <form id="configForm" action="{{ route('parametre.configuration.general.update') }}" method="POST" style="padding: 24px;">
            @csrf
            
            <div class="settings-grid">
                @php
                    $half = ceil($configs->count() / 2);
                    $col1 = $configs->slice(0, $half);
                    $col2 = $configs->slice($half);
                @endphp

                <!-- Colonne 1 -->
                <div class="settings-col">
                    @foreach($col1 as $config)
                        <div class="form-group" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 12px; margin-bottom: 12px;">
                            <label class="form-label" style="margin: 0; font-weight: 500; font-size: 13px;">{{ $config->caption }}</label>
                            {{-- Interrupteur seulement si la valeur est vraiment un booléen (ex. tauxacompte = 99 est typé booléen) --}}
                            @if($config->typevalueid == 2 && in_array(strtolower(trim((string) $config->value)), ['true', 'false'], true))
                                <div class="toggle-wrapper" style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-size: 12px; color: var(--text-muted);">Non</span>
                                    <label class="switch">
                                        <input type="hidden" name="config[{{ $config->retailconfigid }}]" value="False">
                                        <input type="checkbox" name="config[{{ $config->retailconfigid }}]" value="True" {{ strtolower($config->value) === 'true' ? 'checked' : '' }}>
                                        <span class="slider round"></span>
                                    </label>
                                    <span style="font-size: 12px; color: var(--text-main); font-weight: 500;">Oui</span>
                                </div>
                            @else
                                <input type="text" name="config[{{ $config->retailconfigid }}]" value="{{ old('config.' . $config->retailconfigid, $config->value) }}" class="form-control {{ $errors->has('config.' . $config->retailconfigid) ? 'is-invalid' : '' }}" style="width: 200px;">
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Colonne 2 -->
                <div class="settings-col">
                    @foreach($col2 as $config)
                        <div class="form-group" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 12px; margin-bottom: 12px;">
                            <label class="form-label" style="margin: 0; font-weight: 500; font-size: 13px;">{{ $config->caption }}</label>
                            {{-- Interrupteur seulement si la valeur est vraiment un booléen (ex. tauxacompte = 99 est typé booléen) --}}
                            @if($config->typevalueid == 2 && in_array(strtolower(trim((string) $config->value)), ['true', 'false'], true))
                                <div class="toggle-wrapper" style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-size: 12px; color: var(--text-muted);">Non</span>
                                    <label class="switch">
                                        <input type="hidden" name="config[{{ $config->retailconfigid }}]" value="False">
                                        <input type="checkbox" name="config[{{ $config->retailconfigid }}]" value="True" {{ strtolower($config->value) === 'true' ? 'checked' : '' }}>
                                        <span class="slider round"></span>
                                    </label>
                                    <span style="font-size: 12px; color: var(--text-main); font-weight: 500;">Oui</span>
                                </div>
                            @else
                                <input type="text" name="config[{{ $config->retailconfigid }}]" value="{{ old('config.' . $config->retailconfigid, $config->value) }}" class="form-control {{ $errors->has('config.' . $config->retailconfigid) ? 'is-invalid' : '' }}" style="width: 200px;">
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Sous-familles ouvrant droit à la fidélité --}}
            @if(isset($sousFamilles))
            <input type="hidden" name="fidelite_section" value="1">
            <div style="margin-top: 32px; border-top: 2px solid var(--border); padding-top: 20px;">
                <h2 style="font-size: 15px; font-weight: 600; margin: 0 0 4px;">Fidélité : sous-familles concernées</h2>
                <p style="font-size: 12px; color: var(--text-muted); margin: 0 0 14px;">
                    La remise fidélité (paliers ci-dessus) et le bon d'achat « par paire » ne s'appliquent qu'aux articles de ces sous-familles.
                </p>
                <div class="fidelite-grid">
                    @foreach($sousFamilles as $sf)
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; padding: 6px 8px; border: 1px solid var(--border); border-radius: 6px; cursor: pointer;">
                            <input type="checkbox" name="sousfamilles_fidelite[]" value="{{ $sf->sousfamilleid }}" {{ $sf->is_loyalty_enabled ? 'checked' : '' }}>
                            <span>{{ $sf->sousfamillelibelle }}</span>
                            <span style="margin-left: auto; font-size: 11px; color: var(--text-muted);">{{ $sf->famillelibelle }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            @endif
        </form>
    </div>
</div>
@endsection

@section('styles')
<style>
    .settings-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
    }

    @media (max-width: 900px) {
        .settings-grid {
            grid-template-columns: 1fr;
            gap: 0;
        }
    }

    .fidelite-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
        gap: 8px;
    }

    /* Toggle switch CSS - Golden Pos Style */
    .switch {
        position: relative;
        display: inline-block;
        width: 40px;
        height: 22px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: var(--border);
        transition: .3s;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 16px;
        width: 16px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .3s;
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }

    input:checked + .slider {
        background-color: var(--primary);
    }

    input:checked + .slider:before {
        transform: translateX(18px);
    }

    .slider.round {
        border-radius: 22px;
    }

    .slider.round:before {
        border-radius: 50%;
    }
</style>
@endsection
