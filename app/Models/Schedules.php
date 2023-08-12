<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedules extends Model
{
    use HasFactory;
    protected $fillable = [
        'employee_id', 'day', 'start_time','end_time'
    ];
    public function employee()
    {
        return $this->belongs(Employee::class);
    }
}
