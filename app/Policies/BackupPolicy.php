<?php

namespace App\Policies;

use App\Models\User;

class BackupPolicy
{
    /**
     * Create a new policy instance.
     */
    public function backup(User $user)
    {
        return $user->isEmployee() && $user->isAdministartor();
    }
}
