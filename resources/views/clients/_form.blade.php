@php
    $client = $client ?? null;
    $states = [
        'AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO',
    ];

    $selectedType = old('person_type', $client->person_type ?? 'individual');
    $statusIsActive = old('status', $client->status ?? 'active') === 'active';
@endphp

<div class="space-y-6">
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <label class="form-label">
            Nome
            <input type="text" name="name" value="{{ old('name', $client->name ?? '') }}" required class="form-input">
            @error('name')
                <span class="text-sm font-medium text-rose-600">{{ $message }}</span>
            @enderror
        </label>

        <label class="form-label">
            Tipo de pessoa
            <select name="person_type" id="person_type" required class="form-select">
                <option value="individual" {{ $selectedType === 'individual' ? 'selected' : '' }}>Pessoa Física</option>
                <option value="company" {{ $selectedType === 'company' ? 'selected' : '' }}>Pessoa Jurídica</option>
            </select>
            @error('person_type')
                <span class="text-sm font-medium text-rose-600">{{ $message }}</span>
            @enderror
        </label>

        <label class="form-label">
            CPF/CNPJ
            <input type="text" name="document" id="document" value="{{ old('document', isset($client) ? $client->formattedDocument() : '') }}" required class="form-input">
            @error('document')
                <span class="text-sm font-medium text-rose-600">{{ $message }}</span>
            @enderror
        </label>

        <div class="switch-field sm:col-span-2 lg:col-span-3" data-status-control>
            <span class="switch-label">Status do cliente</span>
            <label class="relative inline-flex h-7 w-12 cursor-pointer items-center">
                <input type="hidden" name="status" value="inactive">
                <input type="checkbox" name="status" value="active" data-status-toggle class="peer sr-only" {{ $statusIsActive ? 'checked' : '' }}>
                <span class="pointer-events-none block h-full w-full rounded-full bg-slate-300 transition peer-checked:bg-blue-600 peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-blue-500/60"></span>
                <span class="pointer-events-none absolute left-1 h-5 w-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
            </label>
            <span
                class="switch-status {{ $statusIsActive ? '' : 'inactive' }}"
                data-status-label
                data-status-active="Ativo"
                data-status-inactive="Inativo"
            >
                {{ $statusIsActive ? 'Ativo' : 'Inativo' }}
            </span>
        </div>
        @error('status')
            <span class="text-sm font-medium text-rose-600 sm:col-span-2 lg:col-span-3">{{ $message }}</span>
        @enderror
    </div>

    <label class="form-label">
        Observações
        <textarea name="observations" rows="4" class="form-textarea">{{ old('observations', $client->observations ?? '') }}</textarea>
        @error('observations')
            <span class="text-sm font-medium text-rose-600">{{ $message }}</span>
        @enderror
    </label>

    <fieldset class="space-y-3">
        <legend class="text-sm font-semibold text-slate-700">Endereço</legend>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <label class="form-label">
                CEP
                <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code', isset($client) ? $client->formattedPostalCode() : '') }}" required class="form-input">
                @error('postal_code')
                    <span class="text-sm font-medium text-rose-600">{{ $message }}</span>
                @enderror
            </label>
            <label class="form-label">
                Logradouro
                <input type="text" name="address" value="{{ old('address', $client->address ?? '') }}" required class="form-input">
                @error('address')
                    <span class="text-sm font-medium text-rose-600">{{ $message }}</span>
                @enderror
            </label>
            <label class="form-label">
                Número
                <input type="text" name="address_number" value="{{ old('address_number', $client->address_number ?? '') }}" required class="form-input">
                @error('address_number')
                    <span class="text-sm font-medium text-rose-600">{{ $message }}</span>
                @enderror
            </label>
            <label class="form-label">
                Complemento
                <input type="text" name="address_complement" value="{{ old('address_complement', $client->address_complement ?? '') }}" class="form-input">
                @error('address_complement')
                    <span class="text-sm font-medium text-rose-600">{{ $message }}</span>
                @enderror
            </label>
            <label class="form-label">
                Bairro
                <input type="text" name="neighborhood" value="{{ old('neighborhood', $client->neighborhood ?? '') }}" required class="form-input">
                @error('neighborhood')
                    <span class="text-sm font-medium text-rose-600">{{ $message }}</span>
                @enderror
            </label>
            <label class="form-label">
                Cidade
                <input type="text" name="city" value="{{ old('city', $client->city ?? '') }}" required class="form-input">
                @error('city')
                    <span class="text-sm font-medium text-rose-600">{{ $message }}</span>
                @enderror
            </label>
            <label class="form-label">
                Estado (UF)
                <select name="state" required class="form-select">
                    <option value="">Selecione</option>
                    @foreach ($states as $state)
                        <option value="{{ $state }}" {{ old('state', $client->state ?? '') === $state ? 'selected' : '' }}>{{ $state }}</option>
                    @endforeach
                </select>
                @error('state')
                    <span class="text-sm font-medium text-rose-600">{{ $message }}</span>
                @enderror
            </label>
        </div>
    </fieldset>

    <fieldset class="space-y-3">
        <legend class="text-sm font-semibold text-slate-700">Contato</legend>
        <div class="grid gap-4 sm:grid-cols-2" id="contact_fields">
            <label class="form-label" data-company-field>
                Nome do contato
                <input type="text" name="contact_name" value="{{ old('contact_name', $client->contact_name ?? '') }}" class="form-input">
                @error('contact_name')
                    <span class="text-sm font-medium text-rose-600">{{ $message }}</span>
                @enderror
            </label>
            <label class="form-label" data-company-field>
                Telefone principal
                <input type="text" name="contact_phone_primary" id="contact_phone_primary" value="{{ old('contact_phone_primary', isset($client) ? $client->formattedPhone($client->contact_phone_primary) : '') }}" class="form-input">
                @error('contact_phone_primary')
                    <span class="text-sm font-medium text-rose-600">{{ $message }}</span>
                @enderror
            </label>
            <label class="form-label" data-company-field>
                Telefone secundário
                <input type="text" name="contact_phone_secondary" id="contact_phone_secondary" value="{{ old('contact_phone_secondary', isset($client) ? $client->formattedPhone($client->contact_phone_secondary) : '') }}" class="form-input">
                @error('contact_phone_secondary')
                    <span class="text-sm font-medium text-rose-600">{{ $message }}</span>
                @enderror
            </label>
            <label class="form-label" data-company-field>
                E-mail
                <input type="email" name="contact_email" value="{{ old('contact_email', $client->contact_email ?? '') }}" class="form-input">
                @error('contact_email')
                    <span class="text-sm font-medium text-rose-600">{{ $message }}</span>
                @enderror
            </label>
        </div>
    </fieldset>
</div>
