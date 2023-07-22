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
        'customer_id',
        'purchased_product_id'
    ];


    public function cart()
    {
        return $this->belongsTo(Cart::class, 'customer_id', 'customer_id');
    }

    public function purchasedProduct()
    {
        return $this->belongsTo(PurchasedProduct::class, 'purchased_product_id', 'id');
    }
}
