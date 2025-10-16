<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LeadInteractionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $leads = \App\Models\Lead::all();
        $userIds = \App\Models\User::pluck('id')->toArray();

        if ($leads->isEmpty() || empty($userIds)) {
            return;
        }

        foreach ($leads as $lead) {
            // Cada lead terá entre 0 e 5 interações
            $interactionCount = fake()->numberBetween(0, 5);

            for ($i = 0; $i < $interactionCount; $i++) {
                \App\Models\LeadInteraction::factory()->create([
                    'lead_id' => $lead->id,
                    'created_by_id' => fake()->randomElement($userIds),
                ]);
            }
        }
    }
}
