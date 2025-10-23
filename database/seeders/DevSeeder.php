<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Termwind\Components\Raw;

class DevSeeder extends Seeder
{
    public function run(): void
    {
        // For development/local environments, we can safely truncate and reseed
        Schema::disableForeignKeyConstraints();
        foreach (
            [
                'inventory_movements',
                'inventory_reservations',
                'molded_production_operator',
                'molded_production_silo',
                'molded_productions',
                'block_production_operator',
                'block_production_silo',
                'block_productions',
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
                'almoxarifados',
                'machines',
                'reason_types',
                'reasons',
                'machine_downtimes',
                'operators',
                'block_types',
                'mold_types',
                'production_pointing_operator',
                'production_pointing_silo',
                'production_pointings',
                'raw_materials',
                'silos',
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
            $this->call(AlmoxarifadoSeeder::class);
            $this->call(MachineSeeder::class);
            $this->call(ReasonTypeSeeder::class);
            $this->call(ReasonSeeder::class);
            $this->call(MachineDowntimeSeeder::class);
            $this->call(OperatorSeeder::class);
            $this->call(RawMaterialSeeder::class);
            $this->call(SiloSeeder::class);
            $this->call(BlockTypeSeeder::class);
            $this->call(MoldTypeSeeder::class);
            $this->call(ProductionPointingSeeder::class);
            $this->call(BlockProductionSeeder::class);
            $this->call(MoldedProductionSeeder::class);
            // Movimentos e reservas após produções e apontamentos
            $this->call(InventorySeeder::class);
            $this->call(MoldedProductionScrapSeeder::class);
            $this->call(ProductSeeder::class);
            $this->call(LeadSeeder::class);
            $this->call(LeadInteractionSeeder::class);
            $this->call(OpportunitySeeder::class);
            $this->call(OrderSeeder::class);
            $this->call(OperatorSeeder::class);
        });
    }
}
