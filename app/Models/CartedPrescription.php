<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartedPrescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'prescription'
    ];





    //relationships
    public function cart()
    {
        return $this->hasOne(Cart::class, 'cart_id', 'id');
    }
}
