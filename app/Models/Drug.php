<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Drug extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description',
    'state', 'indication', 'pharmacodynamics',
    'toxicity', 'half_life', 'route_of_elimination',
    'clearance', 'attribute'];
}
