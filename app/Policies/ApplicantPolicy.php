<?php

namespace App\Policies;

use App\Models\Applicant;
use App\Models\User;

class ApplicantPolicy
{
    public function view(User $user, Applicant $applicant)
    {
        return $user->isEmployee() && $user->isAdministrator();
    }

    public function viewAll(User $user)
    {
        return $user->isEmployee() && $user->isAdministrator();
    }
}
