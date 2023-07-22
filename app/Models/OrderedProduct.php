<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderedProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'purchased_product_id',
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
}
