@extends('layouts.app')

@section('title', 'Editar cliente')

@section('content')
    <section class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;gap:1rem;flex-wrap:wrap;">
            <div>
                <h1 style="font-size:1.75rem;margin:0;">Editar cliente</h1>
                <p style="color:#64748b;margin-top:0.5rem;">Atualize as informações do cliente.</p>
            </div>
            <a class="link" href="{{ route('clients.show', $client) }}">Voltar aos detalhes</a>
        </div>

        @if ($errors->any())
            <div class="status" style="background:#fee2e2;color:#991b1b;border-color:#fecaca;">
                <strong>Ops!</strong> Verifique os campos destacados abaixo.
            </div>
        @endif

        <form method="POST" action="{{ route('clients.update', $client) }}" style="display:grid;gap:1.5rem;">
            @csrf
            @method('PATCH')

            @include('clients._form', ['client' => $client])

            <div style="display:flex;gap:1rem;flex-wrap:wrap;">
                <button type="submit">Salvar alterações</button>
                <a class="link" href="{{ route('clients.show', $client) }}">Cancelar</a>
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
            return pattern.replace(/#/g, _ => v[i++] ?? '').replace(/([-/\.() ])+$/, '');
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
            companyFields.forEach(field => {
                if (personTypeField.value === 'company') {
                    field.setAttribute('required', 'required');
                } else {
                    field.removeAttribute('required');
                }
            });
        };

        const updateStatusLabel = () => {
            if (!statusLabel || !statusToggle) return;
            if (statusToggle.checked) {
                statusLabel.textContent = 'Ativo';
                statusLabel.style.color = '#2563eb';
            } else {
                statusLabel.textContent = 'Inativo';
                statusLabel.style.color = '#991b1b';
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
