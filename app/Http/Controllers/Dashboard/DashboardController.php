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
        $this->authenticate('viewRevenue', Oder::Class);
        $validator = Validator::make($request->all(), [
            'days' => 'nullable|integer'
        ]);

        if($validator->fails()){
            return self::customResponse('errors', $validator->errors(), 422);
        }
        $days = !$request->days ? 0 : $request->days;
        $revenue = Order::calculateRevenueInDays($days);
        return self::customResponse('revenue', $revenue, 200);
    }

    public function countNewCustomers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'days' => 'nullable|integer'
        ]);

        if($validator->fails()){
            return self::customResponse('errors', $validator->errors(), 422);
        }
        $days = !$request->days ? 0 : $request->days;
        $count = Customer::countNewbies($days);
        return self::customResponse('count', $count, 200);
    }

    public function getBestSellingProducts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'days' => 'nullable|integer'
        ]);

        if($validator->fails()){
            return self::customResponse('errors', $validator->errors(), 422);
        }
        $days = !$request->days ? 0 : $request->days;
        $products = PurchasedProduct::getBestSelling($days);
        return self::customResponse('Best selling products', $products, 200);
    }
}
