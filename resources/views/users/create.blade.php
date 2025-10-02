@extends('layouts.app')

@section('title', 'Novo usuário')

@section('content')
    <section class="card" style="max-width:520px;margin:0 auto;">
        <h1 style="font-size:1.75rem;margin-bottom:1rem;">Cadastrar novo usuário</h1>
        <p style="margin-bottom:1.5rem;color:#64748b;">Preencha as informações abaixo para adicionar um novo usuário ao sistema.</p>

        <form method="POST" action="{{ route('users.store') }}">
            @csrf

            <label>
                Nome
                <input type="text" name="name" value="{{ old('name') }}" required autocomplete="name">
                @error('name')
                    <span class="error">{{ $message }}</span>
                @enderror
            </label>

            <label>
                E-mail
                <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
                @error('email')
                    <span class="error">{{ $message }}</span>
                @enderror
            </label>

            <label>
                Senha
                <input type="password" name="password" required autocomplete="new-password">
                @error('password')
                    <span class="error">{{ $message }}</span>
                @enderror
            </label>

            <label>
                Confirmar senha
                <input type="password" name="password_confirmation" required autocomplete="new-password">
            </label>

            <div style="display:flex;gap:1rem;">
                <button type="submit">Criar usuário</button>
                <a class="link" href="{{ route('users.index') }}">Cancelar</a>
            </div>
        </form>
    </section>
@endsection
