@extends('layouts.app')

@section('title', 'Editar cliente')

@section('content')
    <section class="card space-y-8">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Editar cliente</h1>
                <p class="mt-2 text-sm text-slate-500">Atualize as informações de {{ $client->name }}.</p>
            </div>
            <a class="btn-ghost" href="{{ route('clients.index') }}">Voltar para lista</a>
        </div>

        @if ($errors->any())
            <div class="status status-danger">
                <strong class="font-semibold">Ops!</strong> Verifique os campos destacados abaixo.
            </div>
        @endif

        <form method="POST" action="{{ route('clients.update', $client) }}" id="client_form" class="space-y-6" data-client-form>
            @csrf
            @method('PATCH')

            @include('clients._form', ['client' => $client])

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="btn-primary">Salvar alterações</button>
                <a class="btn-ghost" href="{{ route('clients.index') }}">Cancelar</a>
            </div>
        </form>
    </section>
@endsection
