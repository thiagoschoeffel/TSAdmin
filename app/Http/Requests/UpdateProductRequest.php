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
            'unit_of_measure' => 'required|string|in:UND,KG,M2,M3,L,ML,PCT,CX,DZ',
            'status' => 'in:active,inactive',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'components' => 'array',
            'components.*.id' => 'required|exists:products,id',
            'components.*.quantity' => 'required|numeric|min:0.01',
        ];
    }
}
