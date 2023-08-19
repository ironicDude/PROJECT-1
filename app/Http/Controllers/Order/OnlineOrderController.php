<?php

namespace App\Http\Controllers\Order;

use App\Exceptions\CheckoutOutOfStockException;
use App\Exceptions\EmptyOrderException;
use App\Exceptions\InShortageException;
use App\Exceptions\NoPrescriptionsException;
use App\Exceptions\OutOfStockException;
use App\Exceptions\PrescriptionRequiredException;
use App\Exceptions\ProductAlreadyAddedException;
use App\Exceptions\QuantityExceededOrderLimitException;
use App\Exceptions\SameQuantityException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\CustomResponse;
use App\Http\Resources\Order\OrderFullResource;
use App\Http\Resources\Product\PurchasedProductResource;
use App\Models\OnlineOrder;
use App\Models\PurchasedProduct;
use Illuminate\Support\Facades\Validator;

class OnlineOrderController extends Controller
{
    use CustomResponse;

    public function store(OnlineOrder $onlineOrder, PurchasedProduct $purchasedProduct, Request $request)
    {
        // $this->authorize('manage', $onlineOrder);
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1'
        ]);
        if($validator->fails()){
            return self::customResponse('errors', $validator->errors(), 422);
        }
        try {
            $product = $onlineOrder->addProduct($purchasedProduct, $request->quantity);
        } catch (QuantityExceededOrderLimitException $e) {
            return self::customResponse('For some regulatory purposes, The order cannot include as many of this product', null, 422);
        } catch (OutOfStockException $e) {
            return self::customResponse('Out of stock', null, 422);
        } catch (InShortageException $e){
            return self::customResponse($e->getMessage(), null, 422);
        } catch (ProductAlreadyAddedException $e){
            return self::customResponse('Product already added. You can change its quantity.', null, 422);
        }
        return self::customResponse('Product stored', new PurchasedProductResource($product), 200);
    }

    public function remove(OnlineOrder $onlineOrder, PurchasedProduct $purchasedProduct)
    {
        // $this->authorize('manage', $onlineOrder);
        $product = $onlineOrder->removeProduct($purchasedProduct);
        return self::customResponse('Product removed', new PurchasedProductResource($product), 200);
    }

    public function updateQuantity(Request $request, OnlineOrder $onlineOrder, PurchasedProduct $purchasedProduct)
    {
        // $this->authorize('manage', $onlineOrder);
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1'
        ]);
        if($validator->fails()){
            return self::customResponse('errors', $validator->errors(), 422);
        }
        try {
            $quantity = $onlineOrder->updateQuantity($purchasedProduct, $request->quantity);
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

    public function checkout(Request $request, OnlineOrder $onlineOrder)
    {
        // $this->authorize('manage', $onlineOrder);

        $validator = Validator::make($request->all(), [
            'address' => 'required|string'
        ]);
        if($validator->fails()){
            return self::customResponse('errors', $validator->errors(), 422);
        }
        try {
            $onlineOrder->reCheckout($request->address);
        } catch (PrescriptionRequiredException $e) {
            return self::customResponse('The order contains prescription drugs. Please, add the prescription for each product to continue', null, 422);
        } catch (CheckoutOutOfStockException $e){
            return self::customResponse($e->getMessage(), null, 422);
        } catch (InShortageException $e){
            return self::customResponse($e->getMessage(), null, 422);
        } catch (EmptyOrderException $e){
            return self::customResponse($e->getMessage(), null, 422);
        }
        return self::customResponse('Purchase complete', null, 200);
    }

    public function storePrescriptions(Request $request, OnlineOrder $onlineOrder)
    {
        // $this->authorize('manage', $onlineOrder);
        $validator = Validator::make($request->all(), [
            'files' => 'required|array|max:5',
            'files.*' => 'max:4096|mimes:png,jpg,pdf,jpeg',
        ]);
        if($validator->fails()){
            return self::customResponse('errors', $validator->errors(), 422);
        }
        $prescriptionNames = $onlineOrder->storePrescriptions($request->file('files'));
        return self::customResponse('Prescriptions stored', $prescriptionNames, 200);
    }

    public function deletePrescriptions(OnlineOrder $onlineOrder)
    {
        // $this->authorize('manage', $onlineOrder);
        try{
           $prescriptions = $onlineOrder->deletePrescriptions();
        } catch(NoPrescriptionsException $e){
            return self::customResponse($e->getMessage(), null, 422);
        }
        return self::customResponse('Prescriptions deleted', $prescriptions, 200);
    }

    public function checkForPrescriptions(OnlineOrder $onlineOrder)
    {
        // $this->authorize('manage', $onlineOrder);
        $status = $onlineOrder->checkForPrescriptions();
        return self::customResponse('Status returned', $status, 200);
    }

    public function delete(OnlineOrder $onlineOrder)
    {
        // $this->authorize('manage', $onlineOrder);
        $onlineOrder->destroyOrder();
        return self::customResponse('Order deleted', null, 200);
    }

    public function storeShippingAddress(Request $request, OnlineOrder $onlineOrder)
    {
        // $this->authorize('manage', $onlineOrder);
        $validator = Validator::make($request->all(), [
            'address' => 'required|string'
        ]);
        if($validator->fails()){
            return self::customResponse('errors', $validator->errors(), 422);
        }
        // $this->authorize('manage', $onlineOrder);
        $address = $onlineOrder->storeShippingAddress($request->address);
        return self::customResponse('Shipping address stored', $address, 200);
    }

    public function getShippingAddress(OnlineOrder $onlineOrder)
    {
        // $this->authorize('manage', $onlineOrder);
        $address = $onlineOrder->getShippingAddress();
        return self::customResponse('Shipping address returned', $address, 200);
    }

    public function dispatch(OnlineOrder $onlineOrder)
    {

        $onlineOrder->dispatch();
        return self::customResponse('Order dispatched', new OrderFullResource($onlineOrder), 200);
    }

    public function reject(OnlineOrder $onlineOrder, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string'
        ]);
        if($validator->fails()){
            return self::customResponse('errors', $validator->errors(), 422);
        }
        $onlineOrder->reject($request->reason);
        return self::customResponse('Order rejected', new OrderFullResource($onlineOrder), 200);
    }

}
