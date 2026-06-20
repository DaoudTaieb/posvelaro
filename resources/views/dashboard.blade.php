@extends('layouts.app')

@section('title', 'Velaro POS — Dashboard')

@section('styles')
<style>
    .welcome-section {
        margin-bottom: 32px;
    }
    
    .welcome-title {
        font-size: 28px;
        font-weight: 700;
        color: var(--text);
        margin-bottom: 8px;
        letter-spacing: -0.5px;
    }
    
    .welcome-title span {
        color: var(--primary);
    }
    
    .welcome-subtitle {
        font-size: 15px;
        color: var(--text-secondary);
    }
    
    .status-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 40px;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        box-shadow: var(--shadow-sm);
        max-width: 500px;
        margin: 0 auto;
    }
    
    .status-icon {
        width: 80px;
        height: 80px;
        background: var(--success-bg);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 24px;
        box-shadow: 0 4px 12px rgba(22, 163, 74, 0.1);
    }
    
    .status-icon svg {
        width: 40px;
        height: 40px;
        color: var(--success);
    }
    
    .status-title {
        font-size: 22px;
        font-weight: 700;
        color: var(--text);
        margin-bottom: 12px;
    }
    
    .status-text {
        font-size: 15px;
        color: var(--text-secondary);
        line-height: 1.6;
    }
</style>
@endsection

@section('content')
<div class="main-content-inner">
    <div class="welcome-section">
        <h1 class="welcome-title">Bienvenue, <span>{{ Auth::user()->login ?? 'Utilisateur' }}</span> 👋</h1>
        <p class="welcome-subtitle">Vous êtes connecté au système Velaro POS</p>
    </div>

    <div class="status-card">
        <div class="status-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
        </div>
        <h2 class="status-title">Connexion réussie !</h2>
        <p class="status-text">
            Vous êtes connecté en tant que <strong>{{ Auth::user()->login ?? 'Utilisateur' }}</strong><br>
            Société #{{ Auth::user()->societeid ?? '0' }} — Site #{{ Auth::user()->siteid ?? '0' }}
        </p>
    </div>
</div>
@endsection
