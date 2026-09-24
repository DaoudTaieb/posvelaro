<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('titre') - {{ config('app.name') }}</title>
    <style>
        :root { --primary: #4f46e5; --primary-hover: #4338ca; --bg: #f8fafc; --text: #1e293b; --text-secondary: #475569; --border: #e2e8f0; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 16px;
               background: var(--bg); color: var(--text); font-family: 'Inter', 'Segoe UI', Arial, sans-serif; }
        .carte { width: min(460px, 100%); background: #fff; border: 1px solid var(--border); border-radius: 16px;
                 box-shadow: 0 20px 50px rgba(15, 23, 42, .08); padding: 32px 28px; text-align: center; }
        .code { display: inline-flex; align-items: center; justify-content: center; min-width: 64px; height: 64px; padding: 0 14px;
                border-radius: 16px; background: #eef2ff; color: var(--primary); font-size: 22px; font-weight: 800; margin-bottom: 18px; }
        h1 { margin: 0 0 10px; font-size: 20px; }
        p { margin: 0 0 24px; color: var(--text-secondary); line-height: 1.55; font-size: 14px; }
        .actions { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
        a { display: inline-flex; align-items: center; height: 40px; padding: 0 18px; border-radius: 10px; font-weight: 600;
            font-size: 14px; text-decoration: none; border: 1px solid var(--border); color: var(--text); background: #fff; }
        a.principal { background: var(--primary); border-color: var(--primary); color: #fff; }
        a.principal:hover { background: var(--primary-hover); }
    </style>
</head>
<body>
    <div class="carte">
        <div class="code">@yield('code')</div>
        <h1>@yield('titre')</h1>
        <p>@yield('message')</p>
        <div class="actions">
            <a href="javascript:history.back()">Retour</a>
            @auth
                <a class="principal" href="{{ url('/dashboard') }}">Accueil</a>
            @else
                <a class="principal" href="{{ url('/login') }}">Se connecter</a>
            @endauth
        </div>
    </div>
</body>
</html>
