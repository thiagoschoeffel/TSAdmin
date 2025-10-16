<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // Similar to Dev seeding, but volumes can be overridden via --qtd
        Schema::disableForeignKeyConstraints();
        foreach ([
            'order_items', 'orders',
            'product_components', 'products',
            'addresses', 'clients',
            'lead_interactions', 'opportunity_items', 'opportunities', 'leads',
        ] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->truncate();
            }
        }
        Schema::enableForeignKeyConstraints();

        DB::transaction(function () {
            $this->call(UserSeeder::class);
            $this->call(ClientSeeder::class);
            $this->call(ProductSeeder::class);
            $this->call(LeadSeeder::class);
            $this->call(LeadInteractionSeeder::class);
            $this->call(OrderSeeder::class);
            $this->call(OpportunitySeeder::class);
        });
    }
}

