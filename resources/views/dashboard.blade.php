@extends('layouts.app')

@section('title', 'Velaro POS — Dashboard')

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
