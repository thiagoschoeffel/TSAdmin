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
                <a class="btn-secondary" href="{{ route('clients.edit', $client) }}">Editar</a>
                <form method="POST" action="{{ route('clients.destroy', $client) }}" onsubmit="return confirm('Deseja realmente remover este cliente?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger">Excluir</button>
                </form>
                <a class="btn-ghost" href="{{ route('clients.index') }}">Voltar</a>
            </div>
        </div>

        @if (session('status'))
            <div class="status">{{ session('status') }}</div>
        @endif

        <section class="space-y-3">
            <h2 class="text-lg font-semibold text-slate-900">Informações gerais</h2>
            <dl class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-1">
                    <dt class="text-sm font-semibold text-slate-500">CPF/CNPJ</dt>
                    <dd class="text-sm text-slate-800">{{ $client->formattedDocument() }}</dd>
                </div>
                <div class="space-y-1">
                    <dt class="text-sm font-semibold text-slate-500">Observações</dt>
                    <dd class="text-sm text-slate-800">{{ $client->observations ?: '—' }}</dd>
                </div>
                <div class="space-y-1">
                    <dt class="text-sm font-semibold text-slate-500">Status</dt>
                    <dd>
                        <span class="{{ $client->status === 'active' ? 'badge-success' : 'badge-danger' }}">
                            {{ $client->status === 'active' ? 'Ativo' : 'Inativo' }}
                        </span>
                    </dd>
                </div>
            </dl>
        </section>

        <section class="space-y-3">
            <h2 class="text-lg font-semibold text-slate-900">Endereço</h2>
            <dl class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-1">
                    <dt class="text-sm font-semibold text-slate-500">CEP</dt>
                    <dd class="text-sm text-slate-800">{{ $client->formattedPostalCode() }}</dd>
                </div>
                <div class="space-y-1">
                    <dt class="text-sm font-semibold text-slate-500">Logradouro</dt>
                    <dd class="text-sm text-slate-800">{{ $client->address }}, nº {{ $client->address_number }}</dd>
                </div>
                <div class="space-y-1">
                    <dt class="text-sm font-semibold text-slate-500">Complemento</dt>
                    <dd class="text-sm text-slate-800">{{ $client->address_complement ?: '—' }}</dd>
                </div>
                <div class="space-y-1">
                    <dt class="text-sm font-semibold text-slate-500">Bairro</dt>
                    <dd class="text-sm text-slate-800">{{ $client->neighborhood }}</dd>
                </div>
                <div class="space-y-1">
                    <dt class="text-sm font-semibold text-slate-500">Cidade/UF</dt>
                    <dd class="text-sm text-slate-800">{{ $client->city }}/{{ $client->state }}</dd>
                </div>
            </dl>
        </section>

        <section class="space-y-3">
            <h2 class="text-lg font-semibold text-slate-900">Contato</h2>
            <dl class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-1">
                    <dt class="text-sm font-semibold text-slate-500">Nome do contato</dt>
                    <dd class="text-sm text-slate-800">{{ $client->contact_name ?: '—' }}</dd>
                </div>
                <div class="space-y-1">
                    <dt class="text-sm font-semibold text-slate-500">Telefone principal</dt>
                    <dd class="text-sm text-slate-800">{{ $client->formattedPhone($client->contact_phone_primary) ?: '—' }}</dd>
                </div>
                <div class="space-y-1">
                    <dt class="text-sm font-semibold text-slate-500">Telefone secundário</dt>
                    <dd class="text-sm text-slate-800">{{ $client->formattedPhone($client->contact_phone_secondary) ?: '—' }}</dd>
                </div>
                <div class="space-y-1">
                    <dt class="text-sm font-semibold text-slate-500">E-mail</dt>
                    <dd class="text-sm text-slate-800">{{ $client->contact_email ?: '—' }}</dd>
                </div>
            </dl>
        </section>

        <section class="space-y-3">
            <h2 class="text-lg font-semibold text-slate-900">Auditoria</h2>
            <dl class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-1">
                    <dt class="text-sm font-semibold text-slate-500">Cadastrado em</dt>
                    <dd class="text-sm text-slate-800">{{ $client->created_at->format('d/m/Y H:i') }}</dd>
                </div>
                <div class="space-y-1">
                    <dt class="text-sm font-semibold text-slate-500">Por</dt>
                    <dd class="text-sm text-slate-800">{{ $client->createdBy?->name ?? 'Conta removida' }}</dd>
                </div>
                <div class="space-y-1">
                    <dt class="text-sm font-semibold text-slate-500">Última atualização</dt>
                    <dd class="text-sm text-slate-800">{{ $client->updated_at->format('d/m/Y H:i') }}</dd>
                </div>
                <div class="space-y-1">
                    <dt class="text-sm font-semibold text-slate-500">Por</dt>
                    <dd class="text-sm text-slate-800">{{ $client->updatedBy?->name ?? 'Nunca atualizado' }}</dd>
                </div>
            </dl>
        </section>
    </section>
@endsection
