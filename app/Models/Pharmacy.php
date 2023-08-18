<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pharmacy extends Model
{
    public static function getMoney()
    {
        return Pharmacy::first()->money;
    }
    use HasFactory;
}
