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
     }
>>>>>>> ab6d5ab982e07fec4bdbfbc206e06f0d2c2a8a80

}
