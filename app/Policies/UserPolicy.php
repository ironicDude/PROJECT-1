<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function activateOrDeactivate($user, User $toBeToggledUser)
    {
        return $user->isAdmin() && $toBeToggledUser->isAdmin() === false;
    }
}
