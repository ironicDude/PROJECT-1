<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Gender extends Model
{
    use HasFactory;
    protected $fillable = ['gender'];


    /**
     * relations
     */
    function users(){
        return $this->hasMany(User::class);
    }
}
