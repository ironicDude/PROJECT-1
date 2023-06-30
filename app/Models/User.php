<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\UserRole;
use App\Models\Gender;
use App\Models\AccountStatus;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'address',
        'date_of_birth',
        'user_role_id',
        'gender_id',
        'image'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    //change the account_status_id to Blocked
    public function deactivate(): void
    {
        $this->account_status_id = AccountStatus::where('status', 'Blocked')->value('id');
        $this->save();
    }// end of deactivate

    //change the account_status_id to Active
    public function activate(): void
    {
        $this->account_status_id = AccountStatus::where('status', 'Active')->value('id');
        $this->save();
    }// end of deactivate

    public function isAdmin()
    {
        
    }// end of isAdmin


    /**
     * relations
     */
    public function role(){
        return $this->hasOne(UserRole::class, 'id', 'user_role_id');
    }
    public function gender(){
        return $this->hasOne(Gender::class, 'id', 'gender_id');
    }
    public function status(){
        return $this->hasOne(AccountStatus::class, 'id', 'account_status_id');
    }
}
