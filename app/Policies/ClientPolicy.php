<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->canManage('clients', 'view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Client $client): bool
    {
        return $user->canManage('clients', 'view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->canManage('clients', 'create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Client $client): bool
    {
        return $user->canManage('clients', 'update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Client $client): bool
    {
        return $user->canManage('clients', 'delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Client $client): bool
    {
        return $user->canManage('clients', 'update');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Client $client): bool
    {
        return $user->canManage('clients', 'delete');
    }

    /**
     * Determine whether the user can manage addresses for this client.
     */
    public function manageAddresses(User $user, Client $client): bool
    {
        return $user->canManage('clients', 'view');
    }

    /**
     * Determine whether the user can create addresses for this client.
     */
    public function createAddress(User $user, Client $client): bool
    {
        return $user->canManage('clients', 'create') || $user->canManage('clients', 'update');
    }

    /**
     * Determine whether the user can update addresses for this client.
     */
    public function updateAddress(User $user, Client $client): bool
    {
        return $user->canManage('clients', 'update');
    }

    /**
     * Determine whether the user can delete addresses for this client.
     */
    public function deleteAddress(User $user, Client $client): bool
    {
        return $user->canManage('clients', 'update') || $user->canManage('clients', 'delete');
    }
}
