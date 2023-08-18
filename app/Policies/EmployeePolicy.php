<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
{
    public function viewPayments(User $user, Employee $employee)
    {
        return $user->id == $employee->id || $user->isEmployee() && $user->isAdministrator();
    }

    public function manageRoles(User $user, Employee $employee)
    {
        return $user->isEmployee() && $user->isAdministrator() && $user->id != $employee->id;
    }
}
