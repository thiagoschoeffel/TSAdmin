@php
    $client = $client ?? null;
    $states = [
        'AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO',
    ];

    $selectedType = old('person_type', $client->person_type ?? 'individual');
@endphp

<div style="display:grid;gap:1.25rem;">
    <div style="display:grid;gap:0.75rem;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));">
        <label>
            Nome
            <input type="text" name="name" value="{{ old('name', $client->name ?? '') }}" required>
            @error('name')
                <span class="error">{{ $message }}</span>
            @enderror
        </label>

        <label>
            Tipo de pessoa
            <select name="person_type" id="person_type" required>
                <option value="individual" {{ $selectedType === 'individual' ? 'selected' : '' }}>Pessoa Física</option>
                <option value="company" {{ $selectedType === 'company' ? 'selected' : '' }}>Pessoa Jurídica</option>
            </select>
            @error('person_type')
                <span class="error">{{ $message }}</span>
            @enderror
        </label>

        <label>
            CPF/CNPJ
            <input type="text" name="document" id="document" value="{{ old('document', isset($client) ? $client->formattedDocument() : '') }}" required>
            @error('document')
                <span class="error">{{ $message }}</span>
            @enderror
        </label>

        <div class="switch-field">
            <span class="switch-label">Status do cliente</span>
            <label class="switch">
                <input type="hidden" name="status" value="inactive">
                <input type="checkbox" name="status" value="active" data-status-toggle {{ old('status', $client->status ?? 'active') === 'active' ? 'checked' : '' }}>
                <span class="switch-slider"></span>
            </label>
            <span style="font-weight:600;color:#2563eb;" data-status-label>{{ old('status', $client->status ?? 'active') === 'active' ? 'Ativo' : 'Inativo' }}</span>
        </div>
        @error('status')
            <span class="error">{{ $message }}</span>
        @enderror
    </div>

    <label>
        Observações
        <textarea name="observations" rows="4">{{ old('observations', $client->observations ?? '') }}</textarea>
        @error('observations')
            <span class="error">{{ $message }}</span>
        @enderror
    </label>

    <fieldset style="border:none;padding:0;display:grid;gap:0.75rem;">
        <legend style="font-weight:700;color:#1e293b;margin-bottom:0.5rem;">Endereço</legend>
        <div style="display:grid;gap:0.75rem;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));">
            <label>
                CEP
                <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code', isset($client) ? $client->formattedPostalCode() : '') }}" required>
                @error('postal_code')
                    <span class="error">{{ $message }}</span>
                @enderror
            </label>
            <label>
                Logradouro
                <input type="text" name="address" value="{{ old('address', $client->address ?? '') }}" required>
                @error('address')
                    <span class="error">{{ $message }}</span>
                @enderror
            </label>
            <label>
                Número
                <input type="text" name="address_number" value="{{ old('address_number', $client->address_number ?? '') }}" required>
                @error('address_number')
                    <span class="error">{{ $message }}</span>
                @enderror
            </label>
            <label>
                Complemento
                <input type="text" name="address_complement" value="{{ old('address_complement', $client->address_complement ?? '') }}">
                @error('address_complement')
                    <span class="error">{{ $message }}</span>
                @enderror
            </label>
            <label>
                Bairro
                <input type="text" name="neighborhood" value="{{ old('neighborhood', $client->neighborhood ?? '') }}" required>
                @error('neighborhood')
                    <span class="error">{{ $message }}</span>
                @enderror
            </label>
            <label>
                Cidade
                <input type="text" name="city" value="{{ old('city', $client->city ?? '') }}" required>
                @error('city')
                    <span class="error">{{ $message }}</span>
                @enderror
            </label>
            <label>
                Estado (UF)
                <select name="state" required>
                    <option value="">Selecione</option>
                    @foreach ($states as $state)
                        <option value="{{ $state }}" {{ old('state', $client->state ?? '') === $state ? 'selected' : '' }}>{{ $state }}</option>
                    @endforeach
                </select>
                @error('state')
                    <span class="error">{{ $message }}</span>
                @enderror
            </label>
        </div>
    </fieldset>

    <fieldset style="border:none;padding:0;display:grid;gap:0.75rem;">
        <legend style="font-weight:700;color:#1e293b;margin-bottom:0.5rem;">Contato</legend>
        <div style="display:grid;gap:0.75rem;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));" id="contact_fields">
            <label data-company-field>
                Nome do contato
                <input type="text" name="contact_name" value="{{ old('contact_name', $client->contact_name ?? '') }}">
                @error('contact_name')
                    <span class="error">{{ $message }}</span>
                @enderror
            </label>
            <label data-company-field>
                Telefone principal
                <input type="text" name="contact_phone_primary" id="contact_phone_primary" value="{{ old('contact_phone_primary', isset($client) ? $client->formattedPhone($client->contact_phone_primary) : '') }}">
                @error('contact_phone_primary')
                    <span class="error">{{ $message }}</span>
                @enderror
            </label>
            <label data-company-field>
                Telefone secundário
                <input type="text" name="contact_phone_secondary" id="contact_phone_secondary" value="{{ old('contact_phone_secondary', isset($client) ? $client->formattedPhone($client->contact_phone_secondary) : '') }}">
                @error('contact_phone_secondary')
                    <span class="error">{{ $message }}</span>
                @enderror
            </label>
            <label data-company-field>
                E-mail
                <input type="email" name="contact_email" value="{{ old('contact_email', $client->contact_email ?? '') }}">
                @error('contact_email')
                    <span class="error">{{ $message }}</span>
                @enderror
            </label>
        </div>
    </fieldset>
</div>
