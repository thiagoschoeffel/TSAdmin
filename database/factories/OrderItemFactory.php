<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as Faker;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        $faker = Faker::create('pt_BR');

        $product = Product::where('status', 'active')->inRandomOrder()->first() ?? Product::factory()->create(['status' => 'active']);
        $quantity = $faker->randomFloat(2, 0.5, 10);
        $unitPrice = $product->price;

        return [
            'order_id' => null, // Definir ao usar
            'product_id' => $product->id,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total' => $quantity * $unitPrice,
        ];
    }
}
