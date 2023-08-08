<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'rating',
        'review',
    ];











    //relationships
    public function user()
    {
        return $this->hasOne(User::class, 'user_id', 'id');
    }

    public function product()
    {
        return $this->hasOne(Product::class, 'product_id', 'id');
    }
}
