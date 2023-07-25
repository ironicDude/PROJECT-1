<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Auth;

class UserPolicy
{
    public function activateOrDeactivate(User $user, User $toBeToggledUser): Response
    {
        return $user->isAdministrator() &&
        !$toBeToggledUser->isAdministrator()
        ? Response::allow()
        : Response::denyAsNotFound();
    }

}
