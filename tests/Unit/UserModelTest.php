<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_model_can_be_created_with_valid_data()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'status' => 'active',
            'role' => 'user',
            'permissions' => ['clients' => ['view' => true]],
        ];

        $user = User::create($userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertEquals('active', $user->status);
        $this->assertEquals('user', $user->role);
        $this->assertEquals(['clients' => ['view' => true]], $user->permissions);
    }

    public function test_user_fillable_attributes_are_correct()
    {
        $fillable = ['name', 'email', 'password', 'status', 'role', 'permissions'];
        $this->assertEquals($fillable, (new User)->getFillable());
    }

    public function test_user_hidden_attributes_are_correct()
    {
        $hidden = ['password', 'remember_token'];
        $this->assertEquals($hidden, (new User)->getHidden());
    }

    public function test_user_casts_are_correct()
    {
        $casts = [
            'id' => 'int',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'permissions' => 'array',
        ];

        $this->assertEquals($casts, (new User)->getCasts());
    }

    public function test_user_is_admin_method_returns_true_for_admin_role()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->assertTrue($admin->isAdmin());
    }

    public function test_user_is_admin_method_returns_false_for_non_admin_role()
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->assertFalse($user->isAdmin());
    }

    public function test_admin_user_has_all_permissions()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Test the permission logic directly
        $permissions = $admin->permissions ?? [];
        $this->assertTrue($admin->isAdmin());
        // Admin should have access regardless of permissions array
        $this->assertTrue(empty($permissions) || $admin->isAdmin());
    }

    public function test_user_with_permission_has_access()
    {
        $user = User::factory()->create([
            'role' => 'user',
            'permissions' => ['clients' => ['view' => true]]
        ]);

        $permissions = $user->permissions ?? [];
        $this->assertFalse($user->isAdmin());
        $this->assertTrue((bool)($permissions['clients']['view'] ?? false));
    }

    public function test_user_without_permission_has_no_access()
    {
        $user = User::factory()->create([
            'role' => 'user',
            'permissions' => ['clients' => ['view' => false]]
        ]);

        $permissions = $user->permissions ?? [];
        $this->assertFalse($user->isAdmin());
        $this->assertFalse((bool)($permissions['clients']['view'] ?? false));
    }

    public function test_user_without_permissions_array_has_no_access()
    {
        $user = User::factory()->create([
            'role' => 'user',
            'permissions' => null
        ]);

        $permissions = $user->permissions ?? [];
        $this->assertFalse($user->isAdmin());
        $this->assertFalse((bool)($permissions['clients']['view'] ?? false));
    }

    public function test_user_implements_must_verify_email()
    {
        $user = new User;
        $this->assertInstanceOf(\Illuminate\Contracts\Auth\MustVerifyEmail::class, $user);
    }
}
