<?php

namespace App\Policies;

use App\Models\Purchase;
use App\Models\User;

class PurchasePolicy
{
    public function show(User $user, Purchase $purchase)
    {
        return $user->isEmployee() && ($user->isAdministrator() || $user->id == $purchase->customer_id);
    }

    public function viewAll(User $user)
    {
        return $user->isEmployee() && $user->isAdministrator();
    }
}
