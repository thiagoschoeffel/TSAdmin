<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_requires_authentication(): void
    {
        $this->get(route('profile.edit'))
            ->assertRedirect(route('login'));
    }

    public function test_user_can_update_profile_without_changing_password(): void
    {
        $user = User::factory()->create([
            'email' => 'old@example.com',
        ]);

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'Updated Name',
            'email' => 'new@example.com',
        ]);

        $response->assertRedirect(route('profile.edit'));

        $this->assertEquals('Updated Name', $user->fresh()->name);
        $this->assertEquals('new@example.com', $user->fresh()->email);
    }

    public function test_user_can_update_password_with_current_password(): void
    {
        $user = User::factory()->create([
            'password' => 'OldPass123!',
        ]);

        $response = $this->actingAs($user)->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'current_password' => 'OldPass123!',
            'password' => 'NewPass123!',
            'password_confirmation' => 'NewPass123!',
        ]);

        $response->assertRedirect(route('profile.edit'));

        $this->assertTrue(Hash::check('NewPass123!', $user->fresh()->password));
    }

    public function test_user_listing_displays_all_users_except_actions_for_self(): void
    {
        $user = User::factory()->create(['name' => 'Manager']);
        $other = User::factory()->create(['name' => 'Colleague']);

        $response = $this->actingAs($user)->get(route('users.index'));

        $response->assertOk()
            ->assertSee('Manager')
            ->assertSee('Colleague')
            ->assertSee('Novo usuário')
            ->assertSee('Gerenciar conta');
    }

    public function test_user_cannot_access_edit_page_for_self(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('users.edit', $user))
            ->assertForbidden();
    }

    public function test_user_can_update_other_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $response = $this->actingAs($user)->patch(route('users.update', $other), [
            'name' => 'Updated User',
            'email' => 'updated@example.com',
            'status' => 'inactive',
        ]);

        $response->assertRedirect(route('users.index'));

        $this->assertEquals('Updated User', $other->fresh()->name);
        $this->assertEquals('inactive', $other->fresh()->status);
    }

    public function test_user_can_create_user_from_admin_panel(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('users.store'), [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'status' => 'active',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['email' => 'newuser@example.com', 'status' => 'active']);
    }

    public function test_user_can_delete_other_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $response = $this->actingAs($user)->delete(route('users.destroy', $other));
        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseMissing('users', ['id' => $other->id]);
    }

    public function test_user_cannot_delete_self_through_admin_route(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->delete(route('users.destroy', $user))
            ->assertForbidden();
    }

    public function test_user_can_delete_own_account_from_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->delete(route('profile.destroy'));

        $response->assertRedirect(route('home'));
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_cannot_delete_user_with_associated_clients(): void
    {
        $user = User::factory()->create();
        Client::factory()->create([
            'created_by_id' => $user->id,
        ]);

        $this->actingAs(User::factory()->create())
            ->delete(route('users.destroy', $user))
            ->assertSessionHasErrors();

        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_user_cannot_delete_self_when_has_clients(): void
    {
        $user = User::factory()->create();
        Client::factory()->create([
            'created_by_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->delete(route('profile.destroy'));

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHasErrors('profile');
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }
}
