<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $faker = Faker::create('pt_BR');

        // Gerar permissões variadas
        $permissions = $this->generateRandomPermissions();

        return [
            'name' => $faker->name(),
            'email' => $faker->unique()->safeEmail(),
            'email_verified_at' => $faker->boolean(90) ? now() : null, // 10% não verificados
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'status' => $faker->randomElement(['active', 'inactive']),
            'role' => 'user',
            'permissions' => $permissions,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Generate random permissions for the user.
     */
    private function generateRandomPermissions(): array
    {
        $resources = ['clients', 'products', 'orders'];
        $abilities = ['view', 'create', 'update', 'delete'];

        $permissions = [];

        foreach ($resources as $resource) {
            $permissions[$resource] = [];
            foreach ($abilities as $ability) {
                $permissions[$resource][$ability] = fake()->boolean(70); // 70% chance de ter a permissão
            }
        }

        return $permissions;
    }
}
