@extends('layouts.app')

@section('title', 'Usuários')

@section('content')
    <section class="card space-y-8">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Usuários</h1>
                <p class="mt-2 text-sm text-slate-500">Gerencie os usuários do sistema ou cadastre novos membros.</p>
            </div>
            <a class="btn-primary" href="{{ route('users.create') }}">Novo usuário</a>
        </div>

        <form method="GET" action="{{ route('users.index') }}" class="space-y-4">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <label class="form-label">
                    Buscar por nome ou e-mail
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Digite para buscar" class="form-input">
                </label>
                <label class="form-label">
                    Status
                    <select name="status" class="form-select">
                        <option value="">Todos</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Ativos</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inativos</option>
                    </select>
                </label>
            </div>
            <div class="flex flex-wrap gap-3">
                <button type="submit" class="btn-primary">
                    <x-heroicon name="funnel" class="h-5 w-5" />
                    <span>Filtrar</span>
                </button>
                <a class="btn-ghost" href="{{ route('users.index') }}">Limpar filtros</a>
            </div>
        </form>

        @if (session('status'))
            <div class="status">{{ session('status') }}</div>
        @endif

        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Status</th>
                        <th class="w-24 whitespace-nowrap">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td class="table-actions">
                                <span class="{{ $user->status === 'active' ? 'badge-success' : 'badge-danger' }}">
                                    {{ $user->status === 'active' ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap">
                                @if ($user->id !== auth()->id())
                                    @php $menuId = 'user-menu-'.$user->id; @endphp
                                    <div class="relative inline-block z-10">
                                        <button type="button" class="menu-trigger" data-menu-toggle="{{ $menuId }}" aria-label="Abrir menu de ações">
                                            <x-heroicon name="ellipsis-horizontal" class="h-5 w-5" />
                                        </button>
                                        <div class="menu-panel hidden" data-menu-panel="{{ $menuId }}" data-dropdown-align="end">
                                            <a class="menu-panel-link" href="{{ route('users.edit', $user) }}">
                                                <x-heroicon name="pencil" class="h-4 w-4" />
                                                <span>Editar</span>
                                            </a>
                                            <form method="POST" action="{{ route('users.destroy', $user) }}"
                                                  data-confirm
                                                  data-confirm-title="Excluir usuário"
                                                  data-confirm-message="Tem certeza que deseja remover {{ $user->name }}?"
                                                  data-confirm-confirm-text="Excluir"
                                                  data-confirm-variant="danger">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="menu-panel-link text-rose-600 hover:text-rose-700">
                                                    <x-heroicon name="trash" class="h-4 w-4" />
                                                    <span>Excluir</span>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @else
                                    <a class="btn-secondary" href="{{ route('profile.edit') }}">Gerenciar conta</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="table-empty">Nenhum usuário encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $users->links() }}
        </div>
    </section>
@endsection
