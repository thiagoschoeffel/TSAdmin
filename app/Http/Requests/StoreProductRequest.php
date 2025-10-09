<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Ajuste conforme política de permissão
        return true;
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $components = $this->input('components', []);

            // Verificar auto-referência (não implementado ainda, pois o produto ainda não existe)

            // Verificar ciclos entre componentes existentes
            foreach ($components as $component) {
                $componentId = $component['id'];
                // Verificar se algum componente tem dependências que levam de volta
                if ($this->hasCircularDependency($componentId, array_column($components, 'id'))) {
                    $validator->errors()->add('components', 'Dependências circulares detectadas nos componentes.');
                    break;
                }
            }
        });
    }

    private function hasCircularDependency($productId, $componentIds)
    {
        // Para criação, ainda não há produto, então só verificar entre os componentes
        // Mas como é criação, não há risco de ciclo ainda
        return false;
    }
}
