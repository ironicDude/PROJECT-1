<?php

namespace App\Policies;

use App\Models\Cart;
use App\Models\PurchasedProduct;
use App\Models\User;

class PurchasedProductPolicy
{
    /**
     * Determine if the authenticated user is allowed to store items in the cart.
     *
     * @param User $user The authenticated user.
     * @return bool True if the user is a customer and can store items in the cart, false otherwise.
     */
    public function storeInCart(User $user)
    {
        // Check if the user is a customer.
        // If the user is a customer, they are allowed to store items in the cart (returns true). Otherwise return false
        return $user->type === 'customer';
    }

    // public function getAll(User $user)
    // {
    //     return $user->type == 'employee' && ($user->role->role == 'administrator' || $user->role->role == 'Inventory Manager');
    // }

    public function getAndSet(User $user)
    {
        return $user->type == 'employee' && ($user->role->role == 'administrator' || $user->role->role == 'Inventory Manager');
    }

}