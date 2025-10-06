<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request): View|\Inertia\Response
    {
        abort_unless($request->user()->canManage('clients', 'view'), 403);
        $query = Client::query();

        if ($search = $request->string('search')->toString()) {
            $digits = preg_replace('/\D+/', '', $search);

            $query->where(function ($inner) use ($search, $digits): void {
                $inner->where('name', 'like', "%{$search}%");

                if ($digits) {
                    $inner->orWhere('document', 'like', "%{$digits}%");
                }
            });
        }

        if ($personType = $request->get('person_type')) {
            $query->where('person_type', $personType);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $clients = $query
            ->with(['createdBy', 'updatedBy'])
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString()
            ->through(function (Client $client) {
                return [
                    'id' => $client->id,
                    'name' => $client->name,
                    'person_type' => $client->person_type,
                    'status' => $client->status,
                    'document' => $client->document,
                    'formatted_document' => $client->formattedDocument(),
                    'city' => $client->city,
                    'state' => $client->state,
                    'created_at' => optional($client->created_at)->format('d/m/Y H:i'),
                ];
            });

        if (class_exists(\Inertia\Inertia::class)) {
            return \Inertia\Inertia::render('Admin/Clients/Index', [
                'filters' => [
                    'search' => $request->string('search')->toString(),
                    'person_type' => $request->get('person_type'),
                    'status' => $request->get('status'),
                ],
                'clients' => $clients,
            ]);
        }

        return view('clients.index', compact('clients'));
    }

    public function create(): View|\Inertia\Response
    {
        abort_unless(auth()->user()->canManage('clients', 'create'), 403);
        if (class_exists(\Inertia\Inertia::class)) {
            return \Inertia\Inertia::render('Admin/Clients/Create', [
                'states' => [
                    'AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO',
                ],
            ]);
        }
        return view('clients.create');
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        abort_unless($request->user()->canManage('clients', 'create'), 403);
        $data = $this->preparePayload($request->validated());

        $client = Client::create(array_merge($data, [
            'created_by_id' => Auth::id(),
        ]));

        return redirect()
            ->route('clients.show', $client)
            ->with('status', 'Cliente cadastrado com sucesso.');
    }

    public function show(Client $client): View
    {
        abort_unless(auth()->user()->canManage('clients', 'view'), 403);
        $client->load(['createdBy', 'updatedBy']);

        return view('clients.show', compact('client'));
    }

    public function modal(Client $client): JsonResponse
    {
        abort_unless(auth()->user()->canManage('clients', 'view'), 403);
        $client->load(['createdBy', 'updatedBy']);

        return response()->json([
            'html' => view('clients.partials.details-modal', compact('client'))->render(),
        ]);
    }

    public function edit(Client $client): View|\Inertia\Response
    {
        abort_unless(auth()->user()->canManage('clients', 'update'), 403);
        if (class_exists(\Inertia\Inertia::class)) {
            return \Inertia\Inertia::render('Admin/Clients/Edit', [
                'states' => [
                    'AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO',
                ],
                'client' => [
                    'id' => $client->id,
                    'name' => $client->name,
                    'person_type' => $client->person_type,
                    'document' => $client->formattedDocument(),
                    'observations' => $client->observations,
                    'postal_code' => $client->formattedPostalCode(),
                    'address' => $client->address,
                    'address_number' => $client->address_number,
                    'address_complement' => $client->address_complement,
                    'neighborhood' => $client->neighborhood,
                    'city' => $client->city,
                    'state' => $client->state,
                    'contact_name' => $client->contact_name,
                    'contact_phone_primary' => $client->formattedPhone($client->contact_phone_primary),
                    'contact_phone_secondary' => $client->formattedPhone($client->contact_phone_secondary),
                    'contact_email' => $client->contact_email,
                    'status' => $client->status,
                ],
            ]);
        }
        return view('clients.edit', compact('client'));
    }

    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        abort_unless($request->user()->canManage('clients', 'update'), 403);
        $data = $this->preparePayload($request->validated());

        $client->fill($data);
        $client->updated_by_id = Auth::id();
        $client->save();

        return redirect()
            ->route('clients.show', $client)
            ->with('status', 'Cliente atualizado com sucesso.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        abort_unless(auth()->user()->canManage('clients', 'delete'), 403);
        $client->delete();

        return redirect()
            ->route('clients.index')
            ->with('status', 'Cliente removido com sucesso.');
    }

    protected function preparePayload(array $data): array
    {
        $data['state'] = strtoupper($data['state']);

        $data['contact_email'] = isset($data['contact_email']) && $data['contact_email'] !== ''
            ? strtolower($data['contact_email'])
            : null;

        return $data;
    }
}
