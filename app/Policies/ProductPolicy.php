<?php

namespace App\Policies;

use App\Models\User;

class ProductPolicy
{
    public function storeInCart(User $user)
    {
        return $user->type === 'customer';
    }
}
