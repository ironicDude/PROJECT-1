<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Auth;

class UserPolicy
{
    /**
     * Determine if the authenticated user (employee with administrator role) can activate or deactivate the specified user.
     *
     * @param User $user The authenticated user (employee with administrator role).
     * @param User $toBeToggledUser The user to be activated or deactivated.
     * @return \Illuminate\Auth\Access\Response The authorization response to allow or deny the action.
     */
    public function activateOrDeactivate(User $user, User $toBeToggledUser): Response
    {
        // Check if the authenticated user is an employee with an administrator role,
        // and the user to be toggled is not an administrator.
        if (
            $user->type === 'employee' &&
            $user->role->role === 'administrator' &&
            $toBeToggledUser->role && $toBeToggledUser->role->role != 'administrator'
        ) {
            // If the conditions are met, allow the activation or deactivation action.
            return Response::allow();
        } else {
            // If the conditions are not met, deny the action with a "not found" response.
            return Response::denyAsNotFound();
        }
    }
}
