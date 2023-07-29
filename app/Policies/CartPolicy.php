<?php

namespace App\Policies;

use App\Models\Cart;
use App\Models\CartedProduct;
use App\Models\PurchasedProduct;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Auth;

class CartPolicy
{
    use HandlesAuthorization;

    public function manageCart(User $user, Cart $cart)
    {
        return $user->type === 'customer' && $user->id === $cart->id;
    }
}
