<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DevSeeder extends Seeder
{
    public function run(): void
    {
        // For development/local environments, we can safely truncate and reseed
        Schema::disableForeignKeyConstraints();
        foreach (
            [
                'order_items',
                'orders',
                'product_components',
                'products',
                'addresses',
                'clients',
                'lead_interactions',
                'opportunity_items',
                'opportunities',
                'leads',
                'sectors',
                'machines',
                'reason_types',
                'reasons',
                'machine_downtimes',
                'operators',
            ] as $table
        ) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }
        Schema::enableForeignKeyConstraints();

        DB::transaction(function () {
            // Reseed in coherent order
            $this->call(UserSeeder::class);
            $this->call(SectorSeeder::class);
            $this->call(MachineSeeder::class);
            $this->call(ReasonTypeSeeder::class);
            $this->call(ReasonSeeder::class);
            $this->call(MachineDowntimeSeeder::class);
            $this->call(ClientSeeder::class);
            $this->call(ProductSeeder::class);
            $this->call(LeadSeeder::class);
            $this->call(LeadInteractionSeeder::class);
            $this->call(OrderSeeder::class);
            $this->call(OpportunitySeeder::class);
            $this->call(OperatorSeeder::class);
        });
    }
}
