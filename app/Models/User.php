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
        'email_verified_at',
        'remember_token',
        'mobile',
        'password',
        'address',
        'date_of_birth',
        'type',
        'gender_id',
        'image',
        'account_status_id'
    ];
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

    /**
     * Deactivate the user's account by changing the 'account_status_id' to "Blocked".
     *
     * This method updates the 'account_status_id' attribute of the current user model to the ID of the "Blocked" status
     * in the 'account_statuses' table. It then saves the updated user model to persist the changes in the database.
     *
     * @return void
     */
    public function deactivate(): void
    {
        // Retrieve the 'id' value for the "Blocked" status from the 'account_statuses' table.
        $blockedStatusId = AccountStatus::where('status', 'Blocked')->value('id');

        // Update the 'account_status_id' attribute of the current user model to the "Blocked" status ID.
        $this->account_status_id = $blockedStatusId;

        // Save the updated user model to persist the changes in the database.
        $this->save();
    }

    /**
     * Activate the user's account by changing the 'account_status_id' to "Active".
     *
     * This method updates the 'account_status_id' attribute of the current user model to the ID of the "Active" status
     * in the 'account_statuses' table. It then saves the updated user model to persist the changes in the database.
     *
     * @return void
     */
    public function activate(): void
    {
        // Retrieve the 'id' value for the "Active" status from the 'account_statuses' table.
        $activeStatusId = AccountStatus::where('status', 'Active')->value('id');

        // Update the 'account_status_id' attribute of the current user model to the "Active" status ID.
        $this->account_status_id = $activeStatusId;

        // Save the updated user model to persist the changes in the database.
        $this->save();
    }
    

    /**
     * relationships
     */
    public function gender()
    {
        return $this->belongsTo(Gender::class, 'gender_id', 'id');
    }
    public function accountStatus()
    {
        return $this->belongsTo(AccountStatus::class, 'account_status_id', 'id');
    }
    public function ratings()
    {
        return $this->belongsToMany(User::class, 'ratings', 'product_id', 'user_id')->withPivot('rating');
    }
}
