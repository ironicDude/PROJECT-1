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
     public function applicant_vacancy(){
         return $this->hasMany(Applicant_Vacancy::class);
     }
 
     public function employee(){
         return belongsTo(Employee::class);
     }
}
