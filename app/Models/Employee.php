<?php

namespace App\Models;

use App\Http\Resources\User\EmployeeResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Role;
use Nanigans\SingleTableInheritance\SingleTableInheritanceTrait;

class Employee extends User
{
    use SingleTableInheritanceTrait;
    use HasFactory;

    protected static $singleTableType = 'employee';
    protected static $persisted = ['salary', 'personal_email', 'date_of_joining'];
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'address',
        'date_of_birth',
        'gender',
        'image',
        'account_status',
        'salary',
        'personal_email',
        'date_of_joining',
    ];

    public function isAdministrator(){
        return $this->roles()->where('role', 'administrator')->exists();
    } //end of isAdministrator


    public function getSalary()
    {
        return $this->salary;
    }

    public function getPersonalEmail()
    {
        return $this->personal_email;
    }

    public function getDateOfJoining()
    {
        return $this->date_of_joining;
    }

    public function getRole()
    {
        return $this->roles->first();
    }

    public function setSalary(float $salary)
    {
        $this->salary = $salary;
        $this->save();
        return $this->salary;
    }

    public function setPersonalEmail(string $email)
    {
        $this->personal_email = $email;
        $this->save();
        return $this->personal_email;
    }

    public function setRole(string $role)
    {
        $role = Role::where('name', $role)->firstOrFail();

        $this->roles()->sync([$role->id]);

        return $role;
    }

    public function updateEmployeeInfo(array $newInfo)
    {
        $this->setPersonalEmail($newInfo['personalEmail']);
        return $this;
    }

    public static function getAdmin()
    {
        return self::whereRelation('roles', 'role', 'administrator')->first();
    }

    /**
     * Relationships
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class,'employee_role', 'employee_id', 'role_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'order_id', 'id');
    }
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
    public function user()
    {
        return $this->belongsToMany(User::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'employee_id', 'id');
    }

    public function madePayments()
    {
        return $this->hasMany(Payment::class, 'payer_id', 'id');
    }

    public function receivedPayments()
    {
        return $this->hasMany(Payment::class, 'employee_id', 'id');
    }
}
