<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vacancy;

class VacancyPolicy
{
    public function viewAll(User $user)
    {
        return $user->isEmployee() && $user->isAdministrator();
    }

    public function view(User $user, Vacancy $vacancy)
    {
        return $user->isEmployee() && $user->isAdministrator() && $vacancy->employee_id == $user->id;
    }

    public function update(User $user, Vacancy $vacancy)
    {
        return $user->isEmployee() && $user->isAdministrator() && $vacancy->employee_id == $user->id;
    }

    public function delete(User $user, Vacancy $vacancy)
    {
        return $user->isEmployee() && $user->isAdministrator() && $vacancy->employee_id == $user->id;
    }

    public function create(User $user)
    {
        return $user->isEmployee() && $user->isAdministrator();
    }
}
