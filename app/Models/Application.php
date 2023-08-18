<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model


// class Application extends User


{
    protected static $singleTableType = 'application';
    protected static $persisted = ['salary', 'personal_email', 'date_of_joining'];
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
