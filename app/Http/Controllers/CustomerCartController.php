<?php

namespace App\Http\Controllers;

use App\Exceptions\EmptyCartException;
use App\Exceptions\ItemNotInCartException;
use App\Exceptions\NullQuantityException;
use App\Exceptions\OutOfStockException;
use App\Exceptions\QuantityExceededOrderLimitException;
use App\Http\Resources\CustomerCartResource;
use App\Models\CustomerCart;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Resources\CustomResponse;
use App\Models\CartedProduct;
use App\Models\PurchasedProduct;
use Illuminate\Queue\NullQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ItemNotFoundException;

class CustomerCartController extends Controller
{
    use CustomResponse;

    public function store(Product $product, Request $request)
    {
        try{
            CustomerCart::addItem($product, $request);
        }
        catch(QuantityExceededOrderLimitException $e){
            return self::customResponse("For some regulatory purposes, you cannot order as many of this product", null, 403);
        }
        catch(OutOfStockException $e){
            return self::customResponse("out of stock", null, 403);
        }
        return self::customResponse('item stored', null, 200);
    }

    public function remove(CartedProduct $cartedProduct)
    {
        CustomerCart::removeItem($cartedProduct);
        return self::customResponse('item removed', null, 200);
    }

    public function updateQuantity(CartedProduct $cartedProduct, Request $request)
    {
        try{
            self::updateQuantity($cartedProduct, $request);
        }
        catch(QuantityExceededOrderLimitException $e){
            return self::customResponse("For some regulatory purposes, you cannot order as many of this product", null, 403);
        }
        catch(OutOfStockException $e){
            return self::customResponse("out of stock", null, 403);
        }
        return self::customResponse('Quantity updated', null, 200);
    }

    public function storeAddress(Request $request)
    {
        CustomerCart::storeAdress($request);
        return self::customResponse('Address stored', null, 200);
    }

    public function getAddress()
    {
        $address = CustomerCart::getAddress();
        return self::customResponse('quantity returned', $address, 200);
    }
    public function show()
    {
        $cart = Auth::user()->cart;
        return self::customResponse('cart info returned', new CustomerCartResource($cart), 200);
    }

    public function checkout()
    {
        CustomerCart::checkout();
        return self::customResponse('purchase complete', null, 200);
    }

    public function getQuantity()
    {
        $quantity = CustomerCart::getQuantity();
        return self::customResponse('quantity returned', $quantity, 200);
    }

    public function getTotal()
    {
        $total = CustomerCart::getTotal();
        return self::customResponse('quantity returned', $total, 200);
    }

    public function clear()
    {
        CustomerCart::clear();
        return self::customResponse('cart cleared', null, 200);
    }

    public function storePrescriptions(Request $request)
    {
        CustomerCart::storePrescriptions($request);
        return self::customResponse('Prescriptions stored', null, 200);
    }
}


