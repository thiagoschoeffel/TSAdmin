<?php

namespace Database\Seeders;

use App\Models\Opportunity;
use App\Models\User;
use App\Models\Lead;
use App\Models\Client;
use Illuminate\Database\Seeder;

class OpportunitySeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $userIds = User::query()->pluck('id');
    $leadIds = Lead::query()->pluck('id');
    $clientIds = Client::query()->pluck('id');

    if ($userIds->isEmpty()) {
      $this->call(UserSeeder::class);
      $userIds = User::query()->pluck('id');
    }

    if ($leadIds->isEmpty()) {
      $this->call(LeadSeeder::class);
      $leadIds = Lead::query()->pluck('id');
    }

    if ($clientIds->isEmpty()) {
      $this->call(ClientSeeder::class);
      $clientIds = Client::query()->pluck('id');
    }

    Opportunity::factory()
      ->count(50)
      ->state(fn() => [
        'owner_id' => $userIds->random(),
      ])
      ->create();
  }
}
