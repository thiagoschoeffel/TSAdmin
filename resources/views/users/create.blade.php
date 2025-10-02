@extends('layouts.app')

@section('title', 'Novo usuário')

@section('content')
    <section class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;gap:1rem;flex-wrap:wrap;">
            <div>
                <h1 style="font-size:1.75rem;margin:0;">Cadastrar usuário</h1>
                <p style="color:#64748b;margin-top:0.5rem;">Inclua os dados do novo membro para liberar o acesso ao sistema.</p>
            </div>
            <a class="link" href="{{ route('users.index') }}">Voltar para lista</a>
        </div>

        @if ($errors->any())
            <div class="status" style="background:#fee2e2;color:#991b1b;border-color:#fecaca;">
                <strong>Ops!</strong> Verifique os campos sinalizados abaixo.
            </div>
        @endif

        <form method="POST" action="{{ route('users.store') }}" style="display:grid;gap:1.5rem;">
            @csrf

            @include('users._form', ['requirePassword' => true])

            <div style="display:flex;gap:1rem;flex-wrap:wrap;">
                <button type="submit">Salvar usuário</button>
                <a class="link" href="{{ route('users.index') }}">Cancelar</a>
            </div>
        </form>
    </section>
@endsection
