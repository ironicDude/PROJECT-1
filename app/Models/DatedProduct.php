<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatedProduct extends Model
{
    use HasFactory;




    /**
     * Relationships
     */

     public function purchasedProduct()
     {
        return $this->belongsTo(PurchasedProduct::class, 'product_id', 'id');
     }

     public function orderedProducts()
     {
        return $this->hasMany(OrderedProduct::class, 'dated_product_id', 'id');
     }

     public function cartedProducts()
     {
        return $this->hasMany(CartedProduct::class, 'dated_product_id', 'id');
     }

}
