@extends('layouts.app')

@section('title', 'Entrar')

@section('content')
    <section class="card mx-auto max-w-md space-y-6">
        <h1 class="text-2xl font-semibold text-slate-900">Acesse sua conta</h1>

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <label class="form-label">
                E-mail
                <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus class="form-input">
                @error('email')
                    <span class="text-sm font-medium text-rose-600">{{ $message }}</span>
                @enderror
            </label>

            <label class="form-label">
                Senha
                <input type="password" name="password" required autocomplete="current-password" class="form-input">
                @error('password')
                    <span class="text-sm font-medium text-rose-600">{{ $message }}</span>
                @enderror
            </label>

            <label class="flex items-center gap-2 text-sm font-medium text-slate-600">
                <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                Manter-me conectado
            </label>

            <button type="submit" class="btn-primary w-full justify-center">Entrar</button>
        </form>

        <p class="text-center text-sm text-slate-600">
            Ainda não possui conta?
            <a class="link" href="{{ route('register') }}">Cadastre-se</a>
        </p>
    </section>
@endsection
