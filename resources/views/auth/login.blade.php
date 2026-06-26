<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} — Connexion</title>
    <meta name="description" content="Système de point de vente Golden Pos — Connectez-vous à votre espace">
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
            --primary-hover: #4338ca;
            --primary-light: #eef2ff;
            --accent: #06b6d4;
            --bg: #f8fafc;
            --bg-card: #ffffff;
            --border: #e2e8f0;
            --border-focus: #4f46e5;
            --text: #1e293b;
            --text-secondary: #475569;
            --text-muted: #94a3b8;
            --error: #dc2626;
            --error-bg: #fef2f2;
            --error-border: #fecaca;
            --success: #16a34a;
            --input-bg: #f8fafc;
            --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 10px 25px -3px rgba(0, 0, 0, 0.08), 0 4px 10px -4px rgba(0, 0, 0, 0.04);
            --shadow-xl: 0 20px 50px -12px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* Subtle background pattern */
        .bg-pattern {
            position: fixed;
            inset: 0;
            z-index: 0;
            background:
                radial-gradient(ellipse 80% 50% at 50% -10%, rgba(79, 70, 229, 0.06), transparent),
                radial-gradient(ellipse 60% 40% at 100% 50%, rgba(6, 182, 212, 0.04), transparent),
                radial-gradient(ellipse 50% 40% at 0% 100%, rgba(79, 70, 229, 0.03), transparent);
        }

        .bg-dots {
            position: fixed;
            inset: 0;
            z-index: 0;
            background-image: radial-gradient(circle, #cbd5e1 0.8px, transparent 0.8px);
            background-size: 32px 32px;
            opacity: 0.4;
        }

        /* Login container */
        .login-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 420px;
            padding: 24px;
        }

        /* Logo section */
        .logo-section {
            text-align: center;
            margin-bottom: 32px;
            animation: fadeIn 0.5s ease both;
        }

        .logo-icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 16px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 24px rgba(79, 70, 229, 0.25);
        }

        .logo-icon svg {
            width: 28px;
            height: 28px;
            color: white;
        }

        .logo-title {
            font-size: 26px;
            font-weight: 800;
            color: var(--text);
            letter-spacing: -0.5px;
        }

        .logo-subtitle {
            font-size: 14px;
            color: var(--text-muted);
            margin-top: 4px;
            font-weight: 400;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Card */
        .login-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 32px;
            box-shadow: var(--shadow-xl);
            animation: cardIn 0.5s ease 0.1s both;
        }

        @keyframes cardIn {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .card-header {
            margin-bottom: 24px;
        }

        .card-header h1 {
            font-size: 20px;
            font-weight: 700;
            color: var(--text);
        }

        .card-header p {
            font-size: 14px;
            color: var(--text-muted);
            margin-top: 4px;
        }

        /* Error alert */
        .alert-error {
            background: var(--error-bg);
            border: 1px solid var(--error-border);
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: var(--error);
            font-weight: 500;
            animation: shake 0.4s ease;
        }

        .alert-error svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20% { transform: translateX(-4px); }
            40% { transform: translateX(4px); }
            60% { transform: translateX(-3px); }
            80% { transform: translateX(2px); }
        }

        /* Form */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 6px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            pointer-events: none;
            transition: color 0.2s;
        }

        .input-icon svg {
            width: 20px;
            height: 20px;
        }

        .form-input {
            width: 100%;
            padding: 12px 14px 12px 42px;
            background: var(--input-bg);
            border: 1.5px solid var(--border);
            border-radius: 10px;
            color: var(--text);
            font-size: 15px;
            font-family: 'Inter', sans-serif;
            font-weight: 400;
            outline: none;
            transition: all 0.2s ease;
        }

        .form-input::placeholder {
            color: var(--text-muted);
        }

        .form-input:hover {
            border-color: #cbd5e1;
        }

        .form-input:focus {
            border-color: var(--border-focus);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .form-input:focus ~ .input-icon {
            color: var(--primary);
        }

        /* Password toggle */
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 4px;
            border-radius: 6px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
        }

        .password-toggle:hover {
            color: var(--text-secondary);
            background: rgba(0, 0, 0, 0.04);
        }

        .password-toggle svg {
            width: 20px;
            height: 20px;
        }

        /* Submit button */
        .btn-submit {
            width: 100%;
            padding: 13px 24px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 4px;
        }

        .btn-submit:hover {
            background: var(--primary-hover);
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35);
            transform: translateY(-1px);
        }

        .btn-submit:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(79, 70, 229, 0.25);
        }

        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .btn-content {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        /* Loading spinner */
        .spinner {
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
            display: none;
        }

        .btn-submit.loading .spinner { display: block; }
        .btn-submit.loading .btn-text { display: none; }

        @keyframes spin { to { transform: rotate(360deg); } }

        /* Footer */
        .login-footer {
            text-align: center;
            margin-top: 24px;
            animation: fadeIn 0.5s ease 0.3s both;
        }

        .login-footer p {
            font-size: 13px;
            color: var(--text-muted);
        }

        .version-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 8px;
            padding: 4px 12px;
            background: white;
            border: 1px solid var(--border);
            border-radius: 20px;
            font-size: 12px;
            color: var(--text-muted);
            box-shadow: var(--shadow-sm);
        }

        .version-dot {
            width: 6px;
            height: 6px;
            background: var(--success);
            border-radius: 50%;
            animation: pulse-dot 2s ease-in-out infinite;
        }

        @keyframes pulse-dot {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-container { padding: 16px; }
            .login-card { padding: 24px 20px; border-radius: 14px; }
            .logo-title { font-size: 22px; }
        }
    </style>
</head>
<body>
    <div class="bg-pattern"></div>
    <div class="bg-dots"></div>

    <div class="login-container">
        <!-- Logo -->
        <div class="logo-section">
            <div class="logo-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <path d="M16 10a4 4 0 01-8 0"/>
                </svg>
            </div>
            <h2 class="logo-title">{{ config('app.name') }}</h2>
            <p class="logo-subtitle">Système de Point de Vente</p>
        </div>

        <!-- Login Card -->
        <div class="login-card">
            <div class="card-header">
                <h1>Connexion</h1>
                <p>Entrez vos identifiants pour accéder au système</p>
            </div>

            @if ($errors->any())
                <div class="alert-error" id="alert-error">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="15" y1="9" x2="9" y2="15"/>
                        <line x1="9" y1="9" x2="15" y2="15"/>
                    </svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="login">Identifiant</label>
                    <div class="input-wrapper">
                        <span class="input-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </span>
                        <input
                            type="text"
                            id="login"
                            name="login"
                            class="form-input"
                            placeholder="Entrez votre identifiant"
                            value="{{ old('login') }}"
                            autocomplete="username"
                            autofocus
                            required
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Mot de passe</label>
                    <div class="input-wrapper">
                        <span class="input-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0110 0v4"/>
                            </svg>
                        </span>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-input"
                            placeholder="Entrez votre mot de passe"
                            autocomplete="current-password"
                            required
                            style="padding-right: 44px;"
                        >
                        <button type="button" class="password-toggle" id="togglePassword" aria-label="Afficher le mot de passe">
                            <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg id="eyeOffIcon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;">
                                <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/>
                                <line x1="1" y1="1" x2="23" y2="23"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="form-group" id="siteGroup" style="display: none;">
                    <label class="form-label" for="siteid">Site</label>
                    <div class="input-wrapper">
                        <span class="input-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                <polyline points="9 22 9 12 15 12 15 22"></polyline>
                            </svg>
                        </span>
                        <select
                            id="siteid"
                            name="siteid"
                            class="form-input"
                            style="padding-left: 42px; appearance: none;"
                        >
                            <option value="">Chargement...</option>
                        </select>
                        <div style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); pointer-events: none;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">
                    <div class="btn-content">
                        <span class="btn-text">Se connecter</span>
                        <div class="spinner"></div>
                    </div>
                </button>
            </form>
        </div>

        <!-- Footer -->
        <div class="login-footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }} — Tous droits réservés</p>
            <div class="version-badge">
                <span class="version-dot"></span>
                <span>v1.0 — Système en ligne</span>
            </div>
        </div>
    </div>

    <script>
        const toggleBtn = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        const eyeOffIcon = document.getElementById('eyeOffIcon');

        toggleBtn.addEventListener('click', () => {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            eyeIcon.style.display = isPassword ? 'none' : 'block';
            eyeOffIcon.style.display = isPassword ? 'block' : 'none';
        });

        const form = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');
        form.addEventListener('submit', () => {
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
        });

        document.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('focus', function() {
                this.closest('.input-wrapper').querySelector('.input-icon').style.color = '#4f46e5';
            });
            input.addEventListener('blur', function() {
                this.closest('.input-wrapper').querySelector('.input-icon').style.color = '';
            });
        });

        // Sites logic
        const loginInput = document.getElementById('login');
        const siteGroup = document.getElementById('siteGroup');
        const siteSelect = document.getElementById('siteid');

        let fetchTimeout;
        loginInput.addEventListener('input', () => {
            clearTimeout(fetchTimeout);
            fetchTimeout = setTimeout(() => {
                const login = loginInput.value.trim();
                if (login.toLowerCase() === 'admin') {
                    fetch(`/login/sites?login=${encodeURIComponent(login)}`)
                        .then(res => res.json())
                        .then(sites => {
                            if (sites && sites.length > 0) {
                                siteSelect.innerHTML = '';
                                sites.forEach(site => {
                                    const option = document.createElement('option');
                                    option.value = site.siteid;
                                    option.textContent = site.libelle;
                                    siteSelect.appendChild(option);
                                });
                                siteGroup.style.display = 'block';
                            } else {
                                siteGroup.style.display = 'none';
                                siteSelect.innerHTML = '<option value="">Chargement...</option>';
                            }
                        })
                        .catch(err => console.error('Erreur de chargement des sites:', err));
                } else {
                    siteGroup.style.display = 'none';
                    siteSelect.innerHTML = '<option value="">Chargement...</option>';
                }
            }, 400); // 400ms debounce
        });
        
        // Trigger initially if there is a value (e.g. form validation error redirect)
        if (loginInput.value.trim().length > 0) {
            loginInput.dispatchEvent(new Event('input'));
        }
    </script>
</body>
</html>
