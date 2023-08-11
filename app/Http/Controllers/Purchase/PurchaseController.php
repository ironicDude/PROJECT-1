<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use App\Http\Resources\Purchase\PurchaseFullResource;
use App\Http\Resources\Purchase\PurchaseOverviewCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\CustomResponse;
use App\Models\Purchase;

class PurchaseController extends Controller
{
    use CustomResponse;
    public function index(Request $request)
    {
        $this->authorize('viewAll', Purchase::class);
        $validator = Validator::make($request->all(),
        [
            'date' => 'date'
        ]);

        if($validator->fails()){
            return self::customResponse('errors', $validator->errors(), 422);
        }
        $purchases = Purchase::getAllPurchases($request->date);
        return new PurchaseOverviewCollection($purchases->paginate(10));
    }

    public function show(Purchase $purchase)
    {
        $this->authorize('show', $purchase);
        return self::customResponse('Purchase returned', new PurchaseFullResource($purchase), 200);
    }
}
