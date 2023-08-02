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

    protected function validateInput(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'days' => 'nullable|integer'
        ]);

        if($validator->fails()){
            return self::customResponse('errors', $validator->errors(), 422);
        }
        $days = !$request->days ? 0 : $request->days;
        return $days;
    }

    public function getRevenue(Request $request)
    {
        $this->authorize('viewRevenue', Order::class);
        $days = $this->validateInput($request);
        $revenue = Order::calculateRevenue($days);
        return self::customResponse('revenue', $revenue, 200);
    }

    public function countNewCustomers(Request $request)
    {
        $this->authorize('viewCountOfNewCustomers', Customer::class);
        $days = $this->validateInput($request);
        $count = Customer::countNewbies($days);
        return self::customResponse('count', $count, 200);
    }

    public function getBestSellingProducts(Request $request)
    {
        $this->authorize('viewBestSellingProducts', PurchasedProduct::class);
        $days = $this->validateInput($request);
        $products = PurchasedProduct::getBestSelling($days);
        return self::customResponse('Best selling products', $products, 200);
    }

    public function getMostProfitableProducts(Request $request)
    {
        $this->authorize('viewBestSellingProducts', PurchasedProduct::class);
        $days = $this->validateInput($request);
        $products = PurchasedProduct::getMostProfitable($days);
        return self::customResponse('Most Profitable products', $products, 200);
    }

    public function countOrders(Request $request)
    {
        $this->authorize('viewCountOfOrders', Order::class);
        $days = $this->validateInput($request);
        $count = Order::countOrders($days);
        return self::customResponse('Count of orders', $count, 200);
    }
}
