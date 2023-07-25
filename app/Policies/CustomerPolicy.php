<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;

class CustomerPolicy
{
    public function viewOrders(User $user, Customer $customer)
    {
        return $user->id === $customer->id;
    }
}
