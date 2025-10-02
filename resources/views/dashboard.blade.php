@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <section class="card">
        <h1 style="font-size:1.75rem;margin-bottom:1rem;">Olá, {{ auth()->user()->name }}!</h1>
        <p style="line-height:1.6;margin-bottom:1.5rem;">
            Você está na área autenticada do sistema. Utilize este espaço para adicionar os recursos administrativos que preferir.
        </p>
        <div style="display:flex;gap:1rem;flex-wrap:wrap;">
            <a class="button-link" href="{{ route('home') }}">Voltar para a home pública</a>
        </div>
    </section>
@endsection
