<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
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

        Client::factory()
            ->count(242)
            ->state(fn () => [
                'created_by_id' => $userIds->random(),
                'updated_by_id' => fake()->boolean(40) ? $userIds->random() : null,
            ])
            ->create();
    }
}
