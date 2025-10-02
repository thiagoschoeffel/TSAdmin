const digitsOnly = (value = '') => value.replace(/\D/g, '');

const applyMask = (value, pattern) => {
    let index = 0;
    const numbers = digitsOnly(value);

    return pattern
        .replace(/#/g, () => numbers[index++] ?? '')
        .replace(/([-/\.() ])+$/, '');
};

const formatDocument = (field, typeField) => {
    if (!field || !typeField) {
        return;
    }

    const digits = digitsOnly(field.value);

    field.value = typeField.value === 'company'
        ? applyMask(digits, '##.###.###/####-##')
        : applyMask(digits, '###.###.###-##');
};

const formatPostalCode = (field) => {
    if (!field) {
        return;
    }

    field.value = applyMask(field.value, '#####-###');
};

const formatPhone = (field) => {
    if (!field) {
        return;
    }

    const digits = digitsOnly(field.value);
    const pattern = digits.length > 10 ? '(##) #####-####' : '(##) ####-####';

    field.value = applyMask(digits, pattern);
};

const toggleCompanyFields = (fields, typeField) => {
    fields.forEach((field) => {
        if (typeField?.value === 'company') {
            field.setAttribute('required', 'required');
        } else {
            field.removeAttribute('required');
        }
    });
};

const initClientForm = (form) => {
    const personTypeField = form.querySelector('#person_type');
    const documentField = form.querySelector('#document');
    const postalCodeField = form.querySelector('#postal_code');
    const phonePrimaryField = form.querySelector('#contact_phone_primary');
    const phoneSecondaryField = form.querySelector('#contact_phone_secondary');
    const companyFields = form.querySelectorAll('[data-company-field] input');

    if (!personTypeField) {
        return;
    }

    personTypeField.addEventListener('change', () => {
        formatDocument(documentField, personTypeField);
        toggleCompanyFields(companyFields, personTypeField);
    });

    documentField?.addEventListener('input', () => formatDocument(documentField, personTypeField));
    postalCodeField?.addEventListener('input', () => formatPostalCode(postalCodeField));
    phonePrimaryField?.addEventListener('input', () => formatPhone(phonePrimaryField));
    phoneSecondaryField?.addEventListener('input', () => formatPhone(phoneSecondaryField));

    toggleCompanyFields(companyFields, personTypeField);
    formatDocument(documentField, personTypeField);
    formatPostalCode(postalCodeField);
    formatPhone(phonePrimaryField);
    formatPhone(phoneSecondaryField);
};

const initClientForms = () => {
    document.querySelectorAll('[data-client-form]').forEach((form) => initClientForm(form));
};

document.addEventListener('DOMContentLoaded', initClientForms);
