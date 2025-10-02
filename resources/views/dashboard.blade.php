@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <section class="card space-y-6">
        <h1 class="text-2xl font-semibold text-slate-900">Olá, {{ auth()->user()->name }}!</h1>
        <p class="text-base leading-relaxed text-slate-600">
            Você está na área autenticada do sistema. Utilize este espaço para adicionar os recursos administrativos que preferir.
        </p>
        <div class="flex flex-wrap gap-3">
            <a class="btn-primary" href="{{ route('home') }}">Voltar para a home pública</a>
        </div>
    </section>
@endsection
