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

use Validator;


class OrderController extends Controller
{
    use CustomResponse;
    /**
     * Get a list of orders for a specific customer.
     *
     * @param Customer $customer The customer instance for whom to fetch the orders.
     * @return \Illuminate\Database\Eloquent\Collection The collection of orders belonging to the customer.
     */
    public function index(Customer $customer)
    {
        // Check if the authenticated user is authorized to view the orders for the specified customer.
        $this->authorize('viewOrders', $customer);

        // Fetch the list of orders belonging to the specified customer using the 'viewOrders' method of the Customer model.
        $orders = $customer->viewOrders();

        // Return the collection of orders belonging to the customer.
        return new OrderOverviewCollection($orders);
    }

    /**
     * Get the details of a specific order.
     *
     * @param Order $order The order instance to retrieve the details for.
     * @return \Illuminate\Http\JsonResponse The JSON response with the details of the order.
     */
    public function show(Order $order)
    {
        // Check if the authenticated user is authorized to view the details of the specified order.
        $this->authorize('show', $order);

        // Return a custom success response with the details of the order in a resource format.
        return self::customResponse('Order returned', new OrderFullResource($order), 200);
    }

    public function getPrescriptions(Order $order)
    {
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
}