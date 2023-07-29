<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\PurchasedProduct;
use Illuminate\Http\Request;
use App\Http\Resources\CustomResponse;
class PurchasedProductController extends Controller
{
    use CustomResponse;
    public function getPrice(PurchasedProduct $purchasedProduct)
    {
        $this->authorize('getAndSet', PurchasedProduct::class);
        $price = $purchasedProduct->getPrice();
        return self::customResponse('Price', $price, 200);
    }

    public function getMinimumStockLevel(PurchasedProduct $purchasedProduct)
    {
        $this->authorize('getAndSet', PurchasedProduct::class);
        $level = $purchasedProduct->getMinimumStockLevel();
        return self::customResponse('Minimum stock level', $level, 200);
    }

    public function getOrderLimit(PurchasedProduct $purchasedProduct)
    {
        $this->authorize('getAndSet', PurchasedProduct::class);
        $orderLimit = $purchasedProduct->getOrderLimit();
        return self::customResponse('Order limit', $orderLimit, 200);
    }

    public function setPrice(PurchasedProduct $purchasedProduct, Request $request)
    {
        $this->authorize('getAndSet', PurchasedProduct::class);
        $request->validate(['price' => 'required']);
        $price = $purchasedProduct->setPrice($request->price);
        return self::customResponse('Price', $price, 200);
    }

    public function setMinimumStockLevel(PurchasedProduct $purchasedProduct, Request $request)
    {
        $this->authorize('getAndSet', PurchasedProduct::class);
        $request->validate(['minimumStockLevel' => 'required']);
        $level = $purchasedProduct->setMinimumStockLevel($request->minimumStockLevel);
        return self::customResponse('Minimum stock level', $level, 200);
    }

    public function setOrderLimit(PurchasedProduct $purchasedProduct, Request $request)
    {
        $this->authorize('getAndSet', PurchasedProduct::class);
        $request->validate(['orderLimit' => 'required']);
        $orderLimit = $purchasedProduct->setOrderLimit($request->orderLimit);
        return self::customResponse('Order limit', $orderLimit, 200);
    }

    public function index()
    {
        $this->authorize('getAndSet', PurchasedProduct::class);
        $products = PurchasedProduct::index();
        if($products->count() == 0){
            return self::customResponse('No purchased products', null, 200);
        }
        return $products;
    }
}
