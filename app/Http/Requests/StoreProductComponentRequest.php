<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductComponentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Resolve product from route parameter safely (avoid collisions with input keys)
        $route = $this->route();
        $param = $route ? $route->parameter('product') : null;

        if ($param instanceof \App\Models\Product) {
            $product = $param;
        } elseif (is_scalar($param) && ctype_digit((string)$param)) {
            $product = \App\Models\Product::find((int)$param);
        } else {
            return false;
        }

        return $this->user()?->can('createComponent', $product) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'component_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
        ];
    }
}
