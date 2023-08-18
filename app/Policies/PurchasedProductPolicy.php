<?php

namespace App\Policies;

use App\Models\Cart;
use App\Models\PurchasedProduct;
use App\Models\User;

class PurchasedProductPolicy
{
    public function storeInCart(User $user)
    {
        return $user->isCustomer();
    }

    public function getAndSet(User $user)
    {
        return $user->isEmployee() && ($user->isAdministrator());
    }

    public function viewBestSellingProducts(User $user)
    {
        return $user->isEmployee() && $user->isAdministrator();
    }

    public function viewMostProfitableProducts(User $user)
    {
        return $user->isEmployee() && $user->isAdministrator();
    }

}
