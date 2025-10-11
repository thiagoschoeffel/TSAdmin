<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->canManage('products', 'view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Product $product): bool
    {
        return $user->canManage('products', 'view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->canManage('products', 'create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Product $product): bool
    {
        return $user->canManage('products', 'update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Product $product): bool
    {
        return $user->canManage('products', 'delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Product $product): bool
    {
        return $user->canManage('products', 'update');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Product $product): bool
    {
        return $user->canManage('products', 'delete');
    }

    /**
     * Determine whether the user can manage components for this product.
     */
    public function manageComponents(User $user, Product $product): bool
    {
        return $user->canManage('products', 'view');
    }

    /**
     * Determine whether the user can create components for this product.
     */
    public function createComponent(User $user, Product $product): bool
    {
        return $user->canManage('products', 'create') || $user->canManage('products', 'update');
    }

    /**
     * Determine whether the user can update components for this product.
     */
    public function updateComponent(User $user, Product $product): bool
    {
        return $user->canManage('products', 'update');
    }

    /**
     * Determine whether the user can delete components for this product.
     */
    public function deleteComponent(User $user, Product $product): bool
    {
        return $user->canManage('products', 'update') || $user->canManage('products', 'delete');
    }
}
