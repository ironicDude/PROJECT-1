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
        'purchased_product_id'
    ];


    public function cart()
    {
        return $this->belongsTo(Cart::class, 'cart_id', 'id');
    }

    public function purchasedProduct()
    {
        return $this->belongsTo(PurchasedProduct::class, 'purchased_product_id', 'id');
    }
}
