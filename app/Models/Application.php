<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $fillable = [
        'status',
        'number_of_vacancies',

];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class);
    }
    public function vacancy()
    {
        return $this->belongsTo(Vacancy::class);
    }
}
