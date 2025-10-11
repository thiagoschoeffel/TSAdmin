<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ProductComponent;
use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as Faker;

class ProductComponentFactory extends Factory
{
    protected $model = ProductComponent::class;

    public function definition(): array
    {
        $faker = Faker::create('pt_BR');

        return [
            'product_id' => null, // Defina ao usar
            'component_id' => null, // Defina ao usar
            'quantity' => $faker->randomFloat(2, 0.1, 10),
        ];
    }
}
