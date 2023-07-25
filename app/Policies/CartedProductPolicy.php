<?php

namespace App\Policies;

use App\Models\CartedProduct;
use App\Models\User;

class CartedProductPolicy
{
    /**
     * Check if the user is a customer and can remove the carted product from the cart.
     *
     * @param User         $user          The authenticated user (customer).
     * @param CartedProduct $cartedProduct The carted product to be removed.
     * @return bool True if the customer can remove the carted product, false otherwise.
     */
    public function removeFromCart(User $user, CartedProduct $cartedProduct)
    {
        return $user->type === 'customer' && $user->id === $cartedProduct->cart_id;
    }

    /**
     * Check if the user is a customer and can update the quantity of the carted product in the cart.
     *
     * @param User         $user          The authenticated user (customer).
     * @param CartedProduct $cartedProduct The carted product to be updated.
     * @return bool True if the customer can update the carted product quantity, false otherwise.
     */
    public function updateQuantity(User $user, CartedProduct $cartedProduct)
    {
        return $user->type === 'customer' && $user->id === $cartedProduct->cart_id;
    }
}
