<div class="space-y-6">
    <header class="space-y-2">
        <div class="space-y-1">
            <h2 id="client-details-modal-title" class="text-xl font-semibold text-slate-900">{{ $client->name }}</h2>
            <p class="text-sm text-slate-500">
                {{ $client->person_type === 'company' ? 'Pessoa jurídica' : 'Pessoa física' }} • Documento {{ $client->formattedDocument() }}
            </p>
        </div>
    </header>

    @include('clients.partials.details-sections', ['client' => $client])
</div>
