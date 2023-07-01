<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Gender;
use App\Models\AccountStatus;
use Nanigans\SingleTableInheritance\SingleTableInheritanceTrait;
use App\Models\Employee;
use App\Models\Customer;

class User extends Authenticatable
{
    use SingleTableInheritanceTrait;
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';
    protected static $singleTableTypeField = 'type';
    protected static $singleTableSubclasses = [Employee::class, Customer::class];
    protected static $persisted = [
        'first_name',
        'last_name',
        'email',
        'password',
        'address',
        'date_of_birth',
        'type',
        'gender_id',
        'image'];
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
        'type',
        'date_of_birth',
        'user_role_id',
        'gender_id',
        'image',
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



    /**
     * relationships
     */
    public function gender()
    {
        return $this->hasOne(Gender::class, 'gender_id', 'id');
    }
    public function status()
    {
        return $this->hasOne(AccountStatus::class, 'account_status_id', 'id');
    }
    public function employee()
    {
        return $this->hasOne(Employee::class, 'user_id', 'id');
    }
}
