<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductComponentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
        $this->actingAs($this->admin);
    }

    public function test_index_lists_components_ordered_desc_by_pivot_id()
    {
        $product = Product::factory()->create(['status' => 'active']);
        $a = Product::factory()->create(['name' => 'A', 'price' => 5.00, 'unit_of_measure' => 'UND']);
        $b = Product::factory()->create(['name' => 'B', 'price' => 7.50, 'unit_of_measure' => 'UND']);

        $product->components()->attach($a->id, ['quantity' => 1]);
        $product->components()->attach($b->id, ['quantity' => 2]); // maior pivot.id => deve vir primeiro

        $response = $this->get(route('products.components.index', $product));
        $response->assertStatus(200);
        $response->assertJsonStructure(['components']);

        $components = collect($response->json('components'));
        $this->assertEquals('B', $components[0]['name']);
        $this->assertEquals('R$ 7,50', $components[0]['price']);
        $this->assertEquals('R$ 15,00', $components[0]['total']);
        $this->assertEquals(2, $components[0]['quantity']);
    }

    public function test_store_adds_component_successfully()
    {
        $product = Product::factory()->create(['status' => 'active']);
        $comp = Product::factory()->create(['name' => 'Arroz', 'price' => 6.00, 'unit_of_measure' => 'UND']);

        $payload = [
            'component_id' => $comp->id,
            'quantity' => 3,
        ];

        $response = $this->post(route('products.components.store', $product), $payload);
        $response->assertStatus(201);
        $response->assertJsonStructure(['component' => ['id', 'name', 'quantity', 'price', 'total']]);

        $this->assertDatabaseHas('product_components', [
            'product_id' => $product->id,
            'component_id' => $comp->id,
            'quantity' => 3,
        ]);
    }

    public function test_store_rejects_duplicate_component()
    {
        $product = Product::factory()->create(['status' => 'active']);
        $comp = Product::factory()->create();
        $product->components()->attach($comp->id, ['quantity' => 1]);

        $response = $this->post(route('products.components.store', $product), [
            'component_id' => $comp->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(422);
        $response->assertJson(['message' => 'Este componente já foi adicionado ao produto.']);
    }

    public function test_store_rejects_self_component()
    {
        $product = Product::factory()->create(['status' => 'active']);
        $response = $this->post(route('products.components.store', $product), [
            'component_id' => $product->id,
            'quantity' => 1,
        ]);
        $response->assertStatus(422);
        $response->assertJson(['message' => 'Um produto não pode ser componente de si mesmo.']);
    }

    public function test_store_rejects_circular_dependency()
    {
        $a = Product::factory()->create(['name' => 'A']);
        $b = Product::factory()->create(['name' => 'B']);
        // B depende de A
        $b->components()->attach($a->id, ['quantity' => 1]);

        // Adicionar B a A criaria ciclo
        $response = $this->post(route('products.components.store', $a), [
            'component_id' => $b->id,
            'quantity' => 1,
        ]);
        $response->assertStatus(422);
        $response->assertJson(['message' => 'Esta adição criaria uma dependência circular.']);
    }

    public function test_update_updates_quantity_and_returns_component()
    {
        $product = Product::factory()->create();
        $comp = Product::factory()->create(['price' => 2.5]);
        $product->components()->attach($comp->id, ['quantity' => 2]);

        $response = $this->patch(route('products.components.update', [$product, $comp->id]), [
            'quantity' => 4,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['component' => ['id' => $comp->id, 'quantity' => 4, 'total' => 'R$ 10,00']]);

        $this->assertDatabaseHas('product_components', [
            'product_id' => $product->id,
            'component_id' => $comp->id,
            'quantity' => 4,
        ]);
    }

    public function test_destroy_detaches_component()
    {
        $product = Product::factory()->create();
        $comp = Product::factory()->create();
        $product->components()->attach($comp->id, ['quantity' => 2]);

        $response = $this->delete(route('products.components.destroy', [$product, $comp->id]));
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Componente removido com sucesso.']);

        $this->assertDatabaseMissing('product_components', [
            'product_id' => $product->id,
            'component_id' => $comp->id,
        ]);
    }

    public function test_non_admin_without_permissions_cannot_access_endpoints()
    {
        $product = Product::factory()->create();
        $comp = Product::factory()->create();

        $user = User::factory()->create([
            'role' => 'user',
            'email_verified_at' => now(),
            'permissions' => [
                'products' => [
                    'view' => false,
                    'create' => false,
                    'update' => false,
                    'delete' => false,
                ],
            ],
        ]);

        $this->actingAs($user);

        $this->get(route('products.components.index', $product))->assertStatus(403);
        $this->post(route('products.components.store', $product), ['component_id' => $comp->id, 'quantity' => 1])->assertStatus(403);
        $this->patch(route('products.components.update', [$product, $comp->id]), ['quantity' => 1])->assertStatus(403);
        $this->delete(route('products.components.destroy', [$product, $comp->id]))->assertStatus(403);
    }
}

