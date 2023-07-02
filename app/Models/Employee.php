<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\UserRole;
use App\Models\User;
class Employee extends User
{
    use HasFactory;
    protected static $singleTableType = 'employee';
    protected static $persisted = ['salary', 'personal_email', 'date_of_joining', 'role_id'];
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'address',
        'type',
        'date_of_birth',
        'gender_id',
        'image',
        'salary',
        'personal_email',
        'date_of_joining',
        'role_id'
    ];

}


