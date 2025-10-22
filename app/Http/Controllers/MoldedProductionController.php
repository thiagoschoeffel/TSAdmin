<?php

namespace App\Http\Controllers;

use App\Models\MoldedProduction;
use App\Models\ProductionPointing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MoldedProductionController extends Controller
{
    public function index(Request $request, ProductionPointing $productionPointing): JsonResponse
    {
        $this->authorize('view', $productionPointing);

        $rows = MoldedProduction::query()
            ->where('production_pointing_id', $productionPointing->id)
            ->with(['moldType:id,name', 'operators:id,name', 'silos:id,name'])
            ->orderBy('id')
            ->get();

        $payload = $rows->map(function (MoldedProduction $mp, $idx) {
            return [
                'id' => $mp->id,
                'seq' => $idx + 1,
                'mold_type_id' => $mp->mold_type_id,
                'mold_type_name' => $mp->moldType?->name,
                'started_at' => $mp->started_at?->format('Y-m-d H:i:s'),
                'ended_at' => $mp->ended_at?->format('Y-m-d H:i:s'),
                'sheet_number' => $mp->sheet_number,
                'quantity' => (int) $mp->quantity,
                'package_weight' => (float) $mp->package_weight,
                'loss_factor_enabled' => (bool) $mp->loss_factor_enabled,
                'loss_factor' => $mp->loss_factor !== null ? (float) $mp->loss_factor : null,
                'weight_considered_unit' => (float) $mp->weight_considered_unit,
                'total_weight_considered' => (float) $mp->total_weight_considered,
                'silo_names' => $mp->silos->pluck('name')->values(),
                'operator_names' => $mp->operators->pluck('name')->values(),
                'silo_ids' => $mp->silos->pluck('id')->values(),
                'operator_ids' => $mp->operators->pluck('id')->values(),
            ];
        })->values();

        return response()->json(['data' => $payload]);
    }

    public function store(Request $request, ProductionPointing $productionPointing): JsonResponse
    {
        $this->authorize('update', $productionPointing);

        $data = $request->validate([
            'started_at' => ['required', 'date'],
            'ended_at' => ['required', 'date'],
            'sheet_number' => ['required', 'integer', 'min:1'],
            'mold_type_id' => ['required', 'exists:mold_types,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'package_weight' => ['required', 'numeric', 'min:0.01'],
            // Customização do fator de perda (opcional)
            'loss_factor_enabled' => ['nullable', 'boolean'],
            'loss_factor' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'operator_ids' => ['array'],
            'operator_ids.*' => ['integer', 'exists:operators,id'],
            'silo_ids' => ['array'],
            'silo_ids.*' => ['integer', 'exists:silos,id'],
        ]);
        // Usar quantidade por pacote do cadastro de tipo de moldado
        $moldType = \App\Models\MoldType::query()->findOrFail((int) $data['mold_type_id']);
        $packageQty = max(1, (int) round((float) ($moldType->pieces_per_package ?? 1)));
        $perUnit = ((float) $data['package_weight'] / $packageQty);
        $enabled = (bool) ($data['loss_factor_enabled'] ?? false);
        $lossFactor = $enabled && array_key_exists('loss_factor', $data) && $data['loss_factor'] !== null
            ? max(0.0, min(1.0, (float) $data['loss_factor']))
            : 0.42;
        $weightConsideredUnit = $perUnit - ($perUnit * $lossFactor);
        $totalWeightConsidered = (int) $data['quantity'] * $weightConsideredUnit;

        $mp = MoldedProduction::create([
            'production_pointing_id' => $productionPointing->id,
            'mold_type_id' => (int) $data['mold_type_id'],
            'started_at' => $data['started_at'],
            'ended_at' => $data['ended_at'],
            'sheet_number' => (int) $data['sheet_number'],
            'quantity' => (int) $data['quantity'],
            'package_weight' => (float) $data['package_weight'],
            'package_quantity' => $packageQty,
            'loss_factor_enabled' => $enabled,
            'loss_factor' => $enabled ? $lossFactor : 0.42,
            'weight_considered_unit' => $weightConsideredUnit,
            'total_weight_considered' => $totalWeightConsidered,
            'created_by_id' => Auth::id(),
            'updated_by_id' => Auth::id(),
        ]);

        $mp->operators()->sync($data['operator_ids'] ?? []);
        $mp->silos()->sync($data['silo_ids'] ?? []);

        $mp->load(['moldType:id,name', 'operators:id,name', 'silos:id,name']);

        return response()->json([
            'moldedProduction' => [
                'id' => $mp->id,
                'mold_type_name' => $mp->moldType?->name,
                'started_at' => $mp->started_at?->format('Y-m-d H:i:s'),
                'ended_at' => $mp->ended_at?->format('Y-m-d H:i:s'),
                'sheet_number' => $mp->sheet_number,
                'quantity' => $mp->quantity,
                'package_weight' => (float) $mp->package_weight,
                'weight_considered_unit' => (float) $mp->weight_considered_unit,
                'total_weight_considered' => (float) $mp->total_weight_considered,
                'silo_names' => $mp->silos->pluck('name')->values(),
                'operator_names' => $mp->operators->pluck('name')->values(),
            ],
        ]);
    }

    public function destroy(ProductionPointing $productionPointing, MoldedProduction $moldedProduction): JsonResponse
    {
        $this->authorize('update', $productionPointing);
        if ($moldedProduction->production_pointing_id !== $productionPointing->id) {
            abort(404);
        }
        $moldedProduction->delete();
        return response()->json(['status' => 'ok']);
    }

    public function update(Request $request, ProductionPointing $productionPointing, MoldedProduction $moldedProduction): JsonResponse
    {
        $this->authorize('update', $productionPointing);
        if ($moldedProduction->production_pointing_id !== $productionPointing->id) {
            abort(404);
        }

        $data = $request->validate([
            'started_at' => ['required', 'date'],
            'ended_at' => ['required', 'date'],
            'sheet_number' => ['required', 'integer', 'min:1'],
            'mold_type_id' => ['required', 'exists:mold_types,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'package_weight' => ['required', 'numeric', 'min:0.01'],
            'loss_factor_enabled' => ['nullable', 'boolean'],
            'loss_factor' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'operator_ids' => ['array'],
            'operator_ids.*' => ['integer', 'exists:operators,id'],
            'silo_ids' => ['array'],
            'silo_ids.*' => ['integer', 'exists:silos,id'],
        ]);

        $moldType = \App\Models\MoldType::query()->findOrFail((int) $data['mold_type_id']);
        $packageQty = max(1, (int) round((float) ($moldType->pieces_per_package ?? 1)));
        $perUnit = ((float) $data['package_weight'] / $packageQty);
        $enabled = (bool) ($data['loss_factor_enabled'] ?? false);
        $lossFactor = $enabled && array_key_exists('loss_factor', $data) && $data['loss_factor'] !== null
            ? max(0.0, min(1.0, (float) $data['loss_factor']))
            : 0.42;
        $weightConsideredUnit = $perUnit - ($perUnit * $lossFactor);
        $totalWeightConsidered = (int) $data['quantity'] * $weightConsideredUnit;

        $moldedProduction->update([
            'mold_type_id' => (int) $data['mold_type_id'],
            'started_at' => $data['started_at'],
            'ended_at' => $data['ended_at'],
            'sheet_number' => (int) $data['sheet_number'],
            'quantity' => (int) $data['quantity'],
            'package_weight' => (float) $data['package_weight'],
            'package_quantity' => $packageQty,
            'loss_factor_enabled' => $enabled,
            'loss_factor' => $enabled ? $lossFactor : 0.42,
            'weight_considered_unit' => $weightConsideredUnit,
            'total_weight_considered' => $totalWeightConsidered,
            'updated_by_id' => Auth::id(),
        ]);

        $moldedProduction->operators()->sync($data['operator_ids'] ?? []);
        $moldedProduction->silos()->sync($data['silo_ids'] ?? []);

        $moldedProduction->load(['moldType:id,name', 'operators:id,name', 'silos:id,name']);

        return response()->json([
            'moldedProduction' => [
                'id' => $moldedProduction->id,
                'mold_type_id' => $moldedProduction->mold_type_id,
                'mold_type_name' => $moldedProduction->moldType?->name,
                'started_at' => $moldedProduction->started_at?->format('Y-m-d H:i:s'),
                'ended_at' => $moldedProduction->ended_at?->format('Y-m-d H:i:s'),
                'sheet_number' => $moldedProduction->sheet_number,
                'quantity' => $moldedProduction->quantity,
                'package_weight' => (float) $moldedProduction->package_weight,
                'weight_considered_unit' => (float) $moldedProduction->weight_considered_unit,
                'total_weight_considered' => (float) $moldedProduction->total_weight_considered,
                'silo_names' => $moldedProduction->silos->pluck('name')->values(),
                'operator_names' => $moldedProduction->operators->pluck('name')->values(),
                'silo_ids' => $moldedProduction->silos->pluck('id')->values(),
                'operator_ids' => $moldedProduction->operators->pluck('id')->values(),
            ],
        ]);
    }
}
