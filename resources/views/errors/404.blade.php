@extends('layouts.app')

@section('title', 'Página não encontrada (404)')

@section('content')
    <section class="card mx-auto max-w-xl space-y-6 text-center">
        <div class="flex justify-center">
            <x-heroicon name="exclamation-triangle" class="h-12 w-12 text-amber-500" />
        </div>
        <div class="space-y-2">
            <h1 class="text-2xl font-semibold text-slate-900">Página não encontrada</h1>
            <p class="text-sm text-slate-600">A página que você procura não existe ou foi movida.</p>
        </div>
        <div class="flex justify-center">
            @auth
                <a class="btn-secondary" href="{{ route('dashboard') }}">Ir para o dashboard</a>
            @else
                <a class="btn-secondary" href="{{ route('home') }}">Ir para a página inicial</a>
            @endauth
        </div>
    </section>
@endsection
