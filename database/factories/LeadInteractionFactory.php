<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LeadInteraction>
 */
class LeadInteractionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(['phone_call', 'email', 'meeting', 'message', 'visit', 'other']),
            'interacted_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'description' => fake('pt_BR')->sentence(),
            'created_by_id' => \App\Models\User::inRandomOrder()->first()?->id ?? 1,
        ];
    }
}
