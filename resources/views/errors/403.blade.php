@extends('layouts.app')

@section('title', 'Acesso restrito')

@section('content')
    <section class="card mx-auto max-w-2xl space-y-6 text-center">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-rose-100 text-rose-600">
            <x-heroicon name="shield-exclamation" class="h-8 w-8" />
        </div>

        <div class="space-y-2">
            <h1 class="text-2xl font-semibold text-slate-900">Acesso restrito</h1>
            <p class="text-sm text-slate-600">
                {{ $exception?->getMessage() ?: 'Você não possui permissão para acessar esta funcionalidade. Caso acredite ser um engano, entre em contato com um administrador.' }}
            </p>
        </div>

        <div class="flex flex-wrap justify-center gap-3">
            <a href="{{ route('dashboard') }}" class="btn-primary">
                <x-heroicon name="chart-bar" class="h-5 w-5" />
                <span>Ir para o dashboard</span>
            </a>
            <a href="{{ url()->previous() }}" class="btn-ghost">
                Voltar
            </a>
        </div>
    </section>
@endsection
