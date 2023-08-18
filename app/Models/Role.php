<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Employee;

class Role extends Model
{
    use HasFactory;

    /**
     * Relationships
     */
    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'employee_role', 'employee_id', 'role_id');
    }
}
