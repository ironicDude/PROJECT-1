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

    public function view(User $user, Cart $cart)
    {
        return $user->type === 'customer' && $user->id === $cart->id;
    }

    public function storeAddress(User $user, Cart $cart)
    {
        return $user->type === 'customer' && $cart->id === $user->id;
    }

    public function viewAddress(User $user, Cart $cart)
    {
        return $user->type === 'customer' && $cart->id === $user->id;
    }

    public function checkout(User $user, Cart $cart)
    {
        return $user->type === 'customer' && $cart->id === $user->id;
    }

    public function viewQuantity(User $user, Cart $cart)
    {
        return $user->type === 'customer' && $cart->id === $user->id;
    }

    public function viewTotal(User $user, Cart $cart)
    {
        return $user->type === 'customer' && $cart->id === $user->id;
    }

    public function clear(User $user, Cart $cart)
    {
        return $user->type === 'customer' && $cart->id === $user->id;
    }

    public function storePrescriptions(User $user, Cart $cart)
    {
        return $user->type === 'customer' && $cart->id === $user->id;
    }

    public function checkPrescriptionsUpload(User $user, Cart $cart)
    {
        return $user->type === 'customer' && $cart->id === $user->id;
    }
}

