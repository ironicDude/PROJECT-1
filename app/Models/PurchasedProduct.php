<?php

namespace App\Models;

use App\Exceptions\OutOfStockException;
use App\Http\Resources\Product\PurchasedProductCollection;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchasedProduct extends Model
{
    use HasFactory;

    protected $guarded = [];

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

    public function isMinimumStockLevelSafe()
    {
        return $this->getQuantity() > $this->getMinimumStockLevel();
    }

    public function getName()
    {
        return $this->product->name;
    }

    public function getSafeDistance()
    {
        return $this->getQuantity() - $this->getMinimumStockLevel();
    }
    public static function getBestSelling(int $days)
    {
        $date = Carbon::now()->subDays($days);
        $formattedDate = $date->format('Y-m-d');

        $products = DB::select("SELECT p.id, p.name
                                FROM ordered_products AS op
                                INNER JOIN dated_products AS dp ON op.dated_product_id = dp.id
                                INNER JOIN products AS P ON dp.product_id = p.id
                                WHERE op.created_at > {$formattedDate}
                                GROUP BY p.id, p.name
                                ORDER BY SUM(op.quantity) DESC
                                LIMIT 10");
        return $products;
    }

    public static function getMostProfitable(int $days)
    {
        $date = Carbon::now()->subDays($days);
        $formattedDate = $date->format('Y-m-d');

        $products = DB::select("SELECT p.id, p.name
                                FROM ordered_products AS op
                                INNER JOIN dated_products AS dp ON op.dated_product_id = dp.id
                                INNER JOIN purchased_products AS pp ON dp.product_id = pp.id
                                INNER JOIN products as p ON pp.id = p.id
                                WHERE op.created_at > {$formattedDate}
                                GROUP BY p.id, p.name
                                ORDER BY SUM((op.quantity * (pp.price - dp.purchase_price))) DESC
                                LIMIT 10");
        return $products;
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
