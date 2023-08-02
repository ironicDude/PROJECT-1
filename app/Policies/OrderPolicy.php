<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Determine if the authenticated user has permission to view the specified order.
     *
     * @param User  $user  The authenticated user (either employee or customer).
     * @param Order $order The order to be viewed.
     * @return bool True if the user is allowed to view the order, false otherwise.
     */
    public function show(User $user, Order $order)
    {
        // Check if the user is an employee or a customer and if the user ID matches the customer_id of the order.
        // If any of these conditions are met, the user is allowed to view the order; otherwise, access is denied.
        return ($user->type === 'employee') || ($user->type === 'customer' && $user->id === $order->customer_id);
    }

    public function viewRevenue(User $user, Order $order)
    {
        return $user->isEmployee() && $user->isAdministrator();
    }
}
