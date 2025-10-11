<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $order = \App\Models\Order::findOrFail($this->route('order'));
        return $this->user()->can('update', $order);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'client_id' => 'nullable|exists:clients,id',
            'status' => 'required|in:pending,confirmed,shipped,delivered,cancelled',
            'payment_method' => 'nullable|string',
            'delivery_type' => 'nullable|in:pickup,delivery',
            'address_id' => 'nullable|exists:addresses,id',
        ];
    }
}
