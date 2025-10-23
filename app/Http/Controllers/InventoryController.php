<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
use App\Models\RawMaterial;
use App\Models\Silo;
use App\Models\Almoxarifado;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    // Saldo de blocos por tipo e dimensões
    public function blockStockByTypeAndDimension(Request $request): JsonResponse
    {
        $this->authorize('viewAny', InventoryMovement::class);

        $query = InventoryMovement::query()
            ->where('item_type', 'block');

        // Filtros opcionais
        if ($blockTypeId = $request->query('block_type_id')) {
            $query->where('block_type_id', $blockTypeId);
        }
        if ($length = $request->query('length_mm')) {
            $query->where('length_mm', $length);
        }
        if ($width = $request->query('width_mm')) {
            $query->where('width_mm', $width);
        }
        if ($height = $request->query('height_mm')) {
            $query->where('height_mm', $height);
        }

        $stock = $query
            ->select('block_type_id', 'length_mm', 'width_mm', 'height_mm', DB::raw('SUM(quantity) as saldo'))
            ->groupBy('block_type_id', 'length_mm', 'width_mm', 'height_mm')
            ->get();

        return response()->json(['stock' => $stock]);
    }

    public function modal(InventoryMovement $movement): JsonResponse
    {
        $this->authorize('view', $movement);

        $movement->load([
            'rawMaterial',
            'silo',
            'blockType',
            'almoxarifado',
            'moldType',
            'createdBy',
            'updatedBy'
        ]);

        // Carregar movimento de consumo relacionado se existir
        $relatedConsumption = null;
        if ($movement->item_type === 'block' || $movement->item_type === 'molded') {
            $relatedConsumption = InventoryMovement::query()
                ->where('reference_type', 'inventory_movement')
                ->where('reference_id', $movement->id)
                ->where('direction', 'out')
                ->with(['rawMaterial', 'createdBy'])
                ->first();
        }

        return response()->json([
            'movement' => [
                'id' => $movement->id,
                'occurred_at' => $movement->occurred_at?->format('d/m/Y H:i'),
                'item_type' => $movement->item_type,
                'item_type_formatted' => match ($movement->item_type) {
                    'raw_material' => 'Matéria-prima',
                    'block' => 'Bloco',
                    'molded' => 'Moldado',
                    default => $movement->item_type
                },
                'direction' => $movement->direction,
                'direction_formatted' => match ($movement->direction) {
                    'in' => 'Entrada',
                    'out' => 'Saída',
                    'adjust' => 'Ajuste',
                    default => $movement->direction
                },
                'quantity' => $movement->quantity,
                'unit' => $movement->unit,
                'location_type' => $movement->location_type,
                'location_type_formatted' => match ($movement->location_type) {
                    'silo' => 'Silo',
                    'almoxarifado' => 'Almoxarifado',
                    'none' => 'Nenhum',
                    default => $movement->location_type
                },
                'location_name' => match ($movement->location_type) {
                    'silo' => $movement->silo?->name,
                    'almoxarifado' => $movement->almoxarifado?->name,
                    default => null
                },
                'reference_type' => $movement->reference_type,
                'reference_id' => $movement->reference_id,
                'reference_formatted' => $movement->reference_type ?
                    (\Illuminate\Support\Str::of($movement->reference_type)->replace('App\\Models\\', '')->replace('\\', '') . '#' . $movement->reference_id) :
                    null,
                'notes' => $movement->notes,
                'created_at' => $movement->created_at?->format('d/m/Y H:i'),
                'updated_at' => $movement->updated_at?->format('d/m/Y H:i'),
                'created_by' => $movement->createdBy?->name,
                'updated_by' => $movement->updatedBy?->name,

                // Dados específicos do item
                'raw_material' => $movement->rawMaterial ? [
                    'id' => $movement->rawMaterial->id,
                    'name' => $movement->rawMaterial->name,
                ] : null,
                'block_type' => $movement->blockType ? [
                    'id' => $movement->blockType->id,
                    'name' => $movement->blockType->name,
                ] : null,
                'mold_type' => $movement->moldType ? [
                    'id' => $movement->moldType->id,
                    'name' => $movement->moldType->name,
                ] : null,
                'dimensions' => $movement->length_mm ? [
                    'length_mm' => $movement->length_mm,
                    'width_mm' => $movement->width_mm,
                    'height_mm' => $movement->height_mm,
                ] : null,

                // Movimento de consumo relacionado
                'related_consumption' => $relatedConsumption ? [
                    'id' => $relatedConsumption->id,
                    'raw_material' => $relatedConsumption->rawMaterial ? [
                        'id' => $relatedConsumption->rawMaterial->id,
                        'name' => $relatedConsumption->rawMaterial->name,
                    ] : null,
                    'quantity' => $relatedConsumption->quantity,
                    'unit' => $relatedConsumption->unit,
                    'created_by' => $relatedConsumption->createdBy?->name,
                    'created_at' => $relatedConsumption->created_at?->format('d/m/Y H:i'),
                ] : null,
            ],
        ]);
    }

    // Página de dashboard: resumo + cargas de silos
    public function dashboard(Request $request): InertiaResponse
    {
        $this->authorize('viewAny', InventoryMovement::class);

        // Reaproveita as consultas do summary()
        $from = $request->query('from');
        $to = $request->query('to');

        $summary = $this->summary($request)->getData(true);

        // Cargas de silos
        $loads = $this->siloLoads()->getData(true)['data'] ?? [];

        return Inertia::render('Admin/Inventory/Dashboard', [
            'filters' => ['from' => $from, 'to' => $to],
            'summary' => $summary,
            'siloLoads' => $loads,
        ]);
    }

    // Página de movimentos: listagem
    public function movementsPage(Request $request): InertiaResponse
    {
        $this->authorize('viewAny', InventoryMovement::class);
        $raws = RawMaterial::query()->orderBy('name')->get(['id', 'name']);
        $silos = Silo::query()->orderBy('name')->get(['id', 'name']);

        // Filtros (mesmos da API)
        $query = InventoryMovement::query();
        if ($item = $request->query('item_type')) {
            $query->where('item_type', $item);
        }
        if ($direction = $request->query('direction')) {
            $query->where('direction', $direction);
        }
        if ($from = $request->query('from')) {
            $query->where('occurred_at', '>=', Carbon::parse($from));
        }
        if ($to = $request->query('to')) {
            $query->where('occurred_at', '<=', Carbon::parse($to));
        }
        $perPage = in_array((int) $request->query('per_page'), [10, 25, 50, 100], true) ? (int) $request->query('per_page') : 25;
        $paginator = $query->orderByDesc('occurred_at')->orderByDesc('id')->paginate($perPage)->withQueryString();

        return Inertia::render('Admin/Inventory/Index', [
            'rawMaterials' => $raws,
            'silos' => $silos,
            'paginator' => $paginator,
            'filters' => $request->only(['item_type', 'direction', 'from', 'to', 'per_page', 'page']),
        ]);
    }

    // Página: criar movimento manual
    public function createMovement(): InertiaResponse
    {
        $raws = RawMaterial::query()->orderBy('name')->get(['id', 'name']);
        $silos = Silo::query()->orderBy('name')->get(['id', 'name']);
        $blockTypes = \App\Models\BlockType::query()->orderBy('name')->get(['id', 'name']);
        $almoxarifados = Almoxarifado::query()->active()->orderBy('name')->get(['id', 'name']);
        $moldTypes = \App\Models\MoldType::query()->active()->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Inventory/Create', [
            'rawMaterials' => $raws,
            'silos' => $silos,
            'blockTypes' => $blockTypes,
            'almoxarifados' => $almoxarifados,
            'moldTypes' => $moldTypes,
        ]);
    }

    // Página: editar movimento manual
    public function editMovement(InventoryMovement $movement): InertiaResponse
    {
        $raws = RawMaterial::query()->orderBy('name')->get(['id', 'name']);
        $silos = Silo::query()->orderBy('name')->get(['id', 'name']);
        $blockTypes = \App\Models\BlockType::query()->orderBy('name')->get(['id', 'name']);
        $almoxarifados = Almoxarifado::query()->active()->orderBy('name')->get(['id', 'name']);
        $moldTypes = \App\Models\MoldType::query()->active()->orderBy('name')->get(['id', 'name']);

        $movement->load(['rawMaterial', 'silo', 'blockType', 'almoxarifado', 'moldType']);

        // Carregar movimento de consumo relacionado se existir
        $relatedConsumption = null;
        if ($movement->item_type === 'block' || $movement->item_type === 'molded') {
            $relatedConsumption = InventoryMovement::query()
                ->where('reference_type', 'inventory_movement')
                ->where('reference_id', $movement->id)
                ->where('direction', 'out')
                ->first();
        }
        if ($movement->item_type === 'block' && !$movement->block_type_id && $movement->item_id) {
            $blockProduction = \App\Models\BlockProduction::find($movement->item_id);
            if ($blockProduction) {
                $movement->block_type_id = $blockProduction->block_type_id;
                $movement->length_mm = $blockProduction->length_mm;
                $movement->width_mm = $blockProduction->width_mm;
                $movement->height_mm = $blockProduction->height_mm;
            }
        }

        $movementData = $movement->toArray();
        $movementData['occurred_at'] = $movement->occurred_at ? $movement->occurred_at->format('Y-m-d H:i') : null;

        return Inertia::render('Admin/Inventory/Edit', [
            'movement' => $movementData,
            'rawMaterials' => $raws,
            'silos' => $silos,
            'blockTypes' => $blockTypes,
            'almoxarifados' => $almoxarifados,
            'moldTypes' => $moldTypes,
            'relatedConsumption' => $relatedConsumption,
        ]);
    }

    // Criar movimento manual
    public function storeMovement(Request $request): JsonResponse
    {
        $this->authorize('create', InventoryMovement::class);
        $data = $request->validate([
            'occurred_at' => ['nullable', 'date'],
            'item_type' => ['required', 'in:raw_material,block,molded'],
            'raw_material_id' => ['nullable', 'exists:raw_materials,id'],
            'mold_type_id' => ['nullable', 'exists:mold_types,id'],
            'block_type_id' => ['nullable', 'exists:block_types,id'],
            'length_mm' => ['nullable', 'integer', 'min:0'],
            'width_mm' => ['nullable', 'integer', 'min:0'],
            'height_mm' => ['nullable', 'integer', 'min:0'],
            'consumed_raw_material_id' => ['nullable', 'exists:raw_materials,id'],
            'consumed_quantity_kg' => ['nullable', 'numeric', 'min:0'],
            'direction' => ['required', 'in:in,adjust,out'],
            'quantity' => ['required', 'numeric', $request->direction === 'adjust' ? null : 'min:0'],
            'location_type' => ['required', 'in:silo,almoxarifado,none'],
            'location_id' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        // Basic guard: when location != none, location_id must be provided
        if ($data['location_type'] !== 'none' && empty($data['location_id'])) {
            return response()->json(['message' => 'location_id é obrigatório para o tipo informado.'], 422);
        }

        $fields = [
            'occurred_at' => $data['occurred_at'] ?? Carbon::now(),
            'item_type' => $data['item_type'],
            'direction' => $data['direction'],
            'quantity' => (float) $data['quantity'],
            'location_type' => $data['location_type'],
            'location_id' => $data['location_id'] ?? null,
            'unit' => $data['item_type'] === 'raw_material' ? 'kg' : 'unidade',
            'reference_type' => null,
            'reference_id' => null,
            'notes' => $data['notes'] ?? null,
            'created_by' => Auth::id(),
        ];

        if ($data['item_type'] === 'raw_material') {
            $fields['item_id'] = (int) $data['raw_material_id'];
            $fields['block_type_id'] = null;
            $fields['length_mm'] = null;
            $fields['width_mm'] = null;
            $fields['height_mm'] = null;
        } elseif ($data['item_type'] === 'molded') {
            $fields['item_id'] = (int) $data['mold_type_id'];
            $fields['block_type_id'] = null;
            $fields['length_mm'] = null;
            $fields['width_mm'] = null;
            $fields['height_mm'] = null;
        } elseif ($data['item_type'] === 'block') {
            $fields['item_id'] = null;
            $fields['block_type_id'] = $data['block_type_id'] ?? null;
            $fields['length_mm'] = $data['length_mm'] ?? null;
            $fields['width_mm'] = $data['width_mm'] ?? null;
            $fields['height_mm'] = $data['height_mm'] ?? null;
        }

        $move = InventoryMovement::query()->create($fields);

        // Se for bloco ou moldado e foi especificada matéria-prima consumida, criar movimento de saída
        if (($data['item_type'] === 'block' || $data['item_type'] === 'molded') && !empty($data['consumed_raw_material_id']) && !empty($data['consumed_quantity_kg'])) {
            InventoryMovement::query()->create([
                'occurred_at' => $fields['occurred_at'],
                'item_type' => 'raw_material',
                'item_id' => (int) $data['consumed_raw_material_id'],
                'direction' => 'out',
                'quantity' => (float) $data['consumed_quantity_kg'],
                'location_type' => 'none', // Consumo não especifica localização
                'location_id' => null,
                'unit' => 'kg',
                'reference_type' => 'inventory_movement',
                'reference_id' => $move->id,
                'notes' => 'Consumo para produção de ' . ($data['item_type'] === 'block' ? 'bloco' : 'moldado') . ' - Movimento #' . $move->id,
                'created_by' => Auth::id(),
            ]);
        }

        return response()->json(['movement' => $move], 201);
    }

    // Atualizar movimento manual
    public function updateMovement(Request $request, InventoryMovement $movement): JsonResponse
    {
        $this->authorize('update', $movement);
        $data = $request->validate([
            'occurred_at' => ['nullable', 'date'],
            'item_type' => ['required', 'in:raw_material,block,molded'],
            'raw_material_id' => ['nullable', 'exists:raw_materials,id'],
            'mold_type_id' => ['nullable', 'exists:mold_types,id'],
            'block_type_id' => ['nullable', 'exists:block_types,id'],
            'length_mm' => ['nullable', 'integer', 'min:0'],
            'width_mm' => ['nullable', 'integer', 'min:0'],
            'height_mm' => ['nullable', 'integer', 'min:0'],
            'consumed_raw_material_id' => ['nullable', 'exists:raw_materials,id'],
            'consumed_quantity_kg' => ['nullable', 'numeric', 'min:0'],
            'direction' => ['required', 'in:in,adjust,out'],
            'quantity' => ['required', 'numeric', $request->direction === 'adjust' ? null : 'min:0'],
            'location_type' => ['required', 'in:silo,almoxarifado,none'],
            'location_id' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        if ($data['location_type'] !== 'none' && empty($data['location_id'])) {
            return response()->json(['message' => 'location_id é obrigatório para o tipo informado.'], 422);
        }

        $fields = [
            'occurred_at' => $data['occurred_at'] ?? Carbon::now(),
            'item_type' => $data['item_type'],
            'direction' => $data['direction'],
            'quantity' => (float) $data['quantity'],
            'location_type' => $data['location_type'],
            'location_id' => $data['location_id'] ?? null,
            'unit' => $data['item_type'] === 'raw_material' ? 'kg' : 'unidade',
            'reference_type' => null,
            'reference_id' => null,
            'notes' => $data['notes'] ?? null,
            'updated_by' => Auth::id(),
        ];

        if ($data['item_type'] === 'raw_material') {
            $fields['item_id'] = (int) $data['raw_material_id'];
            $fields['block_type_id'] = null;
            $fields['length_mm'] = null;
            $fields['width_mm'] = null;
            $fields['height_mm'] = null;
        } elseif ($data['item_type'] === 'molded') {
            $fields['item_id'] = (int) $data['mold_type_id'];
            $fields['block_type_id'] = null;
            $fields['length_mm'] = null;
            $fields['width_mm'] = null;
            $fields['height_mm'] = null;
        } elseif ($data['item_type'] === 'block') {
            $fields['item_id'] = null;
            $fields['block_type_id'] = $data['block_type_id'] ?? null;
            $fields['length_mm'] = $data['length_mm'] ?? null;
            $fields['width_mm'] = $data['width_mm'] ?? null;
            $fields['height_mm'] = $data['height_mm'] ?? null;
        }

        $movement->update($fields);

        // Gerenciar movimento de consumo relacionado para blocos e moldados
        if ($data['item_type'] === 'block' || $data['item_type'] === 'molded') {
            $relatedConsumption = InventoryMovement::query()
                ->where('reference_type', 'inventory_movement')
                ->where('reference_id', $movement->id)
                ->where('direction', 'out')
                ->first();

            if (!empty($data['consumed_raw_material_id']) && !empty($data['consumed_quantity_kg'])) {
                // Criar ou atualizar movimento de consumo
                if ($relatedConsumption) {
                    $relatedConsumption->update([
                        'occurred_at' => $fields['occurred_at'],
                        'item_id' => (int) $data['consumed_raw_material_id'],
                        'quantity' => (float) $data['consumed_quantity_kg'],
                        'notes' => 'Consumo para produção de ' . ($data['item_type'] === 'block' ? 'bloco' : 'moldado') . ' - Movimento #' . $movement->id,
                    ]);
                } else {
                    InventoryMovement::query()->create([
                        'occurred_at' => $fields['occurred_at'],
                        'item_type' => 'raw_material',
                        'item_id' => (int) $data['consumed_raw_material_id'],
                        'direction' => 'out',
                        'quantity' => (float) $data['consumed_quantity_kg'],
                        'location_type' => 'none',
                        'location_id' => null,
                        'unit' => 'kg',
                        'reference_type' => 'inventory_movement',
                        'reference_id' => $movement->id,
                        'notes' => 'Consumo para produção de ' . ($data['item_type'] === 'block' ? 'bloco' : 'moldado') . ' - Movimento #' . $movement->id,
                        'created_by' => Auth::id(),
                    ]);
                }
            } elseif ($relatedConsumption) {
                // Remover movimento de consumo se não há mais consumo especificado
                $relatedConsumption->delete();
            }
        }

        return response()->json(['movement' => $movement]);
    }

    // Exclusão não permitida por razões de auditoria
    public function destroyMovement(InventoryMovement $movement): JsonResponse
    {
        // O observer InventoryMovementObserver impede a exclusão e lança uma exception
        // Este método está mantido apenas por compatibilidade, mas nunca será executado com sucesso
        return response()->json(['error' => 'Movimentos de estoque não podem ser excluídos'], 403);
    }

    // Resumo: entradas MP, produção, consumo e perdas básicas
    public function summary(Request $request): JsonResponse
    {
        $this->authorize('viewAny', InventoryMovement::class);

        $from = $request->query('from') ? Carbon::parse($request->query('from')) : null;
        $to = $request->query('to') ? Carbon::parse($request->query('to')) : null;

        $dateFilter = function ($q) use ($from, $to): void {
            if ($from) {
                $q->where('occurred_at', '>=', $from);
            }
            if ($to) {
                $q->where('occurred_at', '<=', $to);
            }
        };

        $rawIn = (clone InventoryMovement::query())
            ->where('item_type', 'raw_material')->where('direction', 'in')
            ->when(true, $dateFilter)
            ->sum('quantity');

        $rawOut = (clone InventoryMovement::query())
            ->where('item_type', 'raw_material')->where('direction', 'out')
            ->when(true, $dateFilter)
            ->sum('quantity');

        $blocksInKg = (clone InventoryMovement::query())
            ->where('item_type', 'block')->where('direction', 'in')
            ->when(true, $dateFilter)
            ->sum('quantity');

        $moldedInKg = (clone InventoryMovement::query())
            ->where('item_type', 'molded')->where('direction', 'in')
            ->when(true, $dateFilter)
            ->sum('quantity');

        $blockLossKg = (clone InventoryMovement::query())
            ->where('item_type', 'block')->where('direction', 'adjust')
            ->when(true, $dateFilter)
            ->sum(DB::raw('CASE WHEN quantity < 0 THEN -quantity ELSE 0 END'));

        return response()->json([
            'from' => $from?->toDateTimeString(),
            'to' => $to?->toDateTimeString(),
            'raw_material_input_kg' => (float) $rawIn,
            'raw_material_consumed_kg' => (float) $rawOut,
            'blocks_produced_units' => (float) $blocksInKg,
            'molded_produced_units' => (float) $moldedInKg,
            'block_loss_units' => (float) $blockLossKg,
        ]);
    }

    // Listagem de movimentos (JSON paginado)
    public function movements(Request $request): JsonResponse
    {
        $this->authorize('viewAny', InventoryMovement::class);

        $query = InventoryMovement::query();
        if ($item = $request->query('item_type')) {
            $query->where('item_type', $item);
        }
        if ($direction = $request->query('direction')) {
            $query->where('direction', $direction);
        }
        if ($from = $request->query('from')) {
            $query->where('occurred_at', '>=', Carbon::parse($from));
        }
        if ($to = $request->query('to')) {
            $query->where('occurred_at', '<=', Carbon::parse($to));
        }
        $perPage = in_array((int) $request->query('per_page'), [10, 25, 50, 100], true) ? (int) $request->query('per_page') : 25;
        $rows = $query->orderByDesc('occurred_at')->orderByDesc('id')->paginate($perPage)->withQueryString();

        return response()->json($rows);
    }

    // Carga atual por silo (por matéria-prima)
    public function siloLoads(): JsonResponse
    {
        $this->authorize('viewAny', InventoryMovement::class);

        $silos = Silo::query()->orderBy('name')->get(['id', 'name']);
        $raws = RawMaterial::query()->orderBy('name')->get(['id', 'name']);

        $loads = [];
        foreach ($silos as $silo) {
            $entry = [
                'silo_id' => $silo->id,
                'silo_name' => $silo->name,
                'materials' => [],
            ];
            foreach ($raws as $rm) {
                $in = InventoryMovement::query()
                    ->where(['item_type' => 'raw_material', 'item_id' => $rm->id, 'location_type' => 'silo', 'location_id' => $silo->id, 'direction' => 'in'])
                    ->sum('quantity');
                $out = InventoryMovement::query()
                    ->where(['item_type' => 'raw_material', 'item_id' => $rm->id, 'location_type' => 'silo', 'location_id' => $silo->id, 'direction' => 'out'])
                    ->sum('quantity');
                $adjust = InventoryMovement::query()
                    ->where(['item_type' => 'raw_material', 'item_id' => $rm->id, 'location_type' => 'silo', 'location_id' => $silo->id, 'direction' => 'adjust'])
                    ->sum('quantity');
                $balance = (float) $in - (float) $out + (float) $adjust;
                if (abs($balance) > 0.0001) {
                    $entry['materials'][] = [
                        'raw_material_id' => $rm->id,
                        'raw_material_name' => $rm->name,
                        'balance_kg' => $balance,
                    ];
                }
            }
            $loads[] = $entry;
        }

        return response()->json(['data' => $loads]);
    }

    // Entrada/ajuste manual de matéria-prima (ex.: compras ou abastecimento de silo)
    public function storeRawMaterialMovement(Request $request): JsonResponse
    {
        $data = $request->validate([
            'occurred_at' => ['nullable', 'date'],
            'item_type' => ['required', 'in:raw_material,block,molded'],
            'raw_material_id' => ['nullable', 'exists:raw_materials,id'],
            'mold_type_id' => ['nullable', 'exists:mold_types,id'],
            'block_type_id' => ['nullable', 'exists:block_types,id'],
            'length_mm' => ['nullable', 'integer', 'min:0'],
            'width_mm' => ['nullable', 'integer', 'min:0'],
            'height_mm' => ['nullable', 'integer', 'min:0'],
            'consumed_raw_material_id' => ['nullable', 'exists:raw_materials,id'],
            'consumed_quantity_kg' => ['nullable', 'numeric', 'min:0'],
            'direction' => ['required', 'in:in,adjust,out'],
            'quantity' => ['required', 'numeric', $request->direction === 'adjust' ? null : 'min:0'],
            'location_type' => ['required', 'in:silo,almoxarifado,none'],
            'location_id' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        // Basic guard: when location != none, location_id must be provided
        if ($data['location_type'] !== 'none' && empty($data['location_id'])) {
            return response()->json(['message' => 'location_id é obrigatório para o tipo informado.'], 422);
        }

        $fields = [
            'occurred_at' => $data['occurred_at'] ?? Carbon::now(),
            'item_type' => $data['item_type'],
            'direction' => $data['direction'],
            'quantity' => (float) $data['quantity'],
            'location_type' => $data['location_type'],
            'location_id' => $data['location_id'] ?? null,
            'unit' => $data['item_type'] === 'raw_material' ? 'kg' : 'unidade',
            'reference_type' => null,
            'reference_id' => null,
            'notes' => $data['notes'] ?? null,
            'created_by' => Auth::id(),
        ];

        if ($data['item_type'] === 'raw_material') {
            $fields['item_id'] = (int) $data['raw_material_id'];
        } elseif ($data['item_type'] === 'molded') {
            $fields['item_id'] = (int) $data['mold_type_id'];
        } elseif ($data['item_type'] === 'block') {
            $fields['block_type_id'] = $data['block_type_id'] ?? null;
            $fields['length_mm'] = $data['length_mm'] ?? null;
            $fields['width_mm'] = $data['width_mm'] ?? null;
            $fields['height_mm'] = $data['height_mm'] ?? null;
        }

        $move = InventoryMovement::query()->create($fields);

        // Se for bloco ou moldado e foi especificada matéria-prima consumida, criar movimento de saída
        if (($data['item_type'] === 'block' || $data['item_type'] === 'molded') && !empty($data['consumed_raw_material_id']) && !empty($data['consumed_quantity_kg'])) {
            InventoryMovement::query()->create([
                'occurred_at' => $fields['occurred_at'],
                'item_type' => 'raw_material',
                'item_id' => (int) $data['consumed_raw_material_id'],
                'direction' => 'out',
                'quantity' => (float) $data['consumed_quantity_kg'],
                'location_type' => 'none', // Consumo não especifica localização
                'location_id' => null,
                'unit' => 'kg',
                'reference_type' => 'inventory_movement',
                'reference_id' => $move->id,
                'notes' => 'Consumo para produção de ' . ($data['item_type'] === 'block' ? 'bloco' : 'moldado') . ' - Movimento #' . $move->id,
                'created_by' => Auth::id(),
            ]);
        }

        return response()->json(['movement' => $move], 201);
    }
}
