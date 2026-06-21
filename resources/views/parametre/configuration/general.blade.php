@extends('layouts.app')

@section('title', 'Configuration - Général')

@section('styles')
<style>
    .main-content {
        padding: 0 !important;
    }

    .page-header {
        padding: 10px 15px;
        background: #f8f9fa;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .page-title {
        font-size: 16px;
        color: #111827;
        margin: 0;
        text-align: center;
        flex: 1;
        font-family: serif; /* to match screenshot title font */
    }

    .header-left {
        display: flex;
        gap: 5px;
    }

    .header-right {
        display: flex;
        gap: 2px;
    }

    .btn-action {
        background: white;
        border: 1px solid #d1d5db;
        cursor: pointer;
        color: #374151;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 4px;
        width: 30px;
        height: 30px;
        background: #fff;
    }
    
    .btn-action:hover {
        background: #f3f4f6;
    }

    .layout-container {
        padding: 30px 40px;
        background: white;
        height: calc(100vh - 60px);
        overflow-y: auto;
    }

    .settings-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        column-gap: 80px;
        row-gap: 20px;
        max-width: 1200px;
        margin: 0 auto;
    }

    .setting-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 13px;
        color: #111827;
        font-family: sans-serif;
    }

    .setting-input {
        width: 220px;
        padding: 4px 8px;
        border: 1px solid #d1d5db;
        border-radius: 2px;
        font-size: 12px;
        outline: none;
    }

    .toggle-container {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 11px;
        color: #4b5563;
        width: 220px;
    }

    /* Toggle switch CSS */
    .switch {
        position: relative;
        display: inline-block;
        width: 36px;
        height: 18px;
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
        background-color: #9ca3af;
        transition: .4s;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 14px;
        width: 14px;
        left: 2px;
        bottom: 2px;
        background-color: white;
        transition: .4s;
    }

    input:checked + .slider {
        background-color: #6b21a8; /* Purple color like in screenshot */
    }

    input:checked + .slider:before {
        transform: translateX(18px);
    }

    .slider.round {
        border-radius: 18px;
    }

    .slider.round:before {
        border-radius: 50%;
    }

    .alert-success {
        background: #dcfce7;
        color: #166534;
        padding: 10px 20px;
        border-bottom: 1px solid #bbf7d0;
        font-size: 12px;
        position: absolute;
        top: 55px;
        left: 0;
        right: 0;
        z-index: 100;
        animation: fadeOut 3s forwards;
        animation-delay: 2s;
    }

    @keyframes fadeOut {
        to { opacity: 0; visibility: hidden; }
    }
</style>
@endsection

@section('content')
<div class="main-content-inner full-width" style="padding: 0; background: white; height: 100vh; overflow: hidden; position: relative;">
    
    <form id="configForm" action="{{ route('parametre.configuration.general.update') }}" method="POST">
        @csrf
        
        <div style="padding: 5px 10px; font-size: 18px; color: #4b5563;">
            Configuration
        </div>

        <div class="page-header">
            <div class="header-left">
                <button type="button" style="display: flex; align-items: center; gap: 5px; padding: 4px 8px; border: 1px solid #d1d5db; background: white; cursor: pointer;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    <span style="font-size: 11px;">root</span>
                </button>
                <button type="button" style="padding: 4px 8px; border: 1px solid #d1d5db; background: white; font-size: 11px; cursor: pointer;">Acceuil</button>
            </div>
            
            <h1 class="page-title">Général</h1>
            
            <div class="header-right">
                <button type="submit" class="btn-action" title="Sauvegarder">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </button>
                <button type="button" class="btn-action">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
                </button>
                <a href="{{ url('/') }}" class="btn-action" style="display: flex; align-items: center; justify-content: center; text-decoration: none;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                </a>
                <button type="button" class="btn-action">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="layout-container">
            <div class="settings-grid">
                @php
                    $half = ceil($configs->count() / 2);
                    $col1 = $configs->slice(0, $half);
                    $col2 = $configs->slice($half);
                @endphp

                <!-- First Column -->
                <div>
                    @foreach($col1 as $config)
                        <div class="setting-item">
                            <span>{{ $config->caption }}</span>
                            @if($config->typevalueid == 2)
                                <div class="toggle-container">
                                    <span>Non</span>
                                    <label class="switch">
                                        <input type="hidden" name="config[{{ $config->retailconfigid }}]" value="False">
                                        <input type="checkbox" name="config[{{ $config->retailconfigid }}]" value="True" {{ strtolower($config->value) === 'true' ? 'checked' : '' }}>
                                        <span class="slider round"></span>
                                    </label>
                                    <span>OUI</span>
                                </div>
                            @else
                                <input type="text" name="config[{{ $config->retailconfigid }}]" value="{{ $config->value }}" class="setting-input">
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Second Column -->
                <div>
                    @foreach($col2 as $config)
                        <div class="setting-item">
                            <span>{{ $config->caption }}</span>
                            @if($config->typevalueid == 2)
                                <div class="toggle-container">
                                    <span>Non</span>
                                    <label class="switch">
                                        <input type="hidden" name="config[{{ $config->retailconfigid }}]" value="False">
                                        <input type="checkbox" name="config[{{ $config->retailconfigid }}]" value="True" {{ strtolower($config->value) === 'true' ? 'checked' : '' }}>
                                        <span class="slider round"></span>
                                    </label>
                                    <span>OUI</span>
                                </div>
                            @else
                                <input type="text" name="config[{{ $config->retailconfigid }}]" value="{{ $config->value }}" class="setting-input">
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
