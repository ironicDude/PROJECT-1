<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchasedProduct extends Model
{
    use HasFactory;





    /**
     * Relationships
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
