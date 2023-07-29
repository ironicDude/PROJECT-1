<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Resources\CustomResponse;
use App\Http\Resources\OrderFullResource;
use App\Models\Order;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

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
        return $orders;
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
}