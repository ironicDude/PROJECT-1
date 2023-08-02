<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;

class CustomerPolicy
{
    /**
     * Determine if the authenticated user can view the orders of the specified customer.
     *
     * @param User     $user     The authenticated user.
     * @param Customer $customer The customer whose orders are being viewed.
     * @return bool True if the user can view the orders of the customer, false otherwise.
     */
    public function viewOrders(User $user, Customer $customer)
    {
        return $user->id === $customer->id;
    }

    public function viewCountOfNewCustomers(User $user)
    {
        return $user->isEmployee() && $user->isAdministrator();
    }
}
