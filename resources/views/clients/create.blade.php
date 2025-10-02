@extends('layouts.app')

@section('title', 'Novo cliente')

@section('content')
    <section class="card space-y-8">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Cadastrar cliente</h1>
                <p class="mt-2 text-sm text-slate-500">Preencha os dados para registrar um novo cliente.</p>
            </div>
            <a class="btn-ghost" href="{{ route('clients.index') }}">Voltar para lista</a>
        </div>

        @if ($errors->any())
            <div class="status status-danger">
                <strong class="font-semibold">Ops!</strong> Verifique os campos destacados abaixo.
            </div>
        @endif

        <form method="POST" action="{{ route('clients.store') }}" id="client_form" class="space-y-6" data-client-form>
            @csrf

            @include('clients._form')

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="btn-primary">Salvar cliente</button>
                <a class="btn-ghost" href="{{ route('clients.index') }}">Cancelar</a>
            </div>
        </form>
    </section>
@endsection
