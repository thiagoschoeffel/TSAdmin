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
                        <th>Perfil</th>
                        <th>Status</th>
                        <th class="w-24 whitespace-nowrap">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>
                                <a href="#"
                                   class="text-blue-600 transition hover:text-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-500"
                                   role="button"
                                   data-user-details-trigger
                                   data-user-details-url="{{ route('users.modal', $user) }}">
                                    {{ $user->name }}
                                </a>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->role === 'admin' ? 'Administrador' : 'Usuário comum' }}</td>
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
                            <td colspan="5" class="table-empty">Nenhum usuário encontrado.</td>
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

@push('modals')
    <div class="modal hidden" data-modal="user-details" role="dialog" aria-modal="true"
         aria-labelledby="user-details-modal-placeholder-title" aria-hidden="true" hidden>
        <div class="modal__backdrop" data-modal-backdrop></div>

        <div class="modal__panel" role="document">
            <button type="button" class="modal__close" data-modal-close data-modal-autofocus="true"
                    aria-label="Fechar detalhes do usuário">
                <x-heroicon name="x-mark" class="h-5 w-5" />
            </button>

            <div class="modal__body" data-modal-body>
                <div class="modal__empty" data-modal-empty>
                    <x-heroicon name="users" class="h-12 w-12 text-slate-300" />
                    <div class="space-y-1">
                        <h2 id="user-details-modal-placeholder-title" class="text-base font-semibold text-slate-900">
                            Detalhes do usuário
                        </h2>
                        <p>Selecione um usuário na lista para visualizar os dados completos.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endpush
