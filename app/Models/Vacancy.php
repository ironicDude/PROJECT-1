<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vacancy extends Model
{
    use HasFactory;
    protected $fillable = [
        'employee_role_id',
        'title',
        'description',
        'type',
        'salary',
        'posting_date',
        'deadline',
        'number_of_vacancies',
        'status',
 ];
<<<<<<< HEAD
     public function applicant_vacancy(){
        return $this->hasMany(Application::class);
     }
     public function applications(){
         return $this->hasMany(Application::class);
     }
 
     public function employee(){
         return belongsTo(Employee::class);
     }
=======

     public function applications(){
         return $this->hasMany(Application::class);
     }

     public function employee()
     {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
>>>>>>> 7a912d883bbfa2950722df1a48decdffb7a0d132

}
}
