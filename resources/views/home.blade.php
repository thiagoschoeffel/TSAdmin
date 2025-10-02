@extends('layouts.app')

@section('title', 'Bem-vindo')

@section('content')
    <section class="card">
        <h1 style="font-size:2rem;margin-bottom:1rem;">Bem-vindo ao painel da aplicação</h1>
        <p style="margin-bottom:1.5rem;max-width:48ch;line-height:1.6;">
            Esta é a área pública do sistema. Faça login para acessar o painel administrativo, onde você poderá ver o conteúdo restrito a usuários autenticados.
        </p>

        @guest
            <div style="display:flex;gap:1rem;flex-wrap:wrap;">
                <a class="button-link" href="{{ route('login') }}">Entrar</a>
                <a class="link" href="{{ route('register') }}">Criar uma conta</a>
            </div>
        @else
            <p style="font-weight:600;">Você já está autenticado. Acesse o seu <a class="link" href="{{ route('dashboard') }}">dashboard</a>.</p>
        @endguest
    </section>
@endsection
