<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'Example App'))</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
    <header class="bg-slate-900 text-white">
        <nav class="container-default flex flex-col gap-4 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-6">
                <a class="text-lg font-semibold tracking-tight text-white transition hover:text-blue-200" href="{{ route('home') }}">
                    {{ config('app.name', 'Example App') }}
                </a>

                <div class="flex flex-wrap items-center gap-3 text-sm font-semibold text-slate-200 sm:gap-4">
                    @auth
                        <a class="transition hover:text-white" href="{{ route('dashboard') }}">Dashboard</a>
                        <a class="transition hover:text-white" href="{{ route('users.index') }}">Usuários</a>
                        <a class="transition hover:text-white" href="{{ route('clients.index') }}">Clientes</a>
                    @endauth
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3 sm:justify-end">
                @auth
                    <div class="relative">
                        <button type="button" class="user-toggle" data-user-menu-toggle>
                            {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                        </button>
                        <div class="user-dropdown hidden" data-user-menu data-dropdown-align="end">
                            <a class="dropdown-link" href="{{ route('profile.edit') }}">Meu perfil</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-link-danger">Sair</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a class="text-sm font-semibold text-slate-200 transition hover:text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white" href="{{ route('login') }}">Entrar</a>
                    <a class="btn-inverse" href="{{ route('register') }}">Registrar</a>
                @endauth
            </div>
        </nav>
    </header>

    <main class="container-default flex-1 py-10">
        @yield('content')
    </main>

    <footer class="container-default py-8 text-center text-sm text-slate-500">
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
                parent?.insertBefore(placeholder, menu);

                const align = menu.dataset.dropdownAlign ?? 'start';
                const offset = Number(menu.dataset.dropdownOffset ?? 8);
                const margin = Number.isFinite(offset) ? offset : 8;

                const state = {
                    toggle,
                    menu,
                    placeholder,
                    align,
                    margin,
                    isOpen: false,
                    position() {
                        const rect = toggle.getBoundingClientRect();
                        const width = Math.max(menu.offsetWidth || 0, toggle.offsetWidth || 0);
                        const guard = 16;

                        let left;

                        switch (align) {
                            case 'end':
                                left = rect.right - width;
                                break;
                            case 'center':
                                left = rect.left + rect.width / 2 - width / 2;
                                break;
                            default:
                                left = rect.left;
                        }

                        left = Math.min(
                            Math.max(guard, left),
                            Math.max(guard, window.innerWidth - width - guard)
                        );

                        menu.style.left = `${left}px`;
                        menu.style.top = `${rect.bottom + margin}px`;
                    },
                    open() {
                        if (state.isOpen) {
                            return;
                        }

                        dropdowns.filter((item) => item !== state).forEach((item) => item.close());

                        menu.classList.remove('hidden');
                        document.body.appendChild(menu);
                        menu.style.position = 'fixed';
                        menu.style.zIndex = '1000';
                        menu.style.right = 'auto';

                        const width = Math.max(menu.offsetWidth || 0, toggle.offsetWidth || 0, 170);
                        menu.style.minWidth = `${width}px`;

                        state.position();

                        requestAnimationFrame(() => {
                            menu.classList.add('is-open');
                        });

                        state.isOpen = true;
                    },
                    close() {
                        if (!state.isOpen) {
                            return;
                        }

                        state.isOpen = false;
                        menu.classList.remove('is-open');

                        setTimeout(() => {
                            if (state.isOpen) {
                                return;
                            }

                            menu.classList.add('hidden');
                            ['position', 'zIndex', 'top', 'left', 'right', 'minWidth'].forEach((prop) => {
                                menu.style[prop] = '';
                            });

                            state.placeholder?.parentNode?.insertBefore(menu, state.placeholder);
                        }, 160);
                    },
                };

                toggle.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();

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
                    if (!state.isOpen) {
                        return;
                    }

                    const clickedToggle = state.toggle.contains(event.target);
                    const clickedMenu = state.menu.contains(event.target);

                    if (!clickedToggle && !clickedMenu) {
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
