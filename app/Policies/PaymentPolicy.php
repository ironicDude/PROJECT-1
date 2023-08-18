<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function view(User $user, Payment $payment)
    {
        return $user->isEmployee() && ($user->isAdministrator() || $user->id == $payment->employee_id);
    }
    public function update(User $user, Payment $payment)
    {
        return $user->isEmployee() && ($user->isAdministrator() || $user->id == $payment->employee_id);
    }
    public function manage(User $user)
    {
        return $user->isEmployee() && $user->isAdministrator();
    }
}
