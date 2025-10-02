@extends('layouts.app')

@section('title', 'Editar usuário')

@section('content')
    <section class="card" style="max-width:520px;margin:0 auto;">
        <h1 style="font-size:1.75rem;margin-bottom:1rem;">Editar usuário</h1>
        <p style="margin-bottom:1.5rem;color:#64748b;">Atualize os dados de {{ $user->name }}.</p>

        <form method="POST" action="{{ route('users.update', $user) }}">
            @csrf
            @method('PATCH')

            <label>
                Nome
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
                @error('name')
                    <span class="error">{{ $message }}</span>
                @enderror
            </label>

            <label>
                E-mail
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
                @error('email')
                    <span class="error">{{ $message }}</span>
                @enderror
            </label>

            <hr style="border:none;border-top:1px solid #e2e8f0;">

            <label>
                Nova senha
                <input type="password" name="password" autocomplete="new-password">
                @error('password')
                    <span class="error">{{ $message }}</span>
                @enderror
            </label>

            <label>
                Confirmar senha
                <input type="password" name="password_confirmation" autocomplete="new-password">
            </label>

            <button type="submit">Salvar alterações</button>
        </form>
    </section>
@endsection
