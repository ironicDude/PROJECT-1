<?php

namespace App\Http\Controllers;

use App\Exceptions\EmptyCartException;
use App\Exceptions\ItemNotInCartException;
use App\Exceptions\NullAddressException;
use App\Exceptions\NullQuantityException;
use App\Exceptions\OutOfStockException;
use App\Exceptions\PrescriptionRequiredException;
use App\Exceptions\QuantityExceededOrderLimitException;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Resources\CustomResponse;
use App\Models\CartedProduct;
use App\Models\PurchasedProduct;
use Illuminate\Queue\NullQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ItemNotFoundException;

class CartController extends Controller
{
    use CustomResponse;

    public function store(Product $product, Request $request)
    {
        try{
            Cart::addItem($product, $request);
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
        Cart::removeItem($cartedProduct);
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
        try{
           $addrss = Cart::storeAdress($request);
        }
        catch(EmptyCartException $e){
            return self::customResponse('Cart is empty', null, 404);
        }
        return self::customResponse('Address stored', null, 200);
    }

    public function getAddress()
    {
        try{
           $address = Cart::getAddress();
        }
        catch(EmptyCartException $e){
            return self::customResponse('cart is empty', null, 404);
        }
        return self::customResponse('address returned', $address, 200);
    }

    public function show()
    {
        try{
            $cart = Auth::user()->cart;
        }
         catch(EmptyCartException $e){
            return self::customResponse('cart is empty', null, 404);
        }
        return self::customResponse('cart info returned', new CartResource($cart), 200);
    }

    public function checkout(Request $request)
    {
        try{
            Cart::checkout($request);
        }
         catch(EmptyCartException $e){
             return self::customResponse('cart is empty', null, 404);
         }
         catch(NullAddressException $e){
            return self::customResponse('Address is required', null, 403);
         }
         catch(PrescriptionRequiredException $e){
            return self::customResponse('Prescriptions required', null, 403);
         }
        return self::customResponse('purchase complete', null, 200);
    }

    public function getQuantity()
    {
        try{
            $quantity = Cart::getQuantity();
        }
         catch(EmptyCartException $e){
             return self::customResponse('cart is empty', null, 404);
         }
        return self::customResponse('quantity returned', $quantity, 200);
    }

    public function getTotal()
    {
        try{
            $total = Cart::getTotal();
         }
         catch(EmptyCartException $e){
             return self::customResponse('cart is empty', null, 404);
        }
        return self::customResponse('total returned', $total, 200);
    }

    public function clear()
    {
        try{
            Cart::clear();
        }
         catch(EmptyCartException $e){
             return self::customResponse('cart is empty', null, 404);
         }
        return self::customResponse('cart cleared', null, 200);
    }

    public function storePrescriptions(Request $request)
    {
        try{
            Cart::storePrescriptions($request);
        }
         catch(EmptyCartException $e){
             return self::customResponse('cart is empty', null, 404);
         }
        return self::customResponse('Prescriptions stored', null, 200);
    }

    public function checkPrescriptionsUpload()
    {
        try{
           $status = Cart::checkPrescriptionsUpload();
        }
         catch(EmptyCartException $e){
             return self::customResponse('cart is empty', null, 404);
         }
        return self::customResponse('status returned', $status, 200);
    }
}


