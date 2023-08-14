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
=======
     public function applications(){
>>>>>>> eb027def1a3e890859c0980b7177d1970fb2573f
         return $this->hasMany(Application::class);
     }
 
     public function employee(){
         return belongsTo(Employee::class);
     }

}
