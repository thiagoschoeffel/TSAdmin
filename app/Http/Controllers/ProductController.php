<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ProductController extends Controller
{
    public function index(): InertiaResponse
    {
        $query = Product::with('components');

        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($status = request('status')) {
            $query->where('status', $status);
        }

        $products = $query->paginate(15)->withQueryString();

        return Inertia::render('Admin/Products/Index', [
            'products' => $products,
            'filters' => request()->only(['search', 'status']),
        ]);
    }

    public function create(): InertiaResponse
    {
        $products = Product::all();
        return Inertia::render('Admin/Products/Create', [
            'products' => $products,
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $product = Product::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'unit_of_measure' => $data['unit_of_measure'],
            'status' => $data['status'] ?? 'active',
            'created_by' => Auth::id(),
        ]);
        if (!empty($data['components'])) {
            $syncData = collect($data['components'])->mapWithKeys(function ($item) {
                return [$item['id'] => ['quantity' => $item['quantity']]];
            })->toArray();
            $product->components()->sync($syncData);
        }
        return redirect()->route('products.index')->with('status', 'Produto criado com sucesso!');
    }

    public function edit(Product $product): InertiaResponse
    {
        $product->load('components');
        $products = Product::where('id', '!=', $product->id)->get();
        return Inertia::render('Admin/Products/Edit', [
            'product' => $product,
            'products' => $products,
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();
        $product->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'unit_of_measure' => $data['unit_of_measure'],
            'status' => $data['status'] ?? 'active',
            'updated_by' => Auth::id(),
        ]);
        if (!empty($data['components'])) {
            $syncData = collect($data['components'])->mapWithKeys(function ($item) {
                return [$item['id'] => ['quantity' => $item['quantity']]];
            })->toArray();
            $product->components()->sync($syncData);
        } else {
            $product->components()->detach();
        }
        return redirect()->route('products.index')->with('status', 'Produto atualizado com sucesso!');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();
        return redirect()->route('products.index')->with('status', 'Produto removido com sucesso!');
    }

    public function modal(Product $product): JsonResponse
    {
        $this->authorize('view', $product);
        $product->load(['createdBy', 'updatedBy', 'components.components']);

        return response()->json([
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->formattedPrice(),
                'status' => $product->status,
                'created_at' => $product->created_at?->format('d/m/Y H:i'),
                'updated_at' => $product->updated_at?->format('d/m/Y H:i'),
                'created_by' => $product->createdBy?->name,
                'updated_by' => $product->updatedBy?->name,
                'components' => $product->components->map(function ($component) {
                    return [
                        'id' => $component->id,
                        'name' => $component->name,
                        'quantity' => $component->pivot->quantity,
                        'unit_of_measure' => $component->unit_of_measure,
                        'price' => $component->formattedPrice(),
                        'total' => 'R$ ' . number_format($component->price * $component->pivot->quantity, 2, ',', '.'),
                        'status' => $component->status,
                        'created_at' => $component->created_at?->format('d/m/Y H:i'),
                        'updated_at' => $component->updated_at?->format('d/m/Y H:i'),
                        'created_by' => $component->createdBy?->name,
                        'updated_by' => $component->updatedBy?->name,
                    ];
                }),
                'component_tree' => $this->buildComponentTree($product),
            ],
        ]);
    }

    private function buildComponentTree(Product $product, $level = 0, $visited = []): array
    {
        if (in_array($product->id, $visited)) {
            return []; // Evita loops infinitos
        }

        $visited[] = $product->id;
        $tree = [];

        foreach ($product->components as $component) {
            $tree[] = [
                'id' => $component->id,
                'name' => $component->name,
                'quantity' => $component->pivot->quantity,
                'unit_of_measure' => $component->unit_of_measure,
                'price' => $component->formattedPrice(),
                'total' => 'R$ ' . number_format($component->price * $component->pivot->quantity, 2, ',', '.'),
                'status' => $component->status,
                'level' => $level,
                'has_children' => $component->components->count() > 0,
                'children' => $this->buildComponentTree($component, $level + 1, $visited),
            ];
        }

        return $tree;
    }
}
