<?php

namespace App\Http\Controllers;

use App\Models\Opportunity;
use App\Models\Lead;
use App\Models\Client;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class OpportunityController extends Controller
{
    public function index(): InertiaResponse|\Illuminate\Http\Response
    {
        $this->authorize('viewAny', Opportunity::class);
        $allowedPerPage = [10, 25, 50, 100];
        $perPageCandidate = (int) request()->integer('per_page');
        $perPage = in_array($perPageCandidate, $allowedPerPage, true) ? $perPageCandidate : 10;

        $opportunities = Opportunity::with(['lead', 'client', 'owner', 'items.product'])
            ->orderByDesc('created_at')->paginate($perPage)->withQueryString();

        $requestedPage = max(1, (int) request()->query('page', 1));
        if ($requestedPage > $opportunities->lastPage() && $opportunities->lastPage() > 0) {
            $queryParams = request()->query();
            $queryParams['page'] = $opportunities->lastPage();
            return Inertia::location(request()->url() . '?' . http_build_query($queryParams));
        }

        return Inertia::render('Admin/Opportunities/Index', [
            'opportunities' => $opportunities,
        ]);
    }

    public function create(): InertiaResponse
    {
        $this->authorize('create', Opportunity::class);
        $leads = Lead::all();
        $clients = Client::all();
        $products = Product::all();
        return Inertia::render('Admin/Opportunities/Create', [
            'leads' => $leads,
            'clients' => $clients,
            'products' => $products,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Opportunity::class);
        $data = $request->validate([
            'lead_id' => 'nullable|exists:leads,id',
            'client_id' => 'nullable|exists:clients,id',
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'stage' => 'required|in:new,contact,proposal,negotiation,won,lost',
            'probability' => 'required|integer|min:0|max:100',
            'expected_value' => 'required|numeric|min:0',
            'expected_close_date' => 'nullable|date',
            'status' => 'required|in:active,inactive',
        ]);
        $data['owner_id'] = Auth::id();
        $opportunity = Opportunity::create($data);
        // Itens
        if ($request->has('items')) {
            foreach ($request->input('items') as $item) {
                $opportunity->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                ]);
            }
        }
        return redirect()->route('opportunities.index')->with('status', 'Oportunidade criada com sucesso!');
    }

    public function modal(Opportunity $opportunity): JsonResponse
    {
        $this->authorize('view', $opportunity);
        $opportunity->load(['lead', 'client', 'owner', 'items.product']);

        return response()->json([
            'opportunity' => [
                'id' => $opportunity->id,
                'title' => $opportunity->title,
                'description' => $opportunity->description,
                'stage' => $opportunity->stage,
                'probability' => $opportunity->probability,
                'expected_value' => $opportunity->expected_value,
                'expected_close_date' => $opportunity->expected_close_date?->format('d/m/Y'),
                'status' => $opportunity->status,
                'lead' => $opportunity->lead ? [
                    'id' => $opportunity->lead->id,
                    'name' => $opportunity->lead->name,
                    'email' => $opportunity->lead->email,
                ] : null,
                'client' => $opportunity->client ? [
                    'id' => $opportunity->client->id,
                    'name' => $opportunity->client->name,
                    'email' => $opportunity->client->email,
                ] : null,
                'owner' => $opportunity->owner ? ['name' => $opportunity->owner->name] : null,
                'items' => $opportunity->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product' => $item->product ? [
                            'id' => $item->product->id,
                            'name' => $item->product->name,
                        ] : null,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'subtotal' => $item->subtotal,
                    ];
                }),
                'created_at' => $opportunity->created_at?->format('d/m/Y H:i'),
                'updated_at' => $opportunity->updated_at?->format('d/m/Y H:i'),
            ],
        ]);
    }

    public function edit(Opportunity $opportunity): InertiaResponse
    {
        $this->authorize('update', $opportunity);
        $leads = Lead::all();
        $clients = Client::all();
        $products = Product::all();
        $opportunity->load(['items']);
        return Inertia::render('Admin/Opportunities/Edit', [
            'opportunity' => $opportunity,
            'leads' => $leads,
            'clients' => $clients,
            'products' => $products,
        ]);
    }

    public function update(Request $request, Opportunity $opportunity): RedirectResponse
    {
        $this->authorize('update', $opportunity);
        $data = $request->validate([
            'lead_id' => 'nullable|exists:leads,id',
            'client_id' => 'nullable|exists:clients,id',
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'stage' => 'required|in:new,contact,proposal,negotiation,won,lost',
            'probability' => 'required|integer|min:0|max:100',
            'expected_value' => 'required|numeric|min:0',
            'expected_close_date' => 'nullable|date',
            'status' => 'required|in:active,inactive',
        ]);
        $opportunity->update($data);
        // Atualizar itens
        $opportunity->items()->delete();
        if ($request->has('items')) {
            foreach ($request->input('items') as $item) {
                $opportunity->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                ]);
            }
        }
        return redirect()->route('opportunities.index')->with('status', 'Oportunidade atualizada com sucesso!');
    }

    public function destroy(Opportunity $opportunity): RedirectResponse
    {
        $this->authorize('delete', $opportunity);
        $opportunity->delete();
        return redirect()->route('opportunities.index')->with('status', 'Oportunidade removida com sucesso!');
    }
}
