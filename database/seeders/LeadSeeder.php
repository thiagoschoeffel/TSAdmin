<?php

namespace Database\Seeders;

use App\Models\Lead;
use App\Models\User;
use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use Illuminate\Database\Seeder;

class LeadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure we have users first
        if (User::count() === 0) {
            $this->call(UserSeeder::class);
        }

        $userIds = User::pluck('id')->toArray();

        for ($i = 0; $i < 25; $i++) {
            Lead::create([
                'name' => fake('pt_BR')->name(),
                'email' => fake('pt_BR')->unique()->safeEmail(),
                'phone' => fake('pt_BR')->phoneNumber(),
                'company' => fake('pt_BR')->optional()->company(),
                'source' => fake()->randomElement(LeadSource::cases()),
                'status' => fake()->randomElement(LeadStatus::cases()),
                'owner_id' => fake()->randomElement($userIds),
                'created_by_id' => fake()->randomElement($userIds),
                'updated_by_id' => fake()->boolean(40) ? fake()->randomElement($userIds) : null,
            ]);
        }
    }
}
