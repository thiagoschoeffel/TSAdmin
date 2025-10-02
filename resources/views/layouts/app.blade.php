<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'Example App'))</title>
    <style>
        :root {
            color-scheme: light;
        }
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        header {
            background: #0f172a;
            color: #f8fafc;
            padding: 1rem 2rem;
        }
        header nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 960px;
            margin: 0 auto;
        }
        header a {
            color: inherit;
            text-decoration: none;
            font-weight: 600;
        }
        main {
            flex: 1;
            width: 100%;
            max-width: 960px;
            margin: 0 auto;
            padding: 2rem;
        }
        .nav-links {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        .button-link,
        button,
        .link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.65rem 1.2rem;
            border-radius: 0.5rem;
            border: none;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
        }
        .button-link,
        button {
            background: #2563eb;
            color: #f8fafc;
        }
        .button-link:hover,
        button:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.15);
        }
        .link {
            background: transparent;
            color: inherit;
            padding: 0;
        }
        .card {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 20px 35px rgba(15, 23, 42, 0.08);
        }
        form {
            display: grid;
            gap: 1.25rem;
        }
        label {
            font-size: 0.9rem;
            font-weight: 600;
            color: #1e293b;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        input[type="email"],
        input[type="password"],
        input[type="text"] {
            padding: 0.75rem 1rem;
            border-radius: 0.55rem;
            border: 1px solid #cbd5f5;
            font-size: 1rem;
            background: #f8fafc;
            transition: border 0.15s ease, box-shadow 0.15s ease;
        }
        input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
        }
        .error {
            color: #dc2626;
            font-size: 0.85rem;
        }
        .status {
            background: #dcfce7;
            color: #166534;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        footer {
            text-align: center;
            padding: 2rem;
            color: #64748b;
            font-size: 0.85rem;
        }
        @media (max-width: 640px) {
            header nav {
                flex-direction: column;
                gap: 1rem;
            }
            main {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <a href="{{ route('home') }}">{{ config('app.name', 'Example App') }}</a>
            <div class="nav-links">
                @auth
                    <a class="link" href="{{ route('dashboard') }}">Dashboard</a>
                    <a class="link" href="{{ route('profile.edit') }}">Meu perfil</a>
                    <a class="link" href="{{ route('users.index') }}">Usuários</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit">Sair</button>
                    </form>
                @else
                    <a class="link" href="{{ route('login') }}">Entrar</a>
                    <a class="button-link" href="{{ route('register') }}">Registrar</a>
                @endauth
            </div>
        </nav>
    </header>

    <main>
        @yield('content')
    </main>

    <footer>
        &copy; {{ date('Y') }} {{ config('app.name', 'Example App') }}. Todos os direitos reservados.
    </footer>
</body>
</html>
