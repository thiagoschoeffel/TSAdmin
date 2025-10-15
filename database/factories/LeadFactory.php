<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\User;
use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as Faker;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        $faker = Faker::create('pt_BR');

        return [
            'name' => $faker->name(),
            'email' => $faker->unique()->safeEmail(),
            'phone' => $faker->phoneNumber(),
            'company' => $faker->optional()->company(),
            'source' => $faker->randomElement(LeadSource::cases()),
            'status' => $faker->randomElement(LeadStatus::cases()),
            'owner_id' => User::factory(),
            'created_by_id' => User::factory(),
            'updated_by_id' => User::factory(),
        ];
    }
}
