@extends('layouts.app')

@section('title', 'Página não encontrada')

@section('content')
    <section class="card mx-auto max-w-2xl space-y-6 text-center">
        <div class="space-y-2">
            <h1 class="text-2xl font-semibold text-slate-900">Página não encontrada</h1>
            <p class="text-sm text-slate-600">
                O recurso solicitado não foi localizado ou pode ter sido movido.
            </p>
        </div>

        <div class="flex flex-wrap justify-center gap-3">
            <a href="{{ route('dashboard') }}" class="btn-primary">
                <span>Ir para o dashboard</span>
            </a>
        </div>
    </section>
@endsection
