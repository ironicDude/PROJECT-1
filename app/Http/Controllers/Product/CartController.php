<?php

namespace App\Http\Controllers\Product;

use App\Exceptions\CheckoutOutOfStockException;
use App\Exceptions\EmptyCartException;
use App\Exceptions\InShortageException;
use App\Exceptions\ItemNotInCartException;
use App\Exceptions\LimitedStockException;
use App\Exceptions\NoPrescriptionsException;
use App\Exceptions\NotEnoughMoneyException;
use App\Exceptions\NullAddressException;
use App\Exceptions\NullQuantityException;
use App\Exceptions\OutOfStockException;
use App\Exceptions\PrescriptionRequiredException;
use App\Exceptions\ProductAlreadyAddedException;
use App\Exceptions\ProductAlreadyInCartException;
use App\Exceptions\QuantityExceededOrderLimitException;
use App\Exceptions\SameQuantityException;
use App\Http\Controllers\Controller;
use App\Http\Resources\Cart\CartResource;
use App\Http\Resources\CartedProductResource;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Resources\CustomResponse;
use App\Models\CartedProduct;
use App\Models\PurchasedProduct;
use App\Models\User;
use Illuminate\Queue\NullQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ItemNotFoundException;

class CartController extends Controller
{
    use CustomResponse;

    public function store(PurchasedProduct $purchasedProduct, Request $request)
    {
        $this->authorize('storeInCart', $purchasedProduct);
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1'
        ]);
        if($validator->fails()){
            return self::customResponse('errors', $validator->errors(), 422);
        }
        try {
            $cart = Auth::user()->createCart();
            $product = $cart->addProduct($purchasedProduct, $request->quantity);
        } catch (QuantityExceededOrderLimitException $e) {
            return self::customResponse('For some regulatory purposes, you cannot order as many of this product', null, 422);
        } catch (OutOfStockException $e) {
            return self::customResponse('Out of stock', null, 422);
        } catch (InShortageException $e){
            return self::customResponse($e->getMessage(), null, 422);
        } catch (ProductAlreadyAddedException $e){
            return self::customResponse('Product already added. You can change its quantity.', null, 422);
        }
        return self::customResponse('Product stored', $product, 200);
    }

    public function remove(Cart $cart, PurchasedProduct $purchasedProduct)
    {
        $this->authorize('manageCart', $cart);
        $product = $cart->removeProduct($purchasedProduct);
        return self::customResponse('Product removed', $product, 200);
    }

    public function updateQuantity(Request $request, Cart $cart, PurchasedProduct $purchasedProduct)
    {
        $this->authorize('manageCart', $cart);
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1'
        ]);
        if($validator->fails()){
            return self::customResponse('errors', $validator->errors(), 422);
        }
        try {
            $quantity = $cart->updateQuantity($purchasedProduct, $request->quantity);
        } catch (QuantityExceededOrderLimitException $e) {
            return self::customResponse('For some regulatory purposes, you cannot order as many of this product', null, 422);
        } catch (OutOfStockException $e) {
            return self::customResponse('Out of stock', null, 422);
        } catch (InShortageException $e){
            return self::customResponse($e->getMessage(), null, 422);
        } catch (SameQuantityException $e){
            return self::customResponse('The provided quantity is the same as before', null, 422);
        }
        return self::customResponse('Quantity updated', $quantity, 200);
    }

    public function storeAddress(Request $request, Cart $cart)
    {
        $validator = Validator::make($request->all(), [
            'address' => 'required|string'
        ]);
        if($validator->fails()){
            return self::customResponse('errors', $validator->errors(), 422);
        }
        $this->authorize('manageCart', $cart);
        $address = $cart->storeAdress($request->address);
        return self::customResponse('Address stored', $address, 200);
    }

    public function getAddress(Cart $cart)
    {
        $this->authorize('manageCart', $cart);
        $address = $cart->getAddress();
        return self::customResponse('Address returned', $address, 200);
    }


    public function show(Cart $cart)
    {
        $this->authorize('manageCart', $cart);
        $cart = $cart->show();
        return self::customResponse('Cart info returned', new CartResource($cart), 200);
    }


    public function checkout(Request $request, Cart $cart)
    {
        $this->authorize('manageCart', $cart);

        $validator = Validator::make($request->all(), [
            'address' => 'nullable|string'
        ]);
        if($validator->fails()){
            return self::customResponse('errors', $validator->errors(), 422);
        }
        try {
            $cart->checkout($request->address);
        } catch (NullAddressException $e) {
            return self::customResponse('Please, provide an shipping address', null, 422);
        } catch (NotEnoughMoneyException $e) {
            return self::customResponse('You bank account does not have enough credit to complete the transaction', null, 422);
        } catch (PrescriptionRequiredException $e) {
            return self::customResponse('You order contains prescription drugs. Please, add the prescription for each product to continue', null, 422);
        } catch (CheckoutOutOfStockException $e){
            return self::customResponse($e->getMessage(), null, 422);
        } catch (InShortageException $e){
            return self::customResponse($e->getMessage(), null, 422);
        }
        return self::customResponse('Purchase complete', null, 200);
    }

    public function getQuantity(Cart $cart)
    {
        $this->authorize('manageCart', $cart);
        $quantity = $cart->getQuantity();
        return self::customResponse('Quantity returned', $quantity, 200);
    }


    public function getTotal(Cart $cart)
    {
        $this->authorize('manageCart', $cart);
        $total = $cart->getTotal();
        return self::customResponse('Total returned', $total, 200);
    }


    public function clear(Cart $cart)
    {
        $this->authorize('manageCart', $cart);
        $cart->clear();
        return self::customResponse('Cart cleared', null, 200);
    }

    public function storePrescriptions(Request $request, Cart $cart)
    {
        $this->authorize('manageCart', $cart);
        $validator = Validator::make($request->all(), [
            'files' => 'required|array|max:5',
            'files.*' => 'max:4096|mimes:png,jpg,pdf,jpeg',
        ]);
        if($validator->fails()){
            return self::customResponse('errors', $validator->errors(), 422);
        }
        $prescriptionNames = $cart->storePrescriptions($request->file('files'));
        return self::customResponse('Prescriptions stored', $prescriptionNames, 200);
    }

    public function deletePrescriptions(Cart $cart)
    {
        $this->authorize('manageCart', $cart);
        try{
           $prescriptions = $cart->deletePrescriptions();
        } catch(NoPrescriptionsException $e){
            return self::customResponse($e->getMessage(), null, 422);
        }
        return self::customResponse('Prescriptions deleted', $prescriptions, 200);
    }


    public function checkForPrescriptions(Cart $cart)
    {
        $this->authorize('manageCart', $cart);
        $status = $cart->checkPrescriptionsUpload();
        return self::customResponse('Status returned', $status, 200);
    }
}
