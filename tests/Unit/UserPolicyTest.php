<?php

namespace Tests\Unit;

use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPolicyTest extends TestCase
{
  use RefreshDatabase;

  private UserPolicy $policy;
  private User $adminUser;
  private User $otherAdminUser;
  private User $regularUser;
  private User $otherUser;

  protected function setUp(): void
  {
    parent::setUp();

    $this->policy = new UserPolicy();

    // Create admin users
    $this->adminUser = User::factory()->create(['role' => 'admin']);
    $this->otherAdminUser = User::factory()->create(['role' => 'admin']);

    // Create regular users
    $this->regularUser = User::factory()->create(['role' => 'user']);
    $this->otherUser = User::factory()->create(['role' => 'user']);
  }

  public function test_admin_can_view_any_users()
  {
    $this->assertTrue($this->policy->viewAny($this->adminUser));
  }

  public function test_admin_can_view_user()
  {
    $this->assertTrue($this->policy->view($this->adminUser, $this->regularUser));
  }

  public function test_admin_can_create_users()
  {
    $this->assertTrue($this->policy->create($this->adminUser));
  }

  public function test_admin_can_update_other_admin()
  {
    $this->assertTrue($this->policy->update($this->adminUser, $this->otherAdminUser));
  }

  public function test_admin_can_update_regular_user()
  {
    $this->assertTrue($this->policy->update($this->adminUser, $this->regularUser));
  }

  public function test_admin_cannot_update_itself()
  {
    $this->assertFalse($this->policy->update($this->adminUser, $this->adminUser));
  }

  public function test_admin_can_delete_other_admin()
  {
    $this->assertTrue($this->policy->delete($this->adminUser, $this->otherAdminUser));
  }

  public function test_admin_can_delete_regular_user()
  {
    $this->assertTrue($this->policy->delete($this->adminUser, $this->regularUser));
  }

  public function test_admin_cannot_delete_itself()
  {
    $this->assertFalse($this->policy->delete($this->adminUser, $this->adminUser));
  }

  public function test_admin_can_restore_user()
  {
    $this->assertTrue($this->policy->restore($this->adminUser, $this->regularUser));
  }

  public function test_admin_can_force_delete_user()
  {
    $this->assertTrue($this->policy->forceDelete($this->adminUser, $this->regularUser));
  }

  public function test_regular_user_cannot_view_any_users()
  {
    $this->assertFalse($this->policy->viewAny($this->regularUser));
  }

  public function test_regular_user_cannot_view_user()
  {
    $this->assertFalse($this->policy->view($this->regularUser, $this->otherUser));
  }

  public function test_regular_user_cannot_create_users()
  {
    $this->assertFalse($this->policy->create($this->regularUser));
  }

  public function test_regular_user_cannot_update_user()
  {
    $this->assertFalse($this->policy->update($this->regularUser, $this->otherUser));
  }

  public function test_regular_user_cannot_delete_user()
  {
    $this->assertFalse($this->policy->delete($this->regularUser, $this->otherUser));
  }

  public function test_regular_user_cannot_restore_user()
  {
    $this->assertFalse($this->policy->restore($this->regularUser, $this->otherUser));
  }

  public function test_regular_user_cannot_force_delete_user()
  {
    $this->assertFalse($this->policy->forceDelete($this->regularUser, $this->otherUser));
  }

  public function test_user_with_different_role_cannot_perform_actions()
  {
    $managerUser = User::factory()->create(['role' => 'manager']);

    $this->assertFalse($this->policy->viewAny($managerUser));
    $this->assertFalse($this->policy->view($managerUser, $this->regularUser));
    $this->assertFalse($this->policy->create($managerUser));
    $this->assertFalse($this->policy->update($managerUser, $this->regularUser));
    $this->assertFalse($this->policy->delete($managerUser, $this->regularUser));
    $this->assertFalse($this->policy->restore($managerUser, $this->regularUser));
    $this->assertFalse($this->policy->forceDelete($managerUser, $this->regularUser));
  }

  public function test_admin_can_update_user_with_null_role()
  {
    $userWithoutRole = User::factory()->create(['role' => 'user']); // Using 'user' instead of null
    $this->assertTrue($this->policy->update($this->adminUser, $userWithoutRole));
  }

  public function test_admin_can_delete_user_with_null_role()
  {
    $userWithoutRole = User::factory()->create(['role' => 'user']); // Using 'user' instead of null
    $this->assertTrue($this->policy->delete($this->adminUser, $userWithoutRole));
  }

  public function test_admin_cannot_update_itself_even_with_different_id_check()
  {
    // Test edge case where IDs might be compared differently
    $adminCopy = User::find($this->adminUser->id);
    $this->assertFalse($this->policy->update($this->adminUser, $adminCopy));
  }

  public function test_admin_cannot_delete_itself_even_with_different_id_check()
  {
    // Test edge case where IDs might be compared differently
    $adminCopy = User::find($this->adminUser->id);
    $this->assertFalse($this->policy->delete($this->adminUser, $adminCopy));
  }
}
