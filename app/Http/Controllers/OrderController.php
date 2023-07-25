<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Resources\CustomResponse;
use App\Http\Resources\OrderFullResource;
use App\Models\Order;

class OrderController extends Controller
{
    use CustomResponse;
    public function index(Customer $customer)
    {
        $this->authorize('viewOrders', $customer);
        $orders = $customer->viewOrders();
        return $orders;
    }

    public function show(Order $order)
    {
        $this->authorize('show', $order);
        return self::customResponse('Order returned', new OrderFullResource($order), 200);
    }
}
