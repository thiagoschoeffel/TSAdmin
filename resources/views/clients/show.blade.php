@extends('layouts.app')

@section('title', 'Detalhes do cliente')

@section('content')
    <section class="card" style="display:grid;gap:1.5rem;">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;">
            <div>
                <h1 style="font-size:1.75rem;margin:0;">{{ $client->name }}</h1>
                <p style="color:#64748b;margin-top:0.5rem;">{{ $client->person_type === 'company' ? 'Pessoa jurídica' : 'Pessoa física' }} • Documento {{ $client->formattedDocument() }}</p>
            </div>
            <div style="display:flex;gap:0.75rem;flex-wrap:wrap;">
                <a class="button-link" href="{{ route('clients.edit', $client) }}">Editar</a>
                <form method="POST" action="{{ route('clients.destroy', $client) }}" onsubmit="return confirm('Deseja realmente remover este cliente?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" style="background:#ef4444;">Excluir</button>
                </form>
                <a class="link" href="{{ route('clients.index') }}">Voltar</a>
            </div>
        </div>

        @if (session('status'))
            <div class="status">{{ session('status') }}</div>
        @endif

        <section style="display:grid;gap:1rem;">
            <h2 style="font-size:1.2rem;margin:0;">Informações gerais</h2>
            <dl style="display:grid;gap:0.75rem;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));margin:0;">
                <div>
                    <dt style="font-weight:600;color:#475569;">CPF/CNPJ</dt>
                    <dd style="margin:0;color:#0f172a;">{{ $client->formattedDocument() }}</dd>
                </div>
                <div>
                    <dt style="font-weight:600;color:#475569;">Observações</dt>
                    <dd style="margin:0;color:#0f172a;">{{ $client->observations ?: '—' }}</dd>
                </div>
                <div>
                    <dt style="font-weight:600;color:#475569;">Status</dt>
                    <dd style="margin:0;">
                        <span style="display:inline-flex;align-items:center;gap:0.4rem;padding:0.2rem 0.7rem;border-radius:999px;font-size:0.9rem;font-weight:600;background:{{ $client->status === 'active' ? '#dcfce7' : '#fee2e2' }};color:{{ $client->status === 'active' ? '#166534' : '#991b1b' }};">
                            {{ $client->status === 'active' ? 'Ativo' : 'Inativo' }}
                        </span>
                    </dd>
                </div>
            </dl>
        </section>

        <section style="display:grid;gap:1rem;">
            <h2 style="font-size:1.2rem;margin:0;">Endereço</h2>
            <dl style="display:grid;gap:0.75rem;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));margin:0;">
                <div>
                    <dt style="font-weight:600;color:#475569;">CEP</dt>
                    <dd style="margin:0;color:#0f172a;">{{ $client->formattedPostalCode() }}</dd>
                </div>
                <div>
                    <dt style="font-weight:600;color:#475569;">Logradouro</dt>
                    <dd style="margin:0;color:#0f172a;">{{ $client->address }}, nº {{ $client->address_number }}</dd>
                </div>
                <div>
                    <dt style="font-weight:600;color:#475569;">Complemento</dt>
                    <dd style="margin:0;color:#0f172a;">{{ $client->address_complement ?: '—' }}</dd>
                </div>
                <div>
                    <dt style="font-weight:600;color:#475569;">Bairro</dt>
                    <dd style="margin:0;color:#0f172a;">{{ $client->neighborhood }}</dd>
                </div>
                <div>
                    <dt style="font-weight:600;color:#475569;">Cidade/UF</dt>
                    <dd style="margin:0;color:#0f172a;">{{ $client->city }}/{{ $client->state }}</dd>
                </div>
            </dl>
        </section>

        <section style="display:grid;gap:1rem;">
            <h2 style="font-size:1.2rem;margin:0;">Contato</h2>
            <dl style="display:grid;gap:0.75rem;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));margin:0;">
                <div>
                    <dt style="font-weight:600;color:#475569;">Nome do contato</dt>
                    <dd style="margin:0;color:#0f172a;">{{ $client->contact_name ?: '—' }}</dd>
                </div>
                <div>
                    <dt style="font-weight:600;color:#475569;">Telefone principal</dt>
                    <dd style="margin:0;color:#0f172a;">{{ $client->formattedPhone($client->contact_phone_primary) ?: '—' }}</dd>
                </div>
                <div>
                    <dt style="font-weight:600;color:#475569;">Telefone secundário</dt>
                    <dd style="margin:0;color:#0f172a;">{{ $client->formattedPhone($client->contact_phone_secondary) ?: '—' }}</dd>
                </div>
                <div>
                    <dt style="font-weight:600;color:#475569;">E-mail</dt>
                    <dd style="margin:0;color:#0f172a;">{{ $client->contact_email ?: '—' }}</dd>
                </div>
            </dl>
        </section>

        <section style="display:grid;gap:0.75rem;">
            <h2 style="font-size:1.2rem;margin:0;">Auditoria</h2>
            <dl style="display:grid;gap:0.75rem;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));margin:0;">
                <div>
                    <dt style="font-weight:600;color:#475569;">Cadastrado em</dt>
                    <dd style="margin:0;color:#0f172a;">{{ $client->created_at->format('d/m/Y H:i') }}</dd>
                </div>
                <div>
                    <dt style="font-weight:600;color:#475569;">Por</dt>
                    <dd style="margin:0;color:#0f172a;">{{ $client->createdBy?->name ?? 'Conta removida' }}</dd>
                </div>
                <div>
                    <dt style="font-weight:600;color:#475569;">Última atualização</dt>
                    <dd style="margin:0;color:#0f172a;">{{ $client->updated_at->format('d/m/Y H:i') }}</dd>
                </div>
                <div>
                    <dt style="font-weight:600;color:#475569;">Por</dt>
                    <dd style="margin:0;color:#0f172a;">{{ $client->updatedBy?->name ?? 'Nunca atualizado' }}</dd>
                </div>
            </dl>
        </section>
    </section>
@endsection
