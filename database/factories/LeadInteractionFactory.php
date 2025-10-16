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
        $faker = config('seeding.faker_locale', config('app.faker_locale')) === 'pt_BR' ? fake('pt_BR') : fake();
        return [
            'type' => $faker->randomElement(['phone_call', 'email', 'meeting', 'message', 'visit', 'other']),
            'interacted_at' => $faker->dateTimeBetween('-30 days', 'now'),
            'description' => $faker->sentence(),
            'created_by_id' => \App\Models\User::factory(),
        ];
    }
}
