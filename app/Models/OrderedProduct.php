<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderedProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'dated_product_id',
        'quantity',
        'subtotal'
    ];
    /**
     * relationships
     */
    public function order()
     {
        return $this->belongsTo(Order::class, 'order_id', 'id');
     }

     public function datedProducts()
     {
        return $this->belongsTo(DatedProduct::class, 'dated_product_id', 'id');
     }
}
