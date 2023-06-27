<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountStatus extends Model
{
    use HasFactory;
    protected $fillable = ['status'];


    /**
     * relations
     */
    function users(){
        return $this->hasMany(User::class, 'account_status_id', 'id');
    }
}
