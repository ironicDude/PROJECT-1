<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Employee extends User
{
    use HasFactory;
    //inherit from user

    protected $fillable = ['user_id', 'salary', 'personal_email', 'date_of_joining'];


    /**
     * relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

}
