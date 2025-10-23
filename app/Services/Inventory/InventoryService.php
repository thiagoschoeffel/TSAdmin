<?php

namespace App\Services\Inventory;

use App\Models\BlockProduction;
use App\Models\InventoryMovement;
use App\Models\InventoryReservation;
use App\Models\MoldedProduction;
use App\Models\ProductionPointing;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function reserveForProductionPointing(ProductionPointing $pp): void
    {
        if (!$pp->raw_material_id || !$pp->quantity) {
            return; // nothing to reserve
        }
        DB::transaction(function () use ($pp) {
            $reservation = InventoryReservation::query()->firstOrNew([
                'production_pointing_id' => $pp->id,
            ]);
            $reservation->raw_material_id = (int) $pp->raw_material_id;
            $reservation->reserved_kg = (float) $pp->quantity;
            if ($reservation->consumed_kg >= $reservation->reserved_kg) {
                $reservation->status = 'closed';
            } else {
                $reservation->status = 'active';
            }
            $reservation->save();

            // record reservation movement for traceability (no location)
            InventoryMovement::query()->create([
                'occurred_at' => $pp->started_at ?? Carbon::now(),
                'item_type' => 'raw_material',
                'item_id' => (int) $pp->raw_material_id,
                'location_type' => 'none',
                'location_id' => null,
                'direction' => 'reserve',
                'quantity' => (float) $pp->quantity,
                'unit' => 'kg',
                'reference_type' => ProductionPointing::class,
                'reference_id' => $pp->id,
                'notes' => 'Reserva por apontamento de produção',
                'created_by' => Auth::id(),
            ]);
        });
    }

    public function syncBlockProduction(BlockProduction $bp): void
    {
        $bp->loadMissing(['productionPointing:id,raw_material_id,quantity', 'silos:id', 'blockType:id']);
        $pp = $bp->productionPointing;
        $rawId = $pp?->raw_material_id;
        $weightKg = (float) $bp->weight;
        $occur = $bp->ended_at ?? $bp->started_at ?? Carbon::now();

        DB::transaction(function () use ($bp, $pp, $rawId, $weightKg, $occur) {
            // remove previous movements for this block production
            InventoryMovement::query()
                ->where('reference_type', BlockProduction::class)
                ->where('reference_id', $bp->id)
                ->delete();

            if ($rawId) {
                $siloIds = $bp->silos()->pluck('silo_id')->all();
                $split = max(1, count($siloIds));
                $perSilo = $weightKg / $split;
                if ($split === 0) {
                    // no silo selected: consume without location
                    InventoryMovement::query()->create([
                        'occurred_at' => $occur,
                        'item_type' => 'raw_material',
                        'item_id' => (int) $rawId,
                        'location_type' => 'none',
                        'location_id' => null,
                        'direction' => 'out',
                        'quantity' => $weightKg,
                        'unit' => 'kg',
                        'reference_type' => BlockProduction::class,
                        'reference_id' => $bp->id,
                        'notes' => 'Consumo para bloco',
                        'created_by' => Auth::id(),
                    ]);
                } else {
                    foreach ($siloIds as $siloId) {
                        InventoryMovement::query()->create([
                            'occurred_at' => $occur,
                            'item_type' => 'raw_material',
                            'item_id' => (int) $rawId,
                            'location_type' => 'silo',
                            'location_id' => (int) $siloId,
                            'direction' => 'out',
                            'quantity' => $perSilo,
                            'unit' => 'kg',
                            'reference_type' => BlockProduction::class,
                            'reference_id' => $bp->id,
                            'notes' => 'Consumo para bloco',
                            'created_by' => Auth::id(),
                        ]);
                    }
                }

                // update reservation fulfillment if exists
                if ($pp) {
                    $this->fulfillReservation($pp, $weightKg);
                }
            }

            // Produced stock (skip if scrap)
            if (!$bp->is_scrap) {
                InventoryMovement::query()->create([
                    'occurred_at' => $occur,
                    'item_type' => 'block',
                    'item_id' => (int) $bp->block_type_id,
                    'location_type' => 'none',
                    'location_id' => null,
                    'direction' => 'in',
                    'quantity' => $weightKg, // track in kg
                    'unit' => 'kg',
                    'reference_type' => BlockProduction::class,
                    'reference_id' => $bp->id,
                    'notes' => 'Entrada de produção (bloco)',
                    'created_by' => Auth::id(),
                ]);
            } else {
                // scrap as loss register (adjust negative finished if needed in future)
                InventoryMovement::query()->create([
                    'occurred_at' => $occur,
                    'item_type' => 'block',
                    'item_id' => (int) $bp->block_type_id,
                    'location_type' => 'none',
                    'location_id' => null,
                    'direction' => 'adjust',
                    'quantity' => -$weightKg,
                    'unit' => 'kg',
                    'reference_type' => BlockProduction::class,
                    'reference_id' => $bp->id,
                    'notes' => 'Perda (bloco refugo)',
                    'created_by' => Auth::id(),
                ]);
            }
        });
    }

    public function syncMoldedProduction(MoldedProduction $mp): void
    {
        $mp->loadMissing(['productionPointing:id,raw_material_id,quantity', 'silos:id']);
        $pp = $mp->productionPointing;
        $rawId = $pp?->raw_material_id;
        $consumedKg = (float) $mp->total_weight_considered; // kg considered
        $occur = $mp->ended_at ?? $mp->started_at ?? Carbon::now();

        DB::transaction(function () use ($mp, $pp, $rawId, $consumedKg, $occur) {
            // remove previous movements for this molded production
            InventoryMovement::query()
                ->where('reference_type', MoldedProduction::class)
                ->where('reference_id', $mp->id)
                ->delete();

            if ($rawId) {
                $siloIds = $mp->silos()->pluck('silo_id')->all();
                $split = max(1, count($siloIds));
                $perSilo = $consumedKg / $split;
                if ($split === 0) {
                    InventoryMovement::query()->create([
                        'occurred_at' => $occur,
                        'item_type' => 'raw_material',
                        'item_id' => (int) $rawId,
                        'location_type' => 'none',
                        'location_id' => null,
                        'direction' => 'out',
                        'quantity' => $consumedKg,
                        'unit' => 'kg',
                        'reference_type' => MoldedProduction::class,
                        'reference_id' => $mp->id,
                        'notes' => 'Consumo para moldado',
                        'created_by' => Auth::id(),
                    ]);
                } else {
                    foreach ($siloIds as $siloId) {
                        InventoryMovement::query()->create([
                            'occurred_at' => $occur,
                            'item_type' => 'raw_material',
                            'item_id' => (int) $rawId,
                            'location_type' => 'silo',
                            'location_id' => (int) $siloId,
                            'direction' => 'out',
                            'quantity' => $perSilo,
                            'unit' => 'kg',
                            'reference_type' => MoldedProduction::class,
                            'reference_id' => $mp->id,
                            'notes' => 'Consumo para moldado',
                            'created_by' => Auth::id(),
                        ]);
                    }
                }

                if ($pp) {
                    $this->fulfillReservation($pp, $consumedKg);
                }
            }

            // Produced stock (molded) in kg
            InventoryMovement::query()->create([
                'occurred_at' => $occur,
                'item_type' => 'molded',
                'item_id' => (int) $mp->mold_type_id,
                'location_type' => 'none',
                'location_id' => null,
                'direction' => 'in',
                'quantity' => $consumedKg, // track in kg for parity
                'unit' => 'kg',
                'reference_type' => MoldedProduction::class,
                'reference_id' => $mp->id,
                'notes' => 'Entrada de produção (moldado)',
                'created_by' => Auth::id(),
            ]);
        });
    }

    protected function fulfillReservation(ProductionPointing $pp, float $consumedKg): void
    {
        $reservation = InventoryReservation::query()->where('production_pointing_id', $pp->id)->first();
        if (!$reservation) {
            return;
        }
        $reservation->consumed_kg = max(0, (float) $reservation->consumed_kg + $consumedKg);
        if ($reservation->consumed_kg >= (float) $reservation->reserved_kg) {
            $reservation->status = 'closed';
        }
        $reservation->save();
    }
}

