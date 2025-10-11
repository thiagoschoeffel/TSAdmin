<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userIds = User::query()->pluck('id');
        $clientIds = \App\Models\Client::query()->pluck('id');

        if ($userIds->isEmpty()) {
            $this->call(UserSeeder::class);
            $userIds = User::query()->pluck('id');
        }

        if ($clientIds->isEmpty()) {
            $this->call(ClientSeeder::class);
            $clientIds = \App\Models\Client::query()->pluck('id');
        }

        Order::factory()
            ->count(234)
            ->state(fn() => [
                'client_id' => $clientIds->random(),
                'user_id' => $userIds->random(),
                'created_by_id' => $userIds->random(),
                'updated_by_id' => fake()->boolean(40) ? $userIds->random() : null,
            ])
            ->create();
    }
}
