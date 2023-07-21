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
        return $this->hasOne(CustomerCart::class, 'customer_id', 'customer_id');
    }
}