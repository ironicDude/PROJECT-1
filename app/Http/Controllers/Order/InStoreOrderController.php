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
use App\Http\Resources\Product\ProductOverviewResource;
use Illuminate\Http\Request;
use App\Http\Resources\CustomResponse;
use App\Http\Resources\Order\OrderFullResource;
use App\Http\Resources\Product\PurchasedProductResource;
use App\Models\InStoreOrder;
use App\Models\Order;
use App\Models\PurchasedProduct;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\In;

class InStoreOrderController extends Controller
{
    use CustomResponse;

    public function addNewOrder()
    {
        $this->authorize('manageInStoreOrder', InStoreOrder::class);
        $order = InStoreOrder::create([
            'status' => 'Progressing',
            'method' => 'storely',
            'employee_id' => Auth::user()->id,
        ]);
        return self::customResponse('order created', new OrderFullResource($order), 200);
    }
    public function store(InStoreOrder $inStoreOrder, PurchasedProduct $purchasedProduct, Request $request)
    {
        // $this->authorize('manageInStoreOrder', $inStoreOrder);
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1'
        ]);
        if($validator->fails()){
            return self::customResponse('errors', $validator->errors(), 422);
        }
        try {
            $product = $inStoreOrder->addProduct($purchasedProduct, $request->quantity);
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

    public function remove(InStoreOrder $inStoreOrder, PurchasedProduct $purchasedProduct)
    {
        // $this->authorize('manageInStoreOrder', $inStoreOrder);
        $product = $inStoreOrder->removeProduct($purchasedProduct);
        return self::customResponse('Product removed', new PurchasedProductResource($product), 200);
    }

    public function updateQuantity(Request $request, InStoreOrder $inStoreOrder, PurchasedProduct $purchasedProduct)
    {
        // $this->authorize('manageInStoreOrder', $inStoreOrder);
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1'
        ]);
        if($validator->fails()){
            return self::customResponse('errors', $validator->errors(), 422);
        }
        try {
            $quantity = $inStoreOrder->updateQuantity($purchasedProduct, $request->quantity);
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

    public function checkout(Request $request, InStoreOrder $inStoreOrder)
    {
        // $this->authorize('manageInStoreOrder', $inStoreOrder);

        $validator = Validator::make($request->all(), [
            'address' => 'nullable|string'
        ]);
        if($validator->fails()){
            return self::customResponse('errors', $validator->errors(), 422);
        }
        try {
            $inStoreOrder->checkout();
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

    public function clear(InStoreOrder $inStoreOrder)
    {
        // $this->authorize('manageInStoreOrder', $inStoreOrder);
        $inStoreOrder->clear();
        return self::customResponse('Order cleared', null, 200);
    }

    public function storePrescriptions(Request $request, InStoreOrder $inStoreOrder)
    {
        // $this->authorize('manageInStoreOrder', $inStoreOrder);
        $validator = Validator::make($request->all(), [
            'files' => 'required|array|max:5',
            'files.*' => 'max:4096|mimes:png,jpg,pdf,jpeg',
        ]);
        if($validator->fails()){
            return self::customResponse('errors', $validator->errors(), 422);
        }
        $prescriptionNames = $inStoreOrder->storePrescriptions($request->file('files'));
        return self::customResponse('Prescriptions stored', $prescriptionNames, 200);
    }

    public function deletePrescriptions(InStoreOrder $inStoreOrder)
    {
        // $this->authorize('manageInStoreOrder', $inStoreOrder);
        try{
           $prescriptions = $inStoreOrder->deletePrescriptions();
        } catch(NoPrescriptionsException $e){
            return self::customResponse($e->getMessage(), null, 422);
        }
        return self::customResponse('Prescriptions deleted', $prescriptions, 200);
    }

    public function checkForPrescriptions(InStoreOrder $inStoreOrder)
    {
        // $this->authorize('manageInStoreOrder', $inStoreOrder);
        $status = $inStoreOrder->checkForPrescriptions();
        return self::customResponse('Status returned', $status, 200);
    }

    public function delete(InStoreOrder $inStoreOrder)
    {
        // $this->authorize('manageInStoreOrder', $inStoreOrder);
        $inStoreOrder->destroyOrder();
        return self::customResponse('Order deleted', null, 200);
    }
}
