<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userIds = User::query()->pluck('id');

        if ($userIds->isEmpty()) {
            $this->call(UserSeeder::class);
            $userIds = User::query()->pluck('id');
        }

        Product::factory()
            ->count(79)
            ->state(fn() => [
                'created_by' => $userIds->random(),
                'updated_by' => fake()->boolean(40) ? $userIds->random() : null,
            ])
            ->create();
    }
}
