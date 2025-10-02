@extends('layouts.app')

@section('title', 'Editar usuário')

@section('content')
    <section class="card space-y-8">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Editar usuário</h1>
                <p class="mt-2 text-sm text-slate-500">Atualize as informações de {{ $user->name }}.</p>
            </div>
            <a class="btn-ghost" href="{{ route('users.index') }}">Voltar para lista</a>
        </div>

        @if ($errors->any())
            <div class="status status-danger">
                <strong class="font-semibold">Ops!</strong> Verifique os campos sinalizados abaixo.
            </div>
        @endif

        <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-6">
            @csrf
            @method('PATCH')

            @include('users._form', ['user' => $user])

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="btn-primary">Salvar alterações</button>
                <a class="btn-ghost" href="{{ route('users.index') }}">Cancelar</a>
            </div>
        </form>
    </section>
@endsection
