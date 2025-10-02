@extends('layouts.app')

@section('title', 'Bem-vindo')

@section('content')
    <section class="card space-y-6">
        <h1 class="text-2xl font-semibold text-slate-900">Bem-vindo ao painel da aplicação</h1>
        <p class="max-w-prose text-base leading-relaxed text-slate-600">
            Esta é a área pública do sistema. Faça login para acessar o painel administrativo, onde você poderá ver o conteúdo restrito a usuários autenticados.
        </p>

        @guest
            <div class="flex flex-wrap gap-3">
                <a class="btn-primary" href="{{ route('login') }}">Entrar</a>
                <a class="link" href="{{ route('register') }}">Criar uma conta</a>
            </div>
        @else
            <p class="font-semibold text-slate-700">
                Você já está autenticado. Acesse o seu <a class="link" href="{{ route('dashboard') }}">dashboard</a>.
            </p>
        @endguest
    </section>
@endsection
