<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartedProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'quantity',
        'subtotal',
        'cart_id',
        'dated_product_id'
    ];


    public function cart()
    {
        return $this->belongsTo(Cart::class, 'cart_id', 'id');
    }

    public function datedProduct()
    {
       return $this->belongsTo(DatedProduct::class, 'dated_product_id', 'id');
    }


}
