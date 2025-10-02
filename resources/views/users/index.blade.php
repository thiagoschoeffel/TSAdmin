@extends('layouts.app')

@section('title', 'Usuários')

@section('content')
    <section class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;gap:1rem;flex-wrap:wrap;">
            <div>
                <h1 style="font-size:1.75rem;margin:0;">Usuários cadastrados</h1>
                <p style="color:#64748b; margin-top:0.5rem;">Gerencie os usuários do sistema ou cadastre novos membros.</p>
            </div>
            <a class="button-link" href="{{ route('users.create') }}" style="padding:0.6rem 1.1rem;">Novo usuário</a>
        </div>

        <form method="GET" action="{{ route('users.index') }}" style="display:grid;gap:0.75rem;margin-bottom:1.5rem;">
            <div style="display:grid;gap:0.75rem;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));">
                <label>
                    Buscar por nome ou e-mail
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Digite para buscar">
                </label>
            </div>
            <div style="display:flex;gap:1rem;">
                <button type="submit" style="width:auto;">Filtrar</button>
                <a class="link" href="{{ route('users.index') }}">Limpar filtros</a>
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
                        <th style="padding:0.75rem 0.5rem;">E-mail</th>
                        <th style="padding:0.75rem 0.5rem;width:160px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr style="border-bottom:1px solid #e2e8f0;">
                            <td style="padding:0.75rem 0.5rem;">{{ $user->name }}</td>
                            <td style="padding:0.75rem 0.5rem;">{{ $user->email }}</td>
                            <td style="padding:0.75rem 0.5rem;display:flex;gap:0.5rem;flex-wrap:wrap;">
                                @if ($user->id !== auth()->id())
                                    <a class="button-link" style="padding:0.5rem 0.9rem;font-size:0.9rem;" href="{{ route('users.edit', $user) }}">Editar</a>
                                    <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Tem certeza que deseja remover este usuário?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="background:#ef4444;">Excluir</button>
                                    </form>
                                @else
                                    <a class="button-link" style="padding:0.5rem 0.9rem;font-size:0.9rem;background:#1e293b;" href="{{ route('profile.edit') }}">Gerenciar conta</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="padding:1.5rem;text-align:center;color:#64748b;">Nenhum usuário encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:1.5rem;">
            {{ $users->links() }}
        </div>
    </section>
@endsection
