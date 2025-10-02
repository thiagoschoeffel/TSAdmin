@extends('layouts.app')

@section('title', 'Entrar')

@section('content')
    <section class="card" style="max-width:400px;margin:0 auto;">
        <h1 style="font-size:1.75rem;margin-bottom:1.5rem;">Acesse sua conta</h1>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <label>
                E-mail
                <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                @error('email')
                    <span class="error">{{ $message }}</span>
                @enderror
            </label>

            <label>
                Senha
                <input type="password" name="password" required autocomplete="current-password">
                @error('password')
                    <span class="error">{{ $message }}</span>
                @enderror
            </label>

            <label style="flex-direction:row;align-items:center;gap:0.5rem;font-weight:500;">
                <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }} style="width:auto;">
                Manter-me conectado
            </label>

            <button type="submit">Entrar</button>
        </form>

        <p style="margin-top:1.5rem;text-align:center;">Ainda não possui conta?
            <a class="link" href="{{ route('register') }}">Cadastre-se</a>
        </p>
    </section>
@endsection
