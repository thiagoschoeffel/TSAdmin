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
            gap: 1.5rem;
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
            flex-wrap: wrap;
        }
        .nav-actions {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .nav-user {
            position: relative;
        }
        .user-toggle {
            width: 40px;
            height: 40px;
            border-radius: 999px;
            border: none;
            background: #1e3a8a;
            color: #f8fafc;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .user-toggle:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(30, 64, 175, 0.25);
        }
        .user-dropdown {
            position: absolute;
            right: 0;
            top: calc(100% + 0.75rem);
            min-width: 200px;
            background: #ffffff;
            border-radius: 0.75rem;
            box-shadow: 0 20px 35px rgba(15, 23, 42, 0.12);
            padding: 0.5rem;
            display: none;
            z-index: 20;
        }
        .user-dropdown.show {
            display: block;
        }
        .user-dropdown a,
        .user-dropdown button {
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: flex-start;
            gap: 0.5rem;
            padding: 0.65rem 0.75rem;
            border-radius: 0.55rem;
            border: none;
            background: transparent;
            color: #0f172a;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }
        .user-dropdown a:hover,
        .user-dropdown button:hover {
            background: #eff6ff;
        }
        .user-dropdown form {
            margin: 0;
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
        .menu-trigger {
            width: 42px;
            height: 42px;
            border-radius: 999px;
            border: none;
            background: #e2e8f0;
            color: #0f172a;
            font-size: 1.5rem;
            line-height: 1;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background 0.15s ease, box-shadow 0.15s ease;
        }
        .menu-trigger:hover {
            background: #cbd5f5;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.12);
        }
        .menu-panel {
            position: absolute;
            right: 0;
            top: calc(100% + 0.5rem);
            background: #ffffff;
            border-radius: 0.75rem;
            box-shadow: 0 20px 35px rgba(15, 23, 42, 0.12);
            padding: 0.5rem;
            min-width: 170px;
            display: none;
            z-index: 999;
        }
        .table-actions {
            position: relative;
            overflow: visible !important;
        }
        .menu-panel.show {
            display: block;
        }
        .menu-panel a,
        .menu-panel button {
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: flex-start;
            gap: 0.5rem;
            padding: 0.65rem 0.75rem;
            border-radius: 0.55rem;
            border: none;
            background: transparent;
            color: #0f172a;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }
        .menu-panel a:hover,
        .menu-panel button:hover {
            background: #eff6ff;
        }
        .menu-panel form {
            margin: 0;
        }
        .card {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 20px 35px rgba(15, 23, 42, 0.08);
        }
        .switch-field {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem 1rem;
            background: #f8fafc;
            border: 1px solid #cbd5f5;
            border-radius: 0.75rem;
        }
        .switch-label {
            font-weight: 600;
            color: #1e293b;
        }
        .switch {
            position: relative;
            display: inline-block;
            width: 48px;
            height: 26px;
        }
        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .switch-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: #cbd5f5;
            transition: 0.2s ease;
            border-radius: 999px;
        }
        .switch-slider::before {
            position: absolute;
            content: "";
            height: 22px;
            width: 22px;
            left: 2px;
            bottom: 2px;
            background-color: #ffffff;
            transition: 0.2s ease;
            border-radius: 50%;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.15);
        }
        .switch input:checked + .switch-slider {
            background-color: #2563eb;
        }
        .switch input:checked + .switch-slider::before {
            transform: translateX(22px);
        }
        .switch input:focus + .switch-slider {
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.25);
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
        input[type="text"],
        input[type="tel"],
        textarea,
        select {
            padding: 0.75rem 1rem;
            border-radius: 0.55rem;
            border: 1px solid #cbd5f5;
            font-size: 1rem;
            background: #f8fafc;
            transition: border 0.15s ease, box-shadow 0.15s ease;
        }
        textarea {
            resize: vertical;
        }
        select {
            appearance: none;
            background-image: linear-gradient(45deg, transparent 50%, #2563eb 50%),
                              linear-gradient(135deg, #2563eb 50%, transparent 50%);
            background-position: calc(100% - 20px) 50%, calc(100% - 15px) 50%;
            background-size: 5px 5px, 5px 5px;
            background-repeat: no-repeat;
            padding-right: 2.5rem;
        }
        input:focus,
        textarea:focus,
        select:focus {
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
            .nav-links {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <a class="link" href="{{ route('home') }}">{{ config('app.name', 'Example App') }}</a>

            <div class="nav-links">
                @auth
                    <a class="link" href="{{ route('dashboard') }}">Dashboard</a>
                    <a class="link" href="{{ route('users.index') }}">Usuários</a>
                    <a class="link" href="{{ route('clients.index') }}">Clientes</a>
                @endauth
            </div>

            <div class="nav-actions">
                @auth
                    <div class="nav-user">
                        <button type="button" class="user-toggle" data-user-menu-toggle>
                            {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                        </button>
                        <div class="user-dropdown" data-user-menu>
                            <a href="{{ route('profile.edit') }}">Meu perfil</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit">Sair</button>
                            </form>
                        </div>
                    </div>
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

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const dropdowns = [];

            const registerDropdown = (toggle, menu) => {
                if (!toggle || !menu) {
                    return;
                }

                const parent = menu.parentElement;
                const placeholder = document.createComment('dropdown-placeholder');
                parent.insertBefore(placeholder, menu);

                const state = {
                    toggle,
                    menu,
                    placeholder,
                    parent,
                    isOpen: false,
                    position() {
                        const rect = toggle.getBoundingClientRect();
                        const width = menu.offsetWidth || 180;
                        menu.style.top = `${rect.bottom + 8}px`;
                        menu.style.left = `${rect.right - width}px`;
                    },
                    open() {
                        if (state.isOpen) {
                            return;
                        }

                        document.body.appendChild(menu);
                        menu.classList.add('show');
                        menu.style.position = 'fixed';
                        menu.style.zIndex = '1000';
                        menu.style.right = 'auto';
                        const width = Math.max(menu.offsetWidth || 0, 170);
                        menu.style.minWidth = `${width}px`;
                        state.position();
                        state.isOpen = true;
                    },
                    close() {
                        if (!state.isOpen) {
                            return;
                        }

                        menu.classList.remove('show');
                        ['position', 'zIndex', 'right', 'top', 'left', 'minWidth'].forEach((prop) => {
                            menu.style[prop] = '';
                        });
                        state.placeholder.parentNode?.insertBefore(menu, state.placeholder);
                        state.isOpen = false;
                    },
                };

                toggle.addEventListener('click', (event) => {
                    event.stopPropagation();

                    dropdowns.filter((item) => item !== state).forEach((item) => item.close());

                    if (state.isOpen) {
                        state.close();
                    } else {
                        state.open();
                    }
                });

                dropdowns.push(state);
            };

            const closeAll = () => dropdowns.forEach((item) => item.close());

            document.addEventListener('click', (event) => {
                dropdowns.forEach((state) => {
                    if (
                        state.isOpen &&
                        !state.menu.contains(event.target) &&
                        state.toggle !== event.target
                    ) {
                        state.close();
                    }
                });
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeAll();
                }
            });

            document.addEventListener('scroll', closeAll, true);

            window.addEventListener('resize', () => {
                dropdowns.forEach((state) => {
                    if (state.isOpen) {
                        state.position();
                    }
                });
            });

            registerDropdown(
                document.querySelector('[data-user-menu-toggle]'),
                document.querySelector('[data-user-menu]')
            );

            document.querySelectorAll('[data-menu-toggle]').forEach((toggle) => {
                const menuId = toggle.getAttribute('data-menu-toggle');
                const menu = document.querySelector(`[data-menu-panel="${menuId}"]`);
                registerDropdown(toggle, menu);
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
