<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\User;
use App\Policies\OrderPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderPolicyTest extends TestCase
{
    use RefreshDatabase;

    private OrderPolicy $policy;
    private Order $order;
    private User $adminUser;
    private User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new OrderPolicy();
        $this->order = Order::factory()->create();

        // Create admin user
        $this->adminUser = User::factory()->create(['role' => 'admin']);

        // Create regular user with permissions
        $this->regularUser = User::factory()->create([
            'role' => 'user',
            'permissions' => [
                'orders' => [
                    'view' => true,
                    'create' => true,
                    'update' => true,
                    'delete' => true,
                ]
            ]
        ]);
    }

    public function test_admin_can_view_any_orders()
    {
        $this->assertTrue($this->policy->viewAny($this->adminUser));
    }

    public function test_admin_can_view_order()
    {
        $this->assertTrue($this->policy->view($this->adminUser, $this->order));
    }

    public function test_admin_can_create_orders()
    {
        $this->assertTrue($this->policy->create($this->adminUser));
    }

    public function test_admin_can_update_order()
    {
        $this->assertTrue($this->policy->update($this->adminUser, $this->order));
    }

    public function test_admin_can_delete_order()
    {
        $this->assertTrue($this->policy->delete($this->adminUser, $this->order));
    }

    public function test_admin_can_restore_order()
    {
        $this->assertTrue($this->policy->restore($this->adminUser, $this->order));
    }

    public function test_admin_can_force_delete_order()
    {
        $this->assertTrue($this->policy->forceDelete($this->adminUser, $this->order));
    }

    public function test_admin_can_manage_items()
    {
        $this->assertTrue($this->policy->manageItems($this->adminUser, $this->order));
    }

    public function test_admin_can_add_item()
    {
        $this->assertTrue($this->policy->addItem($this->adminUser, $this->order));
    }

    public function test_admin_can_update_item()
    {
        $this->assertTrue($this->policy->updateItem($this->adminUser, $this->order));
    }

    public function test_admin_can_remove_item()
    {
        $this->assertTrue($this->policy->removeItem($this->adminUser, $this->order));
    }

    public function test_user_with_view_permission_can_view_any_orders()
    {
        $user = User::factory()->create([
            'role' => 'user',
            'permissions' => ['orders' => ['view' => true]]
        ]);

        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_user_with_view_permission_can_view_order()
    {
        $user = User::factory()->create([
            'role' => 'user',
            'permissions' => ['orders' => ['view' => true]]
        ]);

        $this->assertTrue($this->policy->view($user, $this->order));
    }

    public function test_user_without_view_permission_cannot_view_any_orders()
    {
        $user = User::factory()->create([
            'role' => 'user',
            'permissions' => ['orders' => ['view' => false]]
        ]);

        $this->assertFalse($this->policy->viewAny($user));
    }

    public function test_user_without_view_permission_cannot_view_order()
    {
        $user = User::factory()->create([
            'role' => 'user',
            'permissions' => ['orders' => ['view' => false]]
        ]);

        $this->assertFalse($this->policy->view($user, $this->order));
    }

    public function test_user_with_create_permission_can_create_orders()
    {
        $user = User::factory()->create([
            'role' => 'user',
            'permissions' => ['orders' => ['create' => true]]
        ]);

        $this->assertTrue($this->policy->create($user));
    }

    public function test_user_without_create_permission_cannot_create_orders()
    {
        $user = User::factory()->create([
            'role' => 'user',
            'permissions' => ['orders' => ['create' => false]]
        ]);

        $this->assertFalse($this->policy->create($user));
    }

    public function test_user_with_update_permission_can_update_order()
    {
        $user = User::factory()->create([
            'role' => 'user',
            'permissions' => ['orders' => ['update' => true]]
        ]);

        $this->assertTrue($this->policy->update($user, $this->order));
    }

    public function test_user_without_update_permission_cannot_update_order()
    {
        $user = User::factory()->create([
            'role' => 'user',
            'permissions' => ['orders' => ['update' => false]]
        ]);

        $this->assertFalse($this->policy->update($user, $this->order));
    }

    public function test_user_with_delete_permission_can_delete_order()
    {
        $user = User::factory()->create([
            'role' => 'user',
            'permissions' => ['orders' => ['delete' => true]]
        ]);

        $this->assertTrue($this->policy->delete($user, $this->order));
    }

    public function test_user_without_delete_permission_cannot_delete_order()
    {
        $user = User::factory()->create([
            'role' => 'user',
            'permissions' => ['orders' => ['delete' => false]]
        ]);

        $this->assertFalse($this->policy->delete($user, $this->order));
    }

    public function test_user_with_update_permission_can_restore_order()
    {
        $user = User::factory()->create([
            'role' => 'user',
            'permissions' => ['orders' => ['update' => true]]
        ]);

        $this->assertTrue($this->policy->restore($user, $this->order));
    }

    public function test_user_without_update_permission_cannot_restore_order()
    {
        $user = User::factory()->create([
            'role' => 'user',
            'permissions' => ['orders' => ['update' => false]]
        ]);

        $this->assertFalse($this->policy->restore($user, $this->order));
    }

    public function test_user_with_delete_permission_can_force_delete_order()
    {
        $user = User::factory()->create([
            'role' => 'user',
            'permissions' => ['orders' => ['delete' => true]]
        ]);

        $this->assertTrue($this->policy->forceDelete($user, $this->order));
    }

    public function test_user_without_delete_permission_cannot_force_delete_order()
    {
        $user = User::factory()->create([
            'role' => 'user',
            'permissions' => ['orders' => ['delete' => false]]
        ]);

        $this->assertFalse($this->policy->forceDelete($user, $this->order));
    }

    public function test_user_with_view_permission_can_manage_items()
    {
        $user = User::factory()->create([
            'role' => 'user',
            'permissions' => ['orders' => ['view' => true]]
        ]);

        $this->assertTrue($this->policy->manageItems($user, $this->order));
    }

    public function test_user_without_view_permission_cannot_manage_items()
    {
        $user = User::factory()->create([
            'role' => 'user',
            'permissions' => ['orders' => ['view' => false]]
        ]);

        $this->assertFalse($this->policy->manageItems($user, $this->order));
    }

    public function test_user_with_create_permission_can_add_item()
    {
        $user = User::factory()->create([
            'role' => 'user',
            'permissions' => ['orders' => ['create' => true]]
        ]);

        $this->assertTrue($this->policy->addItem($user, $this->order));
    }

    public function test_user_with_update_permission_can_add_item()
    {
        $user = User::factory()->create([
            'role' => 'user',
            'permissions' => ['orders' => ['update' => true]]
        ]);

        $this->assertTrue($this->policy->addItem($user, $this->order));
    }

    public function test_user_without_create_or_update_permission_cannot_add_item()
    {
        $user = User::factory()->create([
            'role' => 'user',
            'permissions' => ['orders' => ['create' => false, 'update' => false]]
        ]);

        $this->assertFalse($this->policy->addItem($user, $this->order));
    }

    public function test_user_with_update_permission_can_update_item()
    {
        $user = User::factory()->create([
            'role' => 'user',
            'permissions' => ['orders' => ['update' => true]]
        ]);

        $this->assertTrue($this->policy->updateItem($user, $this->order));
    }

    public function test_user_without_update_permission_cannot_update_item()
    {
        $user = User::factory()->create([
            'role' => 'user',
            'permissions' => ['orders' => ['update' => false]]
        ]);

        $this->assertFalse($this->policy->updateItem($user, $this->order));
    }

    public function test_user_with_update_permission_can_remove_item()
    {
        $user = User::factory()->create([
            'role' => 'user',
            'permissions' => ['orders' => ['update' => true]]
        ]);

        $this->assertTrue($this->policy->removeItem($user, $this->order));
    }

    public function test_user_with_delete_permission_can_remove_item()
    {
        $user = User::factory()->create([
            'role' => 'user',
            'permissions' => ['orders' => ['delete' => true]]
        ]);

        $this->assertTrue($this->policy->removeItem($user, $this->order));
    }

    public function test_user_without_update_or_delete_permission_cannot_remove_item()
    {
        $user = User::factory()->create([
            'role' => 'user',
            'permissions' => ['orders' => ['update' => false, 'delete' => false]]
        ]);

        $this->assertFalse($this->policy->removeItem($user, $this->order));
    }

    public function test_user_with_null_permissions_defaults_to_false()
    {
        $user = User::factory()->create([
            'role' => 'user',
            'permissions' => null
        ]);

        $this->assertFalse($this->policy->viewAny($user));
        $this->assertFalse($this->policy->view($user, $this->order));
        $this->assertFalse($this->policy->create($user));
        $this->assertFalse($this->policy->update($user, $this->order));
        $this->assertFalse($this->policy->delete($user, $this->order));
        $this->assertFalse($this->policy->restore($user, $this->order));
        $this->assertFalse($this->policy->forceDelete($user, $this->order));
        $this->assertFalse($this->policy->manageItems($user, $this->order));
        $this->assertFalse($this->policy->addItem($user, $this->order));
        $this->assertFalse($this->policy->updateItem($user, $this->order));
        $this->assertFalse($this->policy->removeItem($user, $this->order));
    }
}
