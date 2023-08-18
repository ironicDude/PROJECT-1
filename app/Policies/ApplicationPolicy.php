<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    public function viewAll(User $user)
    {
        return $user->isEmployee() && $user->isAdministrator();
    }

    public function view(User $user, Application $application)
    {
        return $user->isEmployee() && $user->isAdministrator();
    }

    public function accept(User $user, Application $application)
    {
        return $user->isEmployee() && $user->isAdministrator();
    }

    public function reject(User $user, Application $application)
    {
        return $user->isEmployee() && $user->isAdministrator();
    }
}
