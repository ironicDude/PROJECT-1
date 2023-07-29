<?php

namespace App\Models;

use App\Exceptions\OutOfStockException;
use App\Http\Resources\PurchasedProductCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
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

    public function getPrice()
    {
        return $this->price;
    }

    public function getMinimumStockLevel()
    {
        return $this->minimum_stock_level;
    }

    public function getOrderLimit()
    {
        return $this->order_limit;
    }

    public function setPrice(float $price)
    {
        $this->price = $price;
        $this->save();
        return $this->price;
    }

    public function setMinimumStockLevel(int $minimumStockLevel)
    {
        $this->minimum_stock_level = $minimumStockLevel;
        $this->save();
        return $this->minimum_stock_level;
    }

    public function setOrderLimit(int $orderLimit)
    {
        $this->order_limit = $orderLimit;
        $this->save();
        return $this->order_limit;
    }

    public static function index()
    {
        return new PurchasedProductCollection(self::paginate(20));
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
