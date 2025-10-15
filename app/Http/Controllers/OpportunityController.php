<?php

namespace App\Http\Controllers;

use App\Models\Opportunity;
use App\Models\Lead;
use App\Models\Client;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class OpportunityController extends Controller
{
    public function index(): InertiaResponse
    {
        $opportunities = Opportunity::with(['lead', 'client', 'owner', 'items.product'])
            ->orderByDesc('created_at')->paginate(15);
        return Inertia::render('Admin/Opportunities/Index', [
            'opportunities' => $opportunities,
        ]);
    }

    public function create(): InertiaResponse
    {
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

    public function edit(Opportunity $opportunity): InertiaResponse
    {
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
        $opportunity->delete();
        return redirect()->route('opportunities.index')->with('status', 'Oportunidade removida com sucesso!');
    }
}
