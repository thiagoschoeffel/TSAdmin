@extends('layouts.app')

@section('title', 'Clientes')

@section('content')
    <section class="card space-y-8">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Clientes</h1>
                <p class="mt-2 text-sm text-slate-500">Gerencie os cadastros de clientes existentes ou adicione novos registros.</p>
            </div>
            @if (auth()->user()->canManage('clients', 'create'))
                <a class="btn-primary" href="{{ route('clients.create') }}">Novo cliente</a>
            @endif
        </div>

        <form method="GET" action="{{ route('clients.index') }}" class="space-y-4">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <label class="form-label">
                    Buscar por nome ou documento
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Digite para buscar" class="form-input">
                </label>
                <label class="form-label">
                    Tipo de pessoa
                    <select name="person_type" class="form-select">
                        <option value="">Todos</option>
                        <option value="individual" {{ request('person_type') === 'individual' ? 'selected' : '' }}>Pessoa Física</option>
                        <option value="company" {{ request('person_type') === 'company' ? 'selected' : '' }}>Pessoa Jurídica</option>
                    </select>
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
                <a class="btn-ghost" href="{{ route('clients.index') }}">Limpar filtros</a>
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
                        <th>Tipo</th>
                        <th>Status</th>
                        <th>Documento</th>
                        <th>Cidade/UF</th>
                        <th>Cadastrado em</th>
                        <th class="w-24">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($clients as $client)
                        <tr>
                            <td>
                                @if (auth()->user()->canManage('clients', 'view'))
                                <a href="{{ route('clients.show', $client) }}"
                                   class="text-blue-600 transition hover:text-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-500"
                                   data-client-details-trigger
                                   data-client-details-url="{{ route('clients.modal', $client) }}">
                                    {{ $client->name }}
                                </a>
                                @else
                                    {{ $client->name }}
                                @endif
                            </td>
                            <td>{{ $client->person_type === 'company' ? 'Jurídica' : 'Física' }}</td>
                            <td class="table-actions">
                                <span class="{{ $client->status === 'active' ? 'badge-success' : 'badge-danger' }}">
                                    {{ $client->status === 'active' ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td>{{ $client->formattedDocument() }}</td>
                            <td>{{ $client->city }}/{{ $client->state }}</td>
                            <td>{{ $client->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                @php $menuId = 'client-menu-'.$client->id; @endphp
                                <div class="relative inline-block z-10">
                                    <button type="button" class="menu-trigger" data-menu-toggle="{{ $menuId }}" aria-label="Abrir menu de ações">
                                        <x-heroicon name="ellipsis-horizontal" class="h-5 w-5" />
                                    </button>
                                    <div class="menu-panel hidden" data-menu-panel="{{ $menuId }}" data-dropdown-align="end">
                                        @if (auth()->user()->canManage('clients', 'update'))
                                            <a class="menu-panel-link" href="{{ route('clients.edit', $client) }}">
                                                <x-heroicon name="pencil" class="h-4 w-4" />
                                                <span>Editar</span>
                                            </a>
                                        @endif
                                        @if (auth()->user()->canManage('clients', 'delete'))
                                            <form method="POST" action="{{ route('clients.destroy', $client) }}"
                                                  data-confirm
                                                  data-confirm-title="Excluir cliente"
                                                  data-confirm-message="Deseja realmente remover {{ $client->name }}?"
                                                  data-confirm-confirm-text="Excluir"
                                                  data-confirm-variant="danger">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="menu-panel-link text-rose-600 hover:text-rose-700">
                                                    <x-heroicon name="trash" class="h-4 w-4" />
                                                    <span>Excluir</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="table-empty">Nenhum cliente cadastrado até o momento.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $clients->links() }}
        </div>
    </section>
@endsection

@push('modals')
    <div class="modal hidden" data-modal="client-details" role="dialog" aria-modal="true"
         aria-labelledby="client-details-modal-placeholder-title" aria-hidden="true" hidden>
        <div class="modal__backdrop" data-modal-backdrop></div>

        <div class="modal__panel modal__panel--lg" role="document">
            <button type="button" class="modal__close" data-modal-close data-modal-autofocus="true"
                    aria-label="Fechar detalhes do cliente">
                <x-heroicon name="x-mark" class="h-5 w-5" />
            </button>

            <div class="modal__body" data-modal-body>
                <div class="modal__empty" data-modal-empty>
                    <x-heroicon name="user-circle" class="h-12 w-12 text-slate-300" />
                    <div class="space-y-1">
                        <h2 id="client-details-modal-placeholder-title" class="text-base font-semibold text-slate-900">
                            Detalhes do cliente
                        </h2>
                        <p>Selecione um cliente na lista para visualizar os dados completos.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endpush
