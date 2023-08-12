<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Schedule extends Model
{
    use HasFactory;
    protected $fillable = [
        'scheduler_id','employee_id', 'day', 'start_time','end_time'
    ];
    public function user()
    {
        return $this->belongsToMany(User::class);
    }
}
