@extends('layouts.app')

@section('title', 'Editar cliente')

@section('content')
    <section class="card space-y-8">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Editar cliente</h1>
                <p class="mt-2 text-sm text-slate-500">Atualize as informações de {{ $client->name }}.</p>
            </div>
            <a class="btn-ghost" href="{{ route('clients.index') }}">Voltar para lista</a>
        </div>

        @if ($errors->any())
            <div class="status status-danger">
                <strong class="font-semibold">Ops!</strong> Verifique os campos destacados abaixo.
            </div>
        @endif

        <form method="POST" action="{{ route('clients.update', $client) }}" id="client_form" class="space-y-6">
            @csrf
            @method('PATCH')

            @include('clients._form', ['client' => $client])

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="btn-primary">Salvar alterações</button>
                <a class="btn-ghost" href="{{ route('clients.index') }}">Cancelar</a>
            </div>
        </form>
    </section>
@endsection

@push('scripts')
    <script>
        const personTypeField = document.getElementById('person_type');
        const documentField = document.getElementById('document');
        const postalCodeField = document.getElementById('postal_code');
        const phonePrimaryField = document.getElementById('contact_phone_primary');
        const phoneSecondaryField = document.getElementById('contact_phone_secondary');
        const companyFields = document.querySelectorAll('[data-company-field] input');
        const statusToggle = document.querySelector('[data-status-toggle]');
        const statusLabel = document.querySelector('[data-status-label]');

        const applyMask = (value, pattern) => {
            let i = 0;
            const v = value.replace(/\D/g, '');
            return pattern.replace(/#/g, () => v[i++] ?? '').replace(/([-/\.() ])+$/, '');
        };

        const formatDocument = () => {
            if (!documentField) return;
            const digits = documentField.value.replace(/\D/g, '');
            documentField.value = personTypeField.value === 'company'
                ? applyMask(digits, '##.###.###/####-##')
                : applyMask(digits, '###.###.###-##');
        };

        const formatPostalCode = () => {
            if (!postalCodeField) return;
            postalCodeField.value = applyMask(postalCodeField.value, '#####-###');
        };

        const formatPhone = (field) => {
            if (!field) return;
            const digits = field.value.replace(/\D/g, '');
            field.value = digits.length > 10
                ? applyMask(digits, '(##) #####-####')
                : applyMask(digits, '(##) ####-####');
        };

        const toggleCompanyFields = () => {
            companyFields.forEach((field) => {
                if (personTypeField.value === 'company') {
                    field.setAttribute('required', 'required');
                } else {
                    field.removeAttribute('required');
                }
            });
        };

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

        personTypeField?.addEventListener('change', () => {
            formatDocument();
            toggleCompanyFields();
        });

        documentField?.addEventListener('input', formatDocument);
        postalCodeField?.addEventListener('input', formatPostalCode);
        phonePrimaryField?.addEventListener('input', () => formatPhone(phonePrimaryField));
        phoneSecondaryField?.addEventListener('input', () => formatPhone(phoneSecondaryField));

        toggleCompanyFields();
        formatDocument();
        formatPostalCode();
        formatPhone(phonePrimaryField);
        formatPhone(phoneSecondaryField);
        updateStatusLabel();

        statusToggle?.addEventListener('change', updateStatusLabel);
    </script>
@endpush
