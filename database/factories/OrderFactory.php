<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use App\Models\Client;
use App\Models\User;
use App\Models\Address;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as Faker;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $faker = Faker::create('pt_BR');

        $deliveryType = $faker->randomElement(['pickup', 'delivery']);

        return [
            'client_id' => Client::factory(),
            'user_id' => User::factory(),
            'status' => $faker->randomElement(['pending', 'confirmed', 'shipped', 'delivered', 'cancelled']),
            'payment_method' => $faker->randomElement(['cash', 'card', 'pix']),
            'delivery_type' => $deliveryType,
            'address_id' => null, // será definido se for delivery
            'total' => 0, // será calculado depois
            'notes' => $faker->optional()->paragraph(),
            'ordered_at' => $faker->dateTimeBetween('-30 days', 'now'),
            'created_by_id' => User::factory(),
            'updated_by_id' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Order $order) {
            $faker = Faker::create('pt_BR');

            // Se for entrega, criar endereço para o cliente
            if ($order->delivery_type === 'delivery') {
                $address = Address::factory()->create([
                    'client_id' => $order->client_id,
                    'created_by_id' => $order->created_by_id,
                ]);
                $order->update(['address_id' => $address->id]);
            }

            // Criar itens do pedido
            $numItems = $faker->numberBetween(1, 5);
            $total = 0;

            for ($i = 0; $i < $numItems; $i++) {
                $product = Product::where('status', 'active')->inRandomOrder()->first() ?? Product::factory()->create(['status' => 'active']);
                $quantity = $faker->randomFloat(2, 0.5, 10);
                $unitPrice = $product->price;
                $itemTotal = $quantity * $unitPrice;

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total' => $itemTotal,
                ]);

                $total += $itemTotal;
            }

            // Atualizar total do pedido
            $order->update(['total' => $total]);
        });
    }
}
