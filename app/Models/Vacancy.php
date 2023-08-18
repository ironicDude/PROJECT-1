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

    public function applications()
    {
        return $this->hasMany(Application::class, 'vacancy_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');

    }
}
