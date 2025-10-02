@extends('layouts.app')

@section('title', 'Cadastrar')

@section('content')
    <section class="card" style="max-width:400px;margin:0 auto;">
        <h1 style="font-size:1.75rem;margin-bottom:1.5rem;">Crie sua conta</h1>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <label>
                Nome
                <input type="text" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
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

            <button type="submit">Cadastrar</button>
        </form>

        <p style="margin-top:1.5rem;text-align:center;">Já possui uma conta?
            <a class="link" href="{{ route('login') }}">Entrar</a>
        </p>
    </section>
@endsection
