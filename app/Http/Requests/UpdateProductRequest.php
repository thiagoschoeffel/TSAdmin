<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Ajuste conforme política de permissão
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'status' => 'in:active,inactive',
            'components' => 'array',
            'components.*.id' => 'required|exists:products,id',
            'components.*.quantity' => 'required|numeric|min:0.01',
        ];
    }
}
