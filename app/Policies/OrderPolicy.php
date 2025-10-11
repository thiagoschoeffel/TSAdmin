<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->canManage('orders', 'view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Order $order): bool
    {
        return $user->canManage('orders', 'view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->canManage('orders', 'create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Order $order): bool
    {
        return $user->canManage('orders', 'update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Order $order): bool
    {
        return $user->canManage('orders', 'delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Order $order): bool
    {
        return $user->canManage('orders', 'update');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Order $order): bool
    {
        return $user->canManage('orders', 'delete');
    }

    /**
     * Determine whether the user can manage items for this order.
     */
    public function manageItems(User $user, Order $order): bool
    {
        return $user->canManage('orders', 'view');
    }

    /**
     * Determine whether the user can add items to this order.
     */
    public function addItem(User $user, Order $order): bool
    {
        return $user->canManage('orders', 'create') || $user->canManage('orders', 'update');
    }

    /**
     * Determine whether the user can update items in this order.
     */
    public function updateItem(User $user, Order $order): bool
    {
        return $user->canManage('orders', 'update');
    }

    /**
     * Determine whether the user can remove items from this order.
     */
    public function removeItem(User $user, Order $order): bool
    {
        return $user->canManage('orders', 'update') || $user->canManage('orders', 'delete');
    }
}
