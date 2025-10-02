@extends('layouts.app')

@section('title', 'Clientes')

@section('content')
    <section class="card space-y-8">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Clientes</h1>
                <p class="mt-2 text-sm text-slate-500">Gerencie os cadastros de clientes existentes ou adicione novos registros.</p>
            </div>
            <a class="btn-primary" href="{{ route('clients.create') }}">Novo cliente</a>
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
                            <td>{{ $client->name }}</td>
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
                                        <a class="menu-panel-link" href="{{ route('clients.show', $client) }}">
                                            <x-heroicon name="eye" class="h-4 w-4" />
                                            <span>Detalhes</span>
                                        </a>
                                        <a class="menu-panel-link" href="{{ route('clients.edit', $client) }}">
                                            <x-heroicon name="pencil" class="h-4 w-4" />
                                            <span>Editar</span>
                                        </a>
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
