<?php

namespace App\Policies;

use App\Models\Cart;
use App\Models\CartedProduct;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Auth;

class CartPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the authenticated customer is allowed to view the specified cart.
     *
     * @param User $user The authenticated user (customer).
     * @param Cart $cart The cart to be viewed.
     * @return bool True if the customer is allowed to view the cart, false otherwise.
     */
    public function view(User $user, Cart $cart)
    {
        return $user->type === 'customer' && $user->id === $cart->id;
    }

/**
     * Determine if the authenticated customer is allowed to store a shipping address for the specified cart.
     *
     * @param User $user The authenticated user (customer).
     * @param Cart $cart The cart to receive the address.
     * @return bool True if the customer is allowed to store the address, false otherwise.
     */
    public function storeAddress(User $user, Cart $cart)
    {
        return $user->type === 'customer' && $cart->id === $user->id;
    }

    /**
     * Determine if the authenticated customer is allowed to view the shipping address for the specified cart
     *
     * @param User $user The authenticated user (customer).
     * @param Cart $cart The cart having the address.
     * @return bool True if the customer is allowed to view the address, false otherwise.
     */
    public function viewAddress(User $user, Cart $cart)
    {
        return $user->type === 'customer' && $cart->id === $user->id;
    }

    //same
    public function checkout(User $user, Cart $cart)
    {
        return $user->type === 'customer' && $cart->id === $user->id;
    }
    //same
    public function viewQuantity(User $user, Cart $cart)
    {
        return $user->type === 'customer' && $cart->id === $user->id;
    }
    //same
    public function viewTotal(User $user, Cart $cart)
    {
        return $user->type === 'customer' && $cart->id === $user->id;
    }
    //same
    public function clear(User $user, Cart $cart)
    {
        return $user->type === 'customer' && $cart->id === $user->id;
    }
    //same
    public function storePrescriptions(User $user, Cart $cart)
    {
        return $user->type === 'customer' && $cart->id === $user->id;
    }
    //same
    public function checkPrescriptionsUpload(User $user, Cart $cart)
    {
        return $user->type === 'customer' && $cart->id === $user->id;
    }
}
