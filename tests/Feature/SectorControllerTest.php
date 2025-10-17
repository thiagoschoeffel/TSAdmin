<?php

namespace Tests\Feature;

use App\Models\Sector;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class SectorControllerTest extends TestCase
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
    App::setLocale('pt_BR');
    $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
  }

  public function test_index_displays_sectors_list()
  {
    Sector::factory()->create(['name' => 'Produção']);
    Sector::factory()->create(['name' => 'Manutenção']);
    Sector::factory()->create(['name' => 'Qualidade']);

    $response = $this->get(route('sectors.index'));

    $response->assertStatus(200);
    $response->assertInertia(
      fn($page) => $page
        ->component('Admin/Sectors/Index')
        ->has('sectors')
        ->has('filters')
    );
  }

  public function test_index_filters_by_search()
  {
    Sector::factory()->create(['name' => 'Produção']);
    Sector::factory()->create(['name' => 'Manutenção']);

    $response = $this->get(route('sectors.index', ['search' => 'Produção']));

    $response->assertStatus(200);
    $response->assertInertia(
      fn($page) => $page
        ->component('Admin/Sectors/Index')
        ->where('filters.search', 'Produção')
    );
  }

  public function test_index_filters_by_status()
  {
    Sector::factory()->create(['name' => 'Produção', 'status' => 'active']);
    Sector::factory()->create(['name' => 'Manutenção', 'status' => 'inactive']);

    $response = $this->get(route('sectors.index', ['status' => 'inactive']));

    $response->assertStatus(200);
    $response->assertInertia(
      fn($page) => $page
        ->component('Admin/Sectors/Index')
        ->where('filters.status', 'inactive')
    );
  }

  public function test_create_displays_form()
  {
    $response = $this->get(route('sectors.create'));
    $response->assertStatus(200);
    $response->assertInertia(
      fn($page) => $page
        ->component('Admin/Sectors/Create')
    );
  }

  public function test_store_creates_sector()
  {
    $this->get(route('sectors.create'));
    $token = csrf_token();

    $payload = [
      '_token' => $token,
      'name' => 'Qualidade',
      'status' => 'active',
    ];

    $response = $this->from(route('sectors.create'))->withHeaders(['X-Inertia' => true])->post(route('sectors.store'), $payload);

    $response->assertRedirect(route('sectors.index'));
    $response->assertSessionHas('status', 'Setor criado com sucesso!');
    $this->assertDatabaseHas('sectors', [
      'name' => 'Qualidade',
      'status' => 'active',
    ]);
  }

  public function test_store_validates_unique_name()
  {
    Sector::factory()->create(['name' => 'Produção']);

    $this->get(route('sectors.create'));
    $token = csrf_token();

    $payload = [
      '_token' => $token,
      'name' => 'Produção',
      'status' => 'active',
    ];

    $response = $this->from(route('sectors.create'))->withHeaders(['X-Inertia' => true])->post(route('sectors.store'), $payload);

    $response->assertSessionHasErrors('name');
  }

  public function test_edit_displays_form()
  {
    $sector = Sector::factory()->create();

    $response = $this->get(route('sectors.edit', $sector));

    $response->assertStatus(200);
    $response->assertInertia(
      fn($page) => $page
        ->component('Admin/Sectors/Edit')
        ->where('sector.id', $sector->id)
    );
  }

  public function test_update_modifies_sector()
  {
    $sector = Sector::factory()->create(['name' => 'Old Name', 'status' => 'active']);

    $this->get(route('sectors.edit', $sector));
    $token = csrf_token();

    $payload = [
      '_token' => $token,
      'name' => 'New Name',
      'status' => 'inactive',
    ];

    $response = $this->from(route('sectors.edit', $sector))->withHeaders(['X-Inertia' => true])->patch(route('sectors.update', $sector), $payload);

    $response->assertRedirect(route('sectors.index'));
    $response->assertSessionHas('status', 'Setor atualizado com sucesso!');
    $this->assertDatabaseHas('sectors', [
      'id' => $sector->id,
      'name' => 'New Name',
      'status' => 'inactive',
    ]);
  }

  public function test_update_validates_unique_name()
  {
    $sector1 = Sector::factory()->create(['name' => 'Produção']);
    $sector2 = Sector::factory()->create(['name' => 'Manutenção']);

    $this->get(route('sectors.edit', $sector2));
    $token = csrf_token();

    $payload = [
      '_token' => $token,
      'name' => 'Produção',
      'status' => 'active',
    ];

    $response = $this->from(route('sectors.edit', $sector2))->withHeaders(['X-Inertia' => true])->patch(route('sectors.update', $sector2), $payload);

    $response->assertSessionHasErrors('name');
  }

  public function test_destroy_deletes_sector()
  {
    $sector = Sector::factory()->create();

    $this->get(route('sectors.index'));
    $token = csrf_token();

    $response = $this->withHeaders(['X-Inertia' => true])->delete(route('sectors.destroy', $sector), ['_token' => $token]);

    $response->assertRedirect(route('sectors.index'));
    $response->assertSessionHas('status', 'Setor removido com sucesso!');
    $this->assertDatabaseMissing('sectors', ['id' => $sector->id]);
  }

  public function test_modal_returns_sector_details()
  {
    $sector = Sector::factory()->create();

    $response = $this->get(route('sectors.modal', $sector));

    $response->assertStatus(200);
    $response->assertJson([
      'sector' => [
        'id' => $sector->id,
        'name' => $sector->name,
        'status' => $sector->status,
        'created_at' => $sector->created_at->format('d/m/Y H:i'),
        'updated_at' => $sector->updated_at->format('d/m/Y H:i'),
      ],
    ]);
  }

  public function test_denies_access_without_permission()
  {
    $user = User::factory()->create([
      'role' => 'user',
      'permissions' => [],
      'email_verified_at' => now(),
    ]);
    $this->actingAs($user);

    $response = $this->get(route('sectors.index'));

    $response->assertStatus(403);
  }
}
