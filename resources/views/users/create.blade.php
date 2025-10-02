@extends('layouts.app')

@section('title', 'Novo usuário')

@section('content')
    <section class="card space-y-8">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Cadastrar usuário</h1>
                <p class="mt-2 text-sm text-slate-500">Inclua os dados do novo membro para liberar o acesso ao sistema.</p>
            </div>
            <a class="btn-ghost" href="{{ route('users.index') }}">Voltar para lista</a>
        </div>

        @if ($errors->any())
            <div class="status status-danger">
                <strong class="font-semibold">Ops!</strong> Verifique os campos sinalizados abaixo.
            </div>
        @endif

        <form method="POST" action="{{ route('users.store') }}" class="space-y-6">
            @csrf

            @include('users._form', ['requirePassword' => true])

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="btn-primary">Salvar usuário</button>
                <a class="btn-ghost" href="{{ route('users.index') }}">Cancelar</a>
            </div>
        </form>
    </section>
@endsection

@push('scripts')
    <script>
        const statusToggle = document.querySelector('[data-status-toggle]');
        const statusLabel = document.querySelector('[data-status-label]');

        const updateStatusLabel = () => {
            if (!statusLabel || !statusToggle) {
                return;
            }

            if (statusToggle.checked) {
                statusLabel.classList.remove('inactive');
                statusLabel.textContent = 'Ativo';
            } else {
                statusLabel.classList.add('inactive');
                statusLabel.textContent = 'Inativo';
            }
        };

        updateStatusLabel();
        statusToggle?.addEventListener('change', updateStatusLabel);
    </script>
@endpush
