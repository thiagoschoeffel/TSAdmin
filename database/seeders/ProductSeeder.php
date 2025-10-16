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

        $base = (int) env('SEED_QTD', (int) config('seeding.volumes.products', 0));
        $default = (int) config('seeding.volumes.products', 80);
        $count = $base > 0 ? max(10, (int) round($base * 0.4)) : $default;

        Product::factory()
            ->count($count)
            ->state(fn() => [
                'created_by' => $userIds->random(),
                'updated_by' => fake()->boolean(40) ? $userIds->random() : null,
            ])
            ->create();
    }
}
