<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Administrador',
            'email' => 'admin@example.com',
            'password' => 'password',
            'status' => 'active',
            'role' => 'admin',
        ]);

        User::factory()->count(22)->create([
            'role' => 'user',
        ]);
    }
}
