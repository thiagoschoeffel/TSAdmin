<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_clients(): void
    {
        $this->get(route('clients.index'))
            ->assertRedirect(route('login'));
    }

    public function test_can_create_individual_client_with_minimum_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('clients.store'), [
            'name' => 'João da Silva',
            'person_type' => 'individual',
            'document' => '123.456.789-09',
            'observations' => 'Cliente preferencial.',
            'postal_code' => '12345-678',
            'address' => 'Rua das Flores',
            'address_number' => '123',
            'address_complement' => '',
            'neighborhood' => 'Centro',
            'city' => 'São Paulo',
            'state' => 'sp',
            'contact_name' => null,
            'contact_phone_primary' => '11987654321',
            'contact_phone_secondary' => null,
            'contact_email' => null,
            'status' => 'active',
        ]);

        $client = Client::first();

        $response->assertRedirect(route('clients.show', $client));
        $this->assertNotNull($client);
        $this->assertEquals('12345678909', $client->document);
        $this->assertEquals('SP', $client->state);
        $this->assertEquals($user->id, $client->created_by_id);
        $this->assertNull($client->updated_by_id);
    }

    public function test_company_requires_contact_information(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('clients.store'), [
            'name' => 'Empresa LTDA',
            'person_type' => 'company',
            'document' => '12.345.678/0001-99',
            'postal_code' => '12345-678',
            'address' => 'Rua Comercial',
            'address_number' => '45',
            'neighborhood' => 'Centro',
            'city' => 'Campinas',
            'state' => 'SP',
            'status' => 'inactive',
        ]);

        $response->assertSessionHasErrors([
            'contact_name',
            'contact_phone_primary',
            'contact_phone_secondary',
            'contact_email',
        ]);
        $this->assertDatabaseCount('clients', 0);
    }

    public function test_can_update_client_and_register_updater(): void
    {
        $creator = User::factory()->create();
        $client = Client::factory()->create([
            'person_type' => 'company',
            'document' => '12345678000199',
            'created_by_id' => $creator->id,
        ]);

        $editor = User::factory()->create();

        $response = $this->actingAs($editor)->patch(route('clients.update', $client), [
            'name' => 'Empresa Atualizada',
            'person_type' => 'company',
            'document' => '98.765.432/0001-11',
            'observations' => 'Nova observação',
            'postal_code' => '87654-321',
            'address' => 'Avenida Central',
            'address_number' => '500',
            'address_complement' => 'Sala 12',
            'neighborhood' => 'Industrial',
            'city' => 'Rio de Janeiro',
            'state' => 'rj',
            'contact_name' => 'Maria',
            'contact_phone_primary' => '21988887777',
            'contact_phone_secondary' => '21977776666',
            'contact_email' => 'contato@empresa.com',
            'status' => 'inactive',
        ]);

        $response->assertRedirect(route('clients.show', $client));

        $client->refresh();
        $this->assertEquals('98765432000111', $client->document);
        $this->assertEquals('RJ', $client->state);
        $this->assertEquals('Maria', $client->contact_name);
        $this->assertEquals($editor->id, $client->updated_by_id);
    }

    public function test_can_delete_client(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create([
            'created_by_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->delete(route('clients.destroy', $client));

        $response->assertRedirect(route('clients.index'));
        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    }
}
