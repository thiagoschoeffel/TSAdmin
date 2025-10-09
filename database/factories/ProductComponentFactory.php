<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ProductComponent;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductComponentFactory extends Factory
{
  protected $model = ProductComponent::class;

  public function definition(): array
  {
    return [
      'product_id' => null, // Defina ao usar
      'component_id' => null, // Defina ao usar
      'quantity' => $this->faker->randomFloat(2, 0.1, 10),
    ];
  }
}
