@extends('layouts.app')

@section('title', 'Editar usuário')

@section('content')
    <section class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;gap:1rem;flex-wrap:wrap;">
            <div>
                <h1 style="font-size:1.75rem;margin:0;">Editar usuário</h1>
                <p style="color:#64748b;margin-top:0.5rem;">Atualize as informações de {{ $user->name }}.</p>
            </div>
            <a class="link" href="{{ route('users.index') }}">Voltar para lista</a>
        </div>

        @if ($errors->any())
            <div class="status" style="background:#fee2e2;color:#991b1b;border-color:#fecaca;">
                <strong>Ops!</strong> Verifique os campos sinalizados abaixo.
            </div>
        @endif

        <form method="POST" action="{{ route('users.update', $user) }}" style="display:grid;gap:1.5rem;">
            @csrf
            @method('PATCH')

            @include('users._form', ['user' => $user])

            <div style="display:flex;gap:1rem;flex-wrap:wrap;">
                <button type="submit">Salvar alterações</button>
                <a class="link" href="{{ route('users.index') }}">Cancelar</a>
            </div>
        </form>
    </section>
@endsection
