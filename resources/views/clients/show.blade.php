@extends('layouts.app')

@section('title', 'Detalhes do cliente')

@section('content')
    <section class="card space-y-8">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">{{ $client->name }}</h1>
                <p class="mt-2 text-sm text-slate-500">
                    {{ $client->person_type === 'company' ? 'Pessoa jurídica' : 'Pessoa física' }} • Documento {{ $client->formattedDocument() }}
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                @if (auth()->user()->canManage('clients', 'update'))
                    <a class="btn-secondary" href="{{ route('clients.edit', $client) }}">Editar</a>
                @endif
                @if (auth()->user()->canManage('clients', 'delete'))
                    <form method="POST" action="{{ route('clients.destroy', $client) }}"
                          data-confirm
                          data-confirm-title="Excluir cliente"
                          data-confirm-message="Deseja realmente remover {{ $client->name }}?"
                          data-confirm-confirm-text="Excluir"
                          data-confirm-variant="danger">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger">Excluir</button>
                    </form>
                @endif
                <a class="btn-ghost" href="{{ route('clients.index') }}">Voltar</a>
            </div>
        </div>

        @if (session('status'))
            <div class="status">{{ session('status') }}</div>
        @endif

        @include('clients.partials.details-sections', ['client' => $client])
    </section>
@endsection
