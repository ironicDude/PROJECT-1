<?php

namespace App\Models;

use App\Exceptions\OutOfStockException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class PurchasedProduct extends Model
{
    use HasFactory;



    public function getEarliestExpiryDateProducts(int $quantity)
    {
        $product = $this->datedProducts()->where('quantity', '>=', $quantity)->whereNotNull('expiry_date')->orderBy('expiry_date')->limit(1)->first();
        return $product;
    }

    public function isAvailable()
    {
        $datedProducts = $this->datedProducts;
        if ($datedProducts->count() == 0 || $datedProducts->sum('quantity') == 0) {
            return false;
        }
        return true;
    }

    public function checkIfCarted()
    {
        $cartedProducts = Auth::user()->cart->cartedProducts->pluck('dated_product_id');
        $datedProducts = $this->datedProducts->pluck('id');
        if (count($cartedProducts->intersect($datedProducts)) != 0) {
            return true;
        }
    }

    public function getQuantity()
    {
        return $this->datedProducts->sum('quantity');
    }

    /**
     * Relationships
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'id', 'id');
    }


    public function datedProducts()
    {
        return $this->hasMany(DatedProduct::class, 'product_id', 'id');
    }
}
