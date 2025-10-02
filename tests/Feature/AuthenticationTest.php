<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_home_page(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Bem-vindo');
    }

    public function test_guest_is_redirected_to_login_when_visiting_dashboard(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_is_redirected_from_login_to_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('login'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_user_can_register_and_is_redirected_to_dashboard(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard'));
    }

    public function test_user_can_login_and_is_redirected_to_dashboard(): void
    {
        $user = User::factory()->create([
            'password' => 'Password123!',
        ]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'Password123!',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard'));
    }
}
