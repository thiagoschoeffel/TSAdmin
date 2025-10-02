@extends('layouts.app')

@section('title', 'Clientes')

@section('content')
    <section class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;margin-bottom:1.5rem;">
            <div>
                <h1 style="font-size:1.75rem;margin:0;">Clientes</h1>
                <p style="color:#64748b;margin-top:0.5rem;">Gerencie os cadastros de clientes existentes ou adicione novos registros.</p>
            </div>
            <a class="button-link" href="{{ route('clients.create') }}">Novo cliente</a>
        </div>

        <form method="GET" action="{{ route('clients.index') }}" style="display:grid;gap:0.75rem;margin-bottom:1.5rem;">
            <div style="display:grid;gap:0.75rem;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));">
                <label>
                    Buscar por nome ou documento
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Digite para buscar">
                </label>
                <label>
                    Tipo de pessoa
                    <select name="person_type">
                        <option value="">Todos</option>
                        <option value="individual" {{ request('person_type') === 'individual' ? 'selected' : '' }}>Pessoa Física</option>
                        <option value="company" {{ request('person_type') === 'company' ? 'selected' : '' }}>Pessoa Jurídica</option>
                    </select>
                </label>
                <label>
                    Status
                    <select name="status">
                        <option value="">Todos</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Ativos</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inativos</option>
                    </select>
                </label>
            </div>
            <div style="display:flex;gap:1rem;">
                <button type="submit" style="width:auto;">Filtrar</button>
                <a class="link" href="{{ route('clients.index') }}">Limpar filtros</a>
            </div>
        </form>

        @if (session('status'))
            <div class="status">{{ session('status') }}</div>
        @endif

        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="text-align:left;color:#475569;border-bottom:2px solid #e2e8f0;">
                        <th style="padding:0.75rem 0.5rem;">Nome</th>
                        <th style="padding:0.75rem 0.5rem;">Tipo</th>
                        <th style="padding:0.75rem 0.5rem;">Status</th>
                        <th style="padding:0.75rem 0.5rem;">Documento</th>
                        <th style="padding:0.75rem 0.5rem;">Cidade/UF</th>
                        <th style="padding:0.75rem 0.5rem;">Cadastrado em</th>
                        <th style="padding:0.75rem 0.5rem;width:180px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($clients as $client)
                        <tr style="border-bottom:1px solid #e2e8f0;">
                            <td style="padding:0.75rem 0.5rem;">{{ $client->name }}</td>
                            <td style="padding:0.75rem 0.5rem;">{{ $client->person_type === 'company' ? 'Jurídica' : 'Física' }}</td>
                            <td style="padding:0.75rem 0.5rem;">
                                <span style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.2rem 0.7rem;border-radius:999px;font-size:0.9rem;font-weight:600;background:{{ $client->status === 'active' ? '#dcfce7' : '#fee2e2' }};color:{{ $client->status === 'active' ? '#166534' : '#991b1b' }};">
                                    {{ $client->status === 'active' ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td style="padding:0.75rem 0.5rem;">{{ $client->formattedDocument() }}</td>
                            <td style="padding:0.75rem 0.5rem;">{{ $client->city }}/{{ $client->state }}</td>
                            <td style="padding:0.75rem 0.5rem;">{{ $client->created_at->format('d/m/Y H:i') }}</td>
                            <td style="padding:0.75rem 0.5rem;display:flex;gap:0.5rem;flex-wrap:wrap;">
                                <a class="button-link" style="padding:0.5rem 0.9rem;font-size:0.9rem;" href="{{ route('clients.show', $client) }}">Detalhes</a>
                                <a class="link" href="{{ route('clients.edit', $client) }}">Editar</a>
                                <form method="POST" action="{{ route('clients.destroy', $client) }}" onsubmit="return confirm('Deseja realmente remover este cliente?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background:#ef4444;">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding:1.5rem;text-align:center;color:#64748b;">Nenhum cliente cadastrado até o momento.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:1.5rem;">
            {{ $clients->links() }}
        </div>
    </section>
@endsection
