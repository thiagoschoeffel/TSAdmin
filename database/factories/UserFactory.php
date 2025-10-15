<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
        static $sequence = 0;

        return [
            'name' => fake()->name(),
            'email' => 'user' . (++$sequence) . '@example.com',
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'status' => fake()->randomElement(['active', 'inactive']),
            'role' => 'user',
            'permissions' => $this->generateRandomPermissions(),
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
        $resources = config('permissions.resources', []);
        $permissions = [];

        foreach ($resources as $resourceKey => $resource) {
            $abilities = array_keys($resource['abilities'] ?? []);
            $permissions[$resourceKey] = [];
            foreach ($abilities as $ability) {
                $permissions[$resourceKey][$ability] = fake()->boolean(70); // 70% chance de ter a permissão
            }
        }

        return $permissions;
    }
}
