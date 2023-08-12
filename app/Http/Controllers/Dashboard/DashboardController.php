<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomResponse;
use App\Models\Customer;
use App\Models\Order;
use App\Models\PurchasedProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class DashboardController extends Controller
{
    use CustomResponse;

    public function getRevenue(Request $request)
    {
        $this->authorize('viewRevenue', Order::class);

        $validator = Validator::make($request->all(), [
            'days' => 'required|integer|min:0'
        ]);

        if($validator->fails()){
            return self::customResponse('errors', $validator->errors(), 422);
        }
        $revenue = Order::calculateRevenue($request->days);
        return self::customResponse('revenue', $revenue, 200);
    }

    public function countNewCustomers(Request $request)
    {
        $this->authorize('viewCountOfNewCustomers', Customer::class);
        $validator = Validator::make($request->all(), [
            'days' => 'required|integer|min:0'
        ]);

        if($validator->fails()){
            return self::customResponse('errors', $validator->errors(), 422);
        }
        $count = Customer::countNewbies($request->days);
        return self::customResponse('count', $count, 200);
    }

    public function getBestSellingProducts(Request $request)
    {
        // $this->authorize('viewBestSellingProducts', PurchasedProduct::class);
        $validator = Validator::make($request->all(), [
            'days' => 'required|integer|min:0'
        ]);

        if($validator->fails()){
            return self::customResponse('errors', $validator->errors(), 422);
        }
        $products = PurchasedProduct::getBestSelling($request->days);
        return self::customResponse('Best selling products', $products, 200);
    }

    public function getMostProfitableProducts(Request $request)
    {
        // $this->authorize('viewBestSellingProducts', PurchasedProduct::class);
        $validator = Validator::make($request->all(), [
            'days' => 'required|integer|min:0'
        ]);

        if($validator->fails()){
            return self::customResponse('errors', $validator->errors(), 422);
        }
        $products = PurchasedProduct::getMostProfitable($request->days);
        return self::customResponse('Most Profitable products', $products, 200);
    }

    public function countOrders(Request $request)
    {
        // $this->authorize('viewCountOfOrders', Order::class);
        $validator = Validator::make($request->all(), [
            'days' => 'required|integer|min:0'
        ]);

        if($validator->fails()){
            return self::customResponse('errors', $validator->errors(), 422);
        }
        $count = Order::countOrders($request->days);
        return self::customResponse('Count of orders', $count, 200);
    }

    public function chartNewAndLostCustomers(Request $request)
    {
        $this->authorize('viewCustomersChart', Customer::class);
        $validator = Validator::make($request->all(), [
            'date' => 'required|date|before:tomorrow',
            'period' => 'required|string|in:day,week,month,year',
        ]);

        if($validator->fails()){
            return self::customResponse('errors', $validator->errors(), 422);
        }

        $points = Customer::chartNewbiesAndBastards($request->date, $request->period);
        return self::customResponse('points', $points, 200);
    }

    public function chartOrders(Request $request)
    {
        $this->authorize('viewOrdersChart', Order::class);
        $validator = Validator::make($request->all(), [
            'date' => 'required|date|before:tomorrow',
            'period' => 'required|string|in:day,week,month,year',
        ]);

        if($validator->fails()){
            return self::customResponse('errors', $validator->errors(), 422);
        }

        $points = Order::chartOrders($request->date, $request->period);
        return self::customResponse('points', $points, 200);
    }


    public function chartRevenue(Request $request)
    {
        $this->authorize('viewRevenueChart', Order::class);
        $validator = Validator::make($request->all(), [
            'date' => 'required|date|before:tomorrow',
            'period' => 'required|string|in:day,week,month,year',
        ]);

        if($validator->fails()){
            return self::customResponse('errors', $validator->errors(), 422);
        }

        $points = Order::chartRevenue($request->date, $request->period);
        return self::customResponse('points', $points, 200);
    }
}
