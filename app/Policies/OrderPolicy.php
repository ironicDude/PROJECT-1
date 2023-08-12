<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function show(User $user, Order $order)
    {
        return $user->isEmployee() || ($user->isCustomer() && $user->id === $order->customer_id);
    }
    public function viewCustomerOrders(User $user, Order $order)
    {
        return $user->isEmployee() || ($user->isCustomer() && $user->id === $order->customer_id);
    }

    public function viewRevenue(User $user)
    {
        return $user->isEmployee() && $user->isAdministrator();
    }

    public function viewCountOfOrders(User $user)
    {
        return $user->isEmployee() && $user->isAdministrator();
    }

    public function viewAll(User $user)
    {
        return $user->isEmployee() && $user->isAdministrator();
    }

    public function viewPrescriptions(User $user, Order $order)
    {
        return $user->isEmployee() || ($user->isCustomer() && $user->id === $order->customer_id);
    }

    public function viewOrdersChart(User $user)
    {
        return $user->isEmployee() && $user->isAdministrator();
    }

    public function viewRevneueChart(User $user)
    {
        return $user->isEmployee() && $user->isAdministrator();
    }
}
