<?php

namespace App\Policies;

use App\Models\CartedProduct;
use App\Models\User;

class CartedProductPolicy
{
    public function removeFromCart(User $user, CartedProduct $cartedProduct)
    {
        return $user->type === 'customer' && $user->id === $cartedProduct->cart_id;
    }

    public function updateQuantity(User $user, CartedProduct $cartedProduct)
    {
        return $user->type === 'customer' && $user->id === $cartedProduct->cart_id;
    }
}
