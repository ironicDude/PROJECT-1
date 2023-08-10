<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\CustomResponse;
use App\Models\Purchase;

class PurchaseController extends Controller
{
    use CustomResponse;
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(),
        [
            'date' => 'date'
        ]);

        if($validator->fails()){
            return self::customResponse('errors', $validator->errors(), 422);
        }
        $orders = Purchase::getAllPurchases($request->date);
        return new PurchaseOverviewCollection($orders->paginate(10));
    }
}
