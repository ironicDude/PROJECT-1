<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

<<<<<<< HEAD
class Applicant extends Model
// class Applicant extends User
=======

// class Applicant extends Model
class Applicant extends User
>>>>>>> eb027def1a3e890859c0980b7177d1970fb2573f

{
    use HasFactory;
    // protected static $singleTableType = 'applicant';
    // protected static $persisted = ['salary', 'personal_email', 'date_of_joining'];
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'mobile',
        'status',
        'address',
        'resource',
    ];
    
<<<<<<< HEAD
        public function applicant_vacancy(){
=======
        public function applications(){
>>>>>>> eb027def1a3e890859c0980b7177d1970fb2573f
            return $this->hasMany(Application::class);
        }

}
