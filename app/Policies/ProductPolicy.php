<?php

namespace App\Policies;

use App\Models\User;

class ProductPolicy
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
}
