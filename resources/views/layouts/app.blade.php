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
                        <a class="group transition hover:text-white" href="{{ route('dashboard') }}">
                            <span class="inline-flex items-center gap-2">
                                <x-heroicon name="chart-bar" class="h-4 w-4 transition-colors group-hover:text-white" />
                                <span>Dashboard</span>
                            </span>
                        </a>
                        @if (auth()->user()?->isAdmin())
                            <a class="group transition hover:text-white" href="{{ route('users.index') }}">
                                <span class="inline-flex items-center gap-2">
                                    <x-heroicon name="users" class="h-4 w-4 transition-colors group-hover:text-white" />
                                    <span>Usuários</span>
                                </span>
                            </a>
                        @endif
                        <a class="group transition hover:text-white" href="{{ route('clients.index') }}">
                            <span class="inline-flex items-center gap-2">
                                <x-heroicon name="identification" class="h-4 w-4 transition-colors group-hover:text-white" />
                                <span>Clientes</span>
                            </span>
                        </a>
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
                            <a class="dropdown-link" href="{{ route('profile.edit') }}">
                                <x-heroicon name="user-circle" class="h-5 w-5" />
                                <span>Meu perfil</span>
                            </a>
                            <form method="POST" action="{{ route('logout') }}"
                                  data-confirm
                                  data-confirm-title="Sair da conta"
                                  data-confirm-message="Tem certeza que deseja encerrar a sessão?"
                                  data-confirm-confirm-text="Sair"
                                  data-confirm-variant="secondary">
                                @csrf
                                <button type="submit" class="dropdown-link-danger">
                                    <x-heroicon name="arrow-left-end-on-rectangle" class="h-5 w-5" />
                                    <span>Sair</span>
                                </button>
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

    @stack('modals')

    <x-confirm-modal />

    @stack('scripts')
</body>
</html>
