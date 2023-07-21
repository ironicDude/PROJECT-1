<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Customer extends User
{
    use HasFactory;
    protected static $singleTableType = 'customer';
    protected static $persisted = ['money'];

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'address',
        'date_of_birth',
        'gender_id',
        'image',
        'money',
    ];


    /**
     * Relationships
     */

     public function cart()
     {
        return $this->hasOne(CustomerCart::class, 'customer_id', 'id');
     }
}
