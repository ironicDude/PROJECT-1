<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeRole extends Model
{
    use HasFactory;
    protected $fillable = [
        'employee_id',
        'role_id',
        'user_id',
    ];
    public function user()
    {
        return $this->belongsToMany(User::class);
    }
}
