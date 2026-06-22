@extends('layouts.app')

@section('title', 'Configuration Générale - Velaro')

@section('content')
<div class="pos-container">
    
    <!-- Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Configuration Générale</h1>
            <p class="page-subtitle">Paramétrage global et préférences du système Velaro.</p>
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

    @if(session('success'))
        <div style="background: var(--success); color: white; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; display: flex; align-items: center; gap: 8px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            {{ session('success') }}
        </div>
    @endif

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
                            @if($config->typevalueid == 2)
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
                                <input type="text" name="config[{{ $config->retailconfigid }}]" value="{{ $config->value }}" class="form-control" style="width: 200px;">
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Colonne 2 -->
                <div class="settings-col">
                    @foreach($col2 as $config)
                        <div class="form-group" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 12px; margin-bottom: 12px;">
                            <label class="form-label" style="margin: 0; font-weight: 500; font-size: 13px;">{{ $config->caption }}</label>
                            @if($config->typevalueid == 2)
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
                                <input type="text" name="config[{{ $config->retailconfigid }}]" value="{{ $config->value }}" class="form-control" style="width: 200px;">
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
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

    /* Toggle switch CSS - Velaro Style */
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
