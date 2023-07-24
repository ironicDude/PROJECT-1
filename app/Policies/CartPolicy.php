<?php

namespace App\Policies;

use App\Models\Cart;
use App\Models\CartedProduct;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CartPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the cart.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function view(User $user)
    {
        // Allow customers to view their own cart.
        return User::isCustomer($user);
    }

    /**
     * Determine whether the user can store items in the cart.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function store(User $user)
    {
        // Allow customers to view their own cart.
        return User::isCustomer($user);
    }

    /**
     * Determine whether the user can update the quantity of items in their carts.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function updateQuantity(User $user, CartedProduct $cartedProduct)
    {
        // Allow customers to update items in their own cart.
        return User::isCustomer($user)
        && $cartedProduct->cart_id == $user->id
        ? true
        : false;
    }

    /**
     * Determine whether the user can remove items from the cart.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function removeItem(User $user, CartedProduct $cartedProduct)
    {
        // Allow customers to remove items from their own cart.
        return User::isCustomer($user) && $cartedProduct->cart_id == $user->id
        ? true
        : false;
    }
}

