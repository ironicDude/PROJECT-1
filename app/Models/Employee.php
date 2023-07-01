<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends User
{
    use HasFactory;
    protected static $singleTableType = 'Employee';
    protected $persisted = ['salary', 'personal_email', 'date_of_joining'];
}
