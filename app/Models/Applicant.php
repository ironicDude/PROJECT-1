<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

<<<<<<< HEAD
class Applicant extends Model
{
=======

 class Applicant extends Model
 {


>>>>>>> 7a912d883bbfa2950722df1a48decdffb7a0d132
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

>>>>>>> 7a912d883bbfa2950722df1a48decdffb7a0d132
        public function applications(){
            return $this->hasMany(Application::class);
        }

}
