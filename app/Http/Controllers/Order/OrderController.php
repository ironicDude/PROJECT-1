<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Resources\CustomResponse;
use App\Http\Resources\Order\OrderFullResource;
use App\Http\Resources\Order\OrderOverviewCollection;
use App\Models\Order;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    use CustomResponse;
    public function getCustomerOrders(Customer $customer, Request $request)
    {
        $this->authorize('viewOrders', $customer);
        $validator = Validator::make($request->all(),
        [
            'date' => 'date'
        ]);
        if($validator->fails()){
            return self::customResponse('errors', $validator->errors(), 422);
        }
        $orders = Order::getCustomerOrders($customer->id, $request->date);
        return new OrderOverviewCollection($orders->paginate(10));
    }

    public function show(Order $order)
    {
        $this->authorize('show', $order);
        return self::customResponse('Order returned', new OrderFullResource($order), 200);
    }

    public function getPrescriptions(Order $order)
    {
        $this->authorize('viewPrescriptions', $order);
        $data = $order->viewPrescriptions();
        return response()->json(['files'=> $data]);
    }

    public function index(Request $request)
    {
        $this->authorize('viewAll', Order::class);
        $validator = Validator::make($request->all(),
        [
            'date' => 'date'
        ]);

        if($validator->fails()){
            return self::customResponse('errors', $validator->errors(), 422);
        }
        $orders = Order::getAllOrders($request->date);
        return new OrderOverviewCollection($orders->paginate(10));
    }
}
