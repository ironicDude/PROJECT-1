<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
class UserRole extends Model
{
    use HasFactory;

    protected $fillable = ['role',];




    /**
     * relations
     */
    public function users()
    {
        return $this->hasMany(User::class, 'user_role_id', 'id');
    }
}
