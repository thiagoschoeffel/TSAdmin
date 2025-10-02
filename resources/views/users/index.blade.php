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
                <a class="link" href="{{ route('users.index') }}">Limpar filtros</a>
            </div>
        </form>

        @if (session('status'))
            <div class="status">{{ session('status') }}</div>
        @endif

        <div style="overflow-x:auto;overflow-y:visible;position:relative;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="text-align:left;color:#475569;border-bottom:2px solid #e2e8f0;">
                        <th style="padding:0.75rem 0.5rem;">Nome</th>
                        <th style="padding:0.75rem 0.5rem;">E-mail</th>
                        <th style="padding:0.75rem 0.5rem;">Status</th>
                        <th style="padding:0.75rem 0.5rem;width:80px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr style="border-bottom:1px solid #e2e8f0;">
                            <td style="padding:0.75rem 0.5rem;">{{ $user->name }}</td>
                            <td style="padding:0.75rem 0.5rem;">{{ $user->email }}</td>
                            <td class="table-actions" style="padding:0.75rem 0.5rem;">
                                <span style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.2rem 0.7rem;border-radius:999px;font-size:0.9rem;font-weight:600;background:{{ $user->status === 'active' ? '#dcfce7' : '#fee2e2' }};color:{{ $user->status === 'active' ? '#166534' : '#991b1b' }};">
                                    {{ $user->status === 'active' ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td style="padding:0.75rem 0.5rem;">
                                @if ($user->id !== auth()->id())
                                    @php $menuId = 'user-menu-'.$user->id; @endphp
                                <div style="position:relative;display:inline-block;z-index:10;">
                                    <button type="button" class="menu-trigger" data-menu-toggle="{{ $menuId }}">…</button>
                                    <div class="menu-panel" data-menu-panel="{{ $menuId }}">
                                        <a href="{{ route('users.edit', $user) }}">Editar</a>
                                            <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Tem certeza que deseja remover este usuário?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" style="color:#ef4444;">Excluir</button>
                                            </form>
                                        </div>
                                    </div>
                                @else
                                    <a class="button-link" style="padding:0.5rem 0.9rem;font-size:0.9rem;background:#1e293b;" href="{{ route('profile.edit') }}">Gerenciar conta</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="padding:1.5rem;text-align:center;color:#64748b;">Nenhum usuário encontrado.</td>
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
