@extends('layouts.app')

@section('title', 'Cadastrar')

@section('content')
    <section class="card mx-auto max-w-md space-y-6">
        <h1 class="text-2xl font-semibold text-slate-900">Crie sua conta</h1>

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf

            <label class="form-label">
                Nome
                <input type="text" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus class="form-input">
                @error('name')
                    <span class="text-sm font-medium text-rose-600">{{ $message }}</span>
                @enderror
            </label>

            <label class="form-label">
                E-mail
                <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="form-input">
                @error('email')
                    <span class="text-sm font-medium text-rose-600">{{ $message }}</span>
                @enderror
            </label>

            <label class="form-label">
                Senha
                <input type="password" name="password" required autocomplete="new-password" class="form-input">
                @error('password')
                    <span class="text-sm font-medium text-rose-600">{{ $message }}</span>
                @enderror
            </label>

            <label class="form-label">
                Confirmar senha
                <input type="password" name="password_confirmation" required autocomplete="new-password" class="form-input">
            </label>

            <button type="submit" class="btn-primary w-full justify-center">Cadastrar</button>
        </form>

        <p class="text-center text-sm text-slate-600">
            Já possui uma conta?
            <a class="link" href="{{ route('login') }}">Entrar</a>
        </p>
    </section>
@endsection
