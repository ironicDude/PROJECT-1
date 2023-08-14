<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Resources\CustomResponse;
use App\Http\Resources\Order\OrderFullCollection;
use App\Http\Resources\Order\OrderFullResource;
use App\Models\Order;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;



class OrderController extends Controller
{
    use CustomResponse;
    public function getCustomerOrders(Customer $customer, Request $request)
    {
        // $this->authorize('viewCustomerOrders', $customer);
        $validator = Validator::make($request->all(),
        [
            'date' => 'date'
        ]);
        if($validator->fails()){
            return self::customResponse('errors', $validator->errors(), 422);
        }
        $orders = Order::getCustomerOrders($customer->id, $request->date);
        return new OrderFullCollection($orders->paginate(10));
    }

    public function show(Order $order)
    {
        // $this->authorize('show', $order);
        return self::customResponse('Order returned', new OrderFullResource($order), 200);
    }

    public function getPrescriptions(Order $order)
    {
        $this->authorize('viewPrescriptions', $order);
        $data = $order->viewPrescriptions();
        return response()->json(['files'=> $data]);
    }


// Update And Delete Order
public function update(Request $request , Order $order){
    if ( $order->status == "Review") {
    $input = $request->all();
    $validator = Validator::make($input , [
        'shipping_address'=>'required',
    ]);

    if ($validator->fails()){
        return response()->json([
            'message'=>'You Have Fill Some Data'
        ]);
    }

    $order->shipping_address = $input['shipping_address'];


    return response()->json([
        'message'=>'Order Updated Successfully',
        'Order'=>$input
    ]);
}
else {
    return response()->json([
        'message'=>'Cant Edite That Order'
    ]);
}
}

public function destroy( Order $order){

    if ( $order->status != "Review"){
        return response()->json([
            'message'=>'You Cant Delete That Order'
        ]);
    }else {
        $order->delete();

        return response()->json([
            'message'=>'Order Deleted Successfully',

        ]);
    }
}


    public function index(Request $request)
    {
        // $this->authorize('viewAll', Order::class);
        $validator = Validator::make($request->all(),
        [
            'date' => 'date'
        ]);

        if($validator->fails()){
            return self::customResponse('errors', $validator->errors(), 422);
        }
        $orders = Order::getAllOrders($request->date);
        return new OrderFullCollection($orders->paginate(10));
    }

}

