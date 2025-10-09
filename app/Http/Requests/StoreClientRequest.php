<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'document' => $this->sanitiseDigits($this->input('document')),
            'contact_phone_primary' => $this->sanitiseDigits($this->input('contact_phone_primary')),
            'contact_phone_secondary' => $this->sanitiseDigits($this->input('contact_phone_secondary')),
        ]);

        // Sanitize postal codes in addresses
        $addresses = $this->input('addresses', []);
        foreach ($addresses as $index => $address) {
            if (isset($address['postal_code'])) {
                $addresses[$index]['postal_code'] = $this->sanitiseDigits($address['postal_code']);
            }
        }
        $this->merge(['addresses' => $addresses]);
    }

    public function rules(): array
    {
        $isCompany = $this->input('person_type') === 'company';

        return [
            'name' => ['required', 'string', 'max:255'],
            'person_type' => ['required', Rule::in(['individual', 'company'])],
            'document' => [
                'required',
                'string',
                $isCompany ? 'digits:14' : 'digits:11',
                Rule::unique('clients', 'document'),
            ],
            'observations' => ['nullable', 'string'],
            'contact_name' => [$isCompany ? 'required' : 'nullable', 'string', 'max:255'],
            'contact_phone_primary' => [$isCompany ? 'required' : 'nullable', 'digits_between:10,11'],
            'contact_phone_secondary' => [$isCompany ? 'required' : 'nullable', 'digits_between:10,11'],
            'contact_email' => [$isCompany ? 'required' : 'nullable', 'email', 'max:255'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'addresses' => ['required', 'array', 'min:1'],
            'addresses.*.description' => ['nullable', 'string', 'max:255'],
            'addresses.*.postal_code' => ['required', 'digits:8'],
            'addresses.*.address' => ['required', 'string', 'max:255'],
            'addresses.*.address_number' => ['required', 'string', 'max:20'],
            'addresses.*.address_complement' => ['nullable', 'string', 'max:255'],
            'addresses.*.neighborhood' => ['required', 'string', 'max:255'],
            'addresses.*.city' => ['required', 'string', 'max:255'],
            'addresses.*.state' => ['required', 'string', 'size:2', Rule::in([
                'AC',
                'AL',
                'AP',
                'AM',
                'BA',
                'CE',
                'DF',
                'ES',
                'GO',
                'MA',
                'MT',
                'MS',
                'MG',
                'PA',
                'PB',
                'PR',
                'PE',
                'PI',
                'RJ',
                'RN',
                'RS',
                'RO',
                'RR',
                'SC',
                'SP',
                'SE',
                'TO',
            ])],
            'addresses.*.status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    protected function sanitiseDigits(?string $value): ?string
    {
        return $value ? preg_replace('/\D+/', '', $value) : null;
    }
}
