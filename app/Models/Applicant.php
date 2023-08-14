<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

<<<<<<< HEAD
// class Applicant extends Model


class Applicant extends Model
// class Applicant extends User

=======
class Applicant extends Model
>>>>>>> ab6d5ab982e07fec4bdbfbc206e06f0d2c2a8a80
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
            return $this->hasMany(Application::class);
        }
=======

>>>>>>> ab6d5ab982e07fec4bdbfbc206e06f0d2c2a8a80
        public function applications(){
            return $this->hasMany(Application::class);
        }

}
