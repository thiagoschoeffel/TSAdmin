<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
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
            'status' => $data['status'] ?? 'active',
            'created_by' => auth()->id(),
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
            'status' => $data['status'] ?? 'active',
            'updated_by' => auth()->id(),
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
}
