<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Only create admin if doesn't exist
        if (!User::where('email', 'admin@example.com')->exists()) {
            User::factory()->create([
                'name' => 'Administrador',
                'email' => 'admin@example.com',
                'password' => 'password',
                'status' => 'active',
                'role' => 'admin',
            ]);
        }

        // Only create user with all permissions if doesn't exist
        if (!User::where('email', 'user@example.com')->exists()) {
            User::factory()->create([
                'name' => 'Usuário Comum',
                'email' => 'user@example.com',
                'password' => 'password',
                'status' => 'active',
                'role' => 'user',
                'permissions' => $this->getAllPermissions(),
            ]);
        }

        // Update existing users to include new permissions
        $this->updateExistingUserPermissions();
    }

    /**
     * Update existing users to include any new permissions from config.
     */
    private function updateExistingUserPermissions(): void
    {
        $resources = config('permissions.resources', []);
        $users = User::where('role', 'user')->get();

        foreach ($users as $user) {
            $permissions = $user->permissions ?? [];

            foreach ($resources as $resourceKey => $resource) {
                $abilities = array_keys($resource['abilities'] ?? []);

                if (!isset($permissions[$resourceKey])) {
                    $permissions[$resourceKey] = [];
                }

                foreach ($abilities as $ability) {
                    if (!isset($permissions[$resourceKey][$ability])) {
                        // Add missing permissions with default value (false for regular users)
                        $permissions[$resourceKey][$ability] = false;
                    }
                }
            }

            $user->update(['permissions' => $permissions]);
        }
    }

    /**
     * Get all permissions set to true for a user with full access.
     */
    private function getAllPermissions(): array
    {
        $resources = config('permissions.resources', []);
        $permissions = [];

        foreach ($resources as $resourceKey => $resource) {
            $abilities = array_keys($resource['abilities'] ?? []);
            $permissions[$resourceKey] = array_fill_keys($abilities, true);
        }

        return $permissions;
    }
}
