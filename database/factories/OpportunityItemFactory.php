<?php

namespace Database\Factories;

use App\Models\OpportunityItem;
use App\Models\Opportunity;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as Faker;

/**
 * @extends Factory<OpportunityItem>
 */
class OpportunityItemFactory extends Factory
{
  protected $model = OpportunityItem::class;

  public function definition(): array
  {
    $faker = Faker::create('pt_BR');

    $quantity = $faker->numberBetween(1, 50);
    $unitPrice = $faker->randomFloat(2, 10, 1000); // Preço entre 10 e 1000

    return [
      'opportunity_id' => Opportunity::factory(),
      'product_id' => Product::factory(),
      'quantity' => $quantity,
      'unit_price' => $unitPrice,
      'subtotal' => $quantity * $unitPrice,
    ];
  }
}
