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
            'postal_code' => $this->sanitiseDigits($this->input('postal_code')),
            'contact_phone_primary' => $this->sanitiseDigits($this->input('contact_phone_primary')),
            'contact_phone_secondary' => $this->sanitiseDigits($this->input('contact_phone_secondary')),
        ]);

        // Fetch address from ViaCEP to ensure city and state are correct
        $this->fetchAndMergeAddress();
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
            'postal_code' => ['required', 'digits:8'],
            'address' => ['required', 'string', 'max:255'],
            'address_number' => ['required', 'string', 'max:20'],
            'address_complement' => ['nullable', 'string', 'max:255'],
            'neighborhood' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'size:2'],
            'contact_name' => [$isCompany ? 'required' : 'nullable', 'string', 'max:255'],
            'contact_phone_primary' => [$isCompany ? 'required' : 'nullable', 'digits_between:10,11'],
            'contact_phone_secondary' => [$isCompany ? 'required' : 'nullable', 'digits_between:10,11'],
            'contact_email' => [$isCompany ? 'required' : 'nullable', 'email', 'max:255'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    protected function sanitiseDigits(?string $value): ?string
    {
        return $value ? preg_replace('/\D+/', '', $value) : null;
    }

    protected function fetchAndMergeAddress(): void
    {
        $postalCode = $this->input('postal_code');
        if (!$postalCode || strlen($postalCode) !== 8) {
            return;
        }

        try {
            $response = \Illuminate\Support\Facades\Http::get("https://viacep.com.br/ws/{$postalCode}/json/");
            $data = $response->json();

            if ($data && !isset($data['erro'])) {
                $this->merge([
                    'address' => $data['logradouro'] ?? $this->input('address'),
                    'neighborhood' => $data['bairro'] ?? $this->input('neighborhood'),
                    'city' => $data['localidade'] ?? $this->input('city'),
                    'state' => $data['uf'] ?? $this->input('state'),
                ]);
            }
        } catch (\Exception $e) {
            // If API fails, keep the submitted values
        }
    }
}
