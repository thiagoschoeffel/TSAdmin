<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email_verified_at' => now(), // Ensure email is verified
            'permissions' => ['clients' => ['view' => true]],
        ]);

        $this->admin = User::factory()->create([
            'email_verified_at' => now(), // Ensure email is verified
            'role' => 'admin', // Make it an admin user
            'permissions' => ['clients' => ['*' => true]], // Give all permissions
        ]);
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->get(route('clients.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_index_requires_view_permission(): void
    {
        $user = User::factory()->create([
            'permissions' => [], // No permissions
        ]);

        $response = $this->actingAs($user)->get(route('clients.index'));

        $response->assertForbidden();
    }

    public function test_index_returns_clients_list(): void
    {
        Client::factory()->count(3)->create();

        $response = $this->actingAs($this->user)->get(route('clients.index'));

        $response->assertInertia(function ($page) {
            $page->component('Admin/Clients/Index')
                ->has('clients.data', 3)
                ->has('filters');
        });
    }

    public function test_index_filters_by_search(): void
    {
        Client::factory()->create(['name' => 'João Silva']);
        Client::factory()->create(['name' => 'Maria Santos']);

        $response = $this->actingAs($this->user)->get(route('clients.index', ['search' => 'João']));

        $response->assertInertia(function ($page) {
            $page->component('Admin/Clients/Index')
                ->has('clients.data', 1)
                ->where('clients.data.0.name', 'João Silva');
        });
    }

    public function test_index_filters_by_person_type(): void
    {
        Client::factory()->create(['person_type' => 'individual']);
        Client::factory()->create(['person_type' => 'company']);

        $response = $this->actingAs($this->user)->get(route('clients.index', ['person_type' => 'individual']));

        $response->assertInertia(function ($page) {
            $page->component('Admin/Clients/Index')
                ->has('clients.data', 1)
                ->where('clients.data.0.person_type', 'individual');
        });
    }

    public function test_index_filters_by_status(): void
    {
        Client::factory()->create(['status' => 'active']);
        Client::factory()->create(['status' => 'inactive']);

        $response = $this->actingAs($this->user)->get(route('clients.index', ['status' => 'active']));

        $response->assertInertia(function ($page) {
            $page->component('Admin/Clients/Index')
                ->has('clients.data', 1)
                ->where('clients.data.0.status', 'active');
        });
    }

    public function test_create_requires_authentication(): void
    {
        $response = $this->get(route('clients.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_create_requires_create_permission(): void
    {
        $user = User::factory()->create([
            'permissions' => ['clients' => ['view' => true]], // Only view permission
        ]);

        $response = $this->actingAs($user)->get(route('clients.create'));

        $response->assertForbidden();
    }

    public function test_create_returns_create_form(): void
    {
        $response = $this->actingAs($this->admin)->get(route('clients.create'));

        $response->assertInertia(function ($page) {
            $page->component('Admin/Clients/Create')
                ->has('states');
        });
    }

    public function test_store_requires_authentication(): void
    {
        $response = $this->post(route('clients.store'));

        $response->assertRedirect(route('login'));
    }

    public function test_store_creates_client(): void
    {
        $clientData = [
            'name' => 'João Silva',
            'person_type' => 'individual',
            'document' => '12345678901',
            'status' => 'active',
        ];

        $response = $this->actingAs($this->admin)->post(route('clients.store'), $clientData);

        $response->assertRedirect(route('clients.index'))
            ->assertSessionHas('status', 'Cliente cadastrado com sucesso.');

        $this->assertDatabaseHas('clients', $clientData);
    }

    public function test_modal_requires_authentication(): void
    {
        $client = Client::factory()->create();

        $response = $this->get(route('clients.modal', $client));

        $response->assertRedirect(route('login'));
    }

    public function test_modal_returns_client_data(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->user)->get(route('clients.modal', $client));

        $response->assertJsonStructure([
            'client' => [
                'id',
                'name',
                'person_type',
                'document',
                'status',
                'addresses',
            ],
        ]);
    }

    public function test_edit_requires_authentication(): void
    {
        $client = Client::factory()->create();

        $response = $this->get(route('clients.edit', $client));

        $response->assertRedirect(route('login'));
    }

    public function test_edit_requires_update_permission(): void
    {
        $client = Client::factory()->create();
        $user = User::factory()->create([
            'permissions' => ['clients' => ['view' => true]], // Only view permission
        ]);

        $response = $this->actingAs($user)->get(route('clients.edit', $client));

        $response->assertForbidden();
    }

    public function test_edit_returns_edit_form(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->admin)->get(route('clients.edit', $client));

        $response->assertInertia(function ($page) {
            $page->component('Admin/Clients/Edit')
                ->has('states')
                ->has('client');
        });
    }

    public function test_update_requires_authentication(): void
    {
        $client = Client::factory()->create();

        $response = $this->patch(route('clients.update', $client));

        $response->assertRedirect(route('login'));
    }

    public function test_update_modifies_client(): void
    {
        $client = Client::factory()->create();
        $updateData = [
            'name' => 'Maria Santos',
            'person_type' => 'individual',
            'document' => '98765432100',
            'status' => 'active',
        ];

        $response = $this->actingAs($this->admin)->patch(route('clients.update', $client), $updateData);

        $response->assertRedirect(route('clients.index'))
            ->assertSessionHas('status', 'Cliente atualizado com sucesso.');

        $this->assertDatabaseHas('clients', array_merge($updateData, ['id' => $client->id]));
    }

    public function test_destroy_requires_authentication(): void
    {
        $client = Client::factory()->create();

        $response = $this->delete(route('clients.destroy', $client));

        $response->assertRedirect(route('login'));
    }

    public function test_destroy_requires_delete_permission(): void
    {
        $client = Client::factory()->create();
        $user = User::factory()->create([
            'permissions' => ['clients' => ['view' => true]], // Only view permission
        ]);

        $response = $this->actingAs($user)->delete(route('clients.destroy', $client));

        $response->assertForbidden();
    }

    public function test_destroy_deletes_client(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('clients.destroy', $client));

        $response->assertRedirect(route('clients.index'))
            ->assertSessionHas('status', 'Cliente removido com sucesso.');

        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    }
}
