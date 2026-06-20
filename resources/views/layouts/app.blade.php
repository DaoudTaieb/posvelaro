<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Velaro POS')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #4f46e5;
            --primary-light: #eef2ff;
            --accent: #06b6d4;
            --bg: #f8fafc;
            --bg-card: #ffffff;
            --border: #e2e8f0;
            --text: #1e293b;
            --text-secondary: #475569;
            --text-muted: #94a3b8;
            --success: #16a34a;
            --success-bg: #f0fdf4;
            --success-border: #bbf7d0;
            --danger: #dc2626;
            --danger-bg: #fef2f2;
            --danger-border: #fecaca;
            --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }

        /* Top navigation */
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 32px;
            background: var(--bg-card);
            border-bottom: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .topbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
        }

        .brand-icon svg {
            width: 18px;
            height: 18px;
            color: white;
        }

        .brand-name {
            font-size: 17px;
            font-weight: 700;
            color: var(--text);
            letter-spacing: -0.3px;
        }

        .topbar-user {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .user-info {
            text-align: right;
        }

        .user-name {
            font-size: 14px;
            font-weight: 600;
            color: var(--text);
        }

        .user-site {
            font-size: 12px;
            color: var(--text-muted);
        }

        .user-avatar {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, var(--primary), #818cf8);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 15px;
            color: white;
        }

        .btn-logout {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            background: var(--danger-bg);
            border: 1px solid var(--danger-border);
            color: var(--danger);
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-logout:hover {
            background: #fee2e2;
        }

        .btn-logout svg {
            width: 16px;
            height: 16px;
        }

        /* App Layout */
        .app-layout {
            display: flex;
            min-height: calc(100vh - 61px); /* minus topbar */
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background: var(--bg-card);
            border-right: 1px solid var(--border);
            padding: 24px 16px;
            flex-shrink: 0;
        }

        .menu-group {
            margin-bottom: 24px;
        }

        .menu-title {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            background: #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 12px;
        }

        .menu-title svg {
            width: 18px;
            height: 18px;
        }

        .menu-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .menu-item {
            display: block;
            padding: 10px 16px 10px 44px;
            color: var(--text);
            text-decoration: none;
            font-size: 14px;
            font-weight: 400;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .menu-item:hover {
            background: var(--bg);
            color: var(--primary);
        }

        .menu-item.active {
            background: var(--primary-light);
            color: var(--primary);
            font-weight: 500;
        }

        /* Main content */
        .main-content {
            padding: 32px;
            flex: 1;
            min-width: 0;
            max-width: 100%; /* For full width tables */
            margin: 0;
        }
        
        .main-content-inner {
            max-width: 1100px;
            margin: 0 auto;
        }
        
        .main-content-inner.full-width {
            max-width: 100%;
        }

    </style>
    @yield('styles')
</head>
<body>
    <!-- Top bar -->
    <nav class="topbar">
        <div class="topbar-brand">
            <div class="brand-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <path d="M16 10a4 4 0 01-8 0"/>
                </svg>
            </div>
            <span class="brand-name">Velaro POS</span>
        </div>

        <div class="topbar-user">
            <div class="user-info">
                <div class="user-name">{{ Auth::user()->login ?? 'Utilisateur' }}</div>
                <div class="user-site">Site #{{ Auth::user()->siteid ?? '0' }}</div>
            </div>
            <div class="user-avatar">
                {{ strtoupper(substr(Auth::user()->login ?? 'U', 0, 1)) }}
            </div>
            <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                @csrf
                <button type="submit" class="btn-logout">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    Déconnexion
                </button>
            </form>
        </div>
    </nav>

    <!-- App Layout -->
    <div class="app-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="menu-group">
                <div class="menu-title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                    Vente
                </div>
                <ul class="menu-list">
                    <li>
                        <a href="{{ route('vente.caisse.index') }}" class="menu-item {{ request()->routeIs('vente.caisse.*') ? 'active' : '' }}">Caisse</a>
                    </li>
                    <li>
                        <a href="{{ route('vente.tickets.index') }}" class="menu-item {{ request()->routeIs('vente.tickets.*') ? 'active' : '' }}">Consultation Tickets</a>
                    </li>
                    <li>
                        <a href="{{ route('vente.commissions.index') }}" class="menu-item {{ request()->routeIs('vente.commissions.*') ? 'active' : '' }}">Calcul Commissions</a>
                    </li>
                    <li>
                        <a href="{{ route('vente.clients.index') }}" class="menu-item {{ request()->routeIs('vente.clients.*') ? 'active' : '' }}">Clients</a>
                    </li>
                </ul>
            </div>

            <div class="menu-group">
                <div class="menu-title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    Journée
                </div>
                <ul class="menu-list">
                    <li>
                        <a href="{{ route('vente.journee.ouverture') }}" class="menu-item {{ request()->routeIs('vente.journee.ouverture') ? 'active' : '' }}">Ouverture Journée</a>
                    </li>
                    <li>
                        <a href="{{ route('vente.journee.cloture') }}" class="menu-item {{ request()->routeIs('vente.journee.cloture') ? 'active' : '' }}">Clôture Journée</a>
                    </li>
                    <li>
                        <a href="{{ route('vente.journee.etat') }}" class="menu-item">
                            Etat Journée
                        </a>
                        <a href="{{ route('vente.journee.index') }}" class="menu-item {{ request()->routeIs('vente.journee.index') ? 'active' : '' }}">Consultation Journées</a>
                    </li>
                </ul>
            </div>

            <div class="menu-group">
                <div class="menu-title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                        <line x1="12" y1="22.08" x2="12" y2="12"></line>
                    </svg>
                    Stock
                </div>
                <ul class="menu-list">
                    <li>
                        <a href="{{ route('stock.articles.index') }}" class="menu-item {{ request()->routeIs('stock.articles.*') ? 'active' : '' }}">Articles</a>
                    </li>
                    <li>
                        <a href="{{ route('stock.consultation.index') }}" class="menu-item {{ request()->routeIs('stock.consultation.*') ? 'active' : '' }}">Consultation Stock</a>
                    </li>
                    <li>
                        <a href="{{ route('stock.detaille.index') }}" class="menu-item {{ request()->routeIs('stock.detaille.*') ? 'active' : '' }}">Stock Détaillé</a>
                    </li>
                    <li>
                        <a href="{{ route('stock.mouvements.index') }}" class="menu-item {{ request()->routeIs('stock.mouvements.*') ? 'active' : '' }}">Mouvements Articles</a>
                    </li>
                    <li>
                        <a href="{{ route('stock.etat.index') }}" class="menu-item {{ request()->routeIs('stock.etat.*') ? 'active' : '' }}">Etat de Stock</a>
                    </li>
                </ul>
            </div>
            <!-- Section Transfert -->
            <div class="menu-section">
                <div class="menu-header" onclick="toggleMenu('transfert-menu')">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                            <line x1="8" y1="21" x2="16" y2="21"></line>
                            <line x1="12" y1="17" x2="12" y2="21"></line>
                        </svg>
                        <span>Transfert</span>
                    </div>
                    <svg class="chevron" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </div>
                <ul class="menu-list" id="transfert-menu">
                    <li>
                        <a href="{{ route('transfert.demande_envoye.index') }}" class="menu-item {{ request()->routeIs('transfert.demande_envoye.*') ? 'active' : '' }}">Demande Trans Envoyé</a>
                    </li>
                    <li>
                        <a href="{{ route('transfert.demande_recu.index') }}" class="menu-item {{ request()->routeIs('transfert.demande_recu.*') ? 'active' : '' }}">Demande Trans reçu</a>
                    </li>
                    <li>
                        <a href="{{ route('transfert.envoye.index') }}" class="menu-item {{ request()->routeIs('transfert.envoye.*') ? 'active' : '' }}">Transfert Envoyé</a>
                    </li>
                    <li>
                        <a href="{{ route('transfert.recu.index') }}" class="menu-item {{ request()->routeIs('transfert.recu.*') ? 'active' : '' }}">Transfert reçu</a>
                    </li>
                </ul>
            </div>
        </aside>

        <!-- Main content -->
        <main class="main-content">
            @yield('content')
        </main>
    </div>
    
    @yield('scripts')
</body>
</html>
