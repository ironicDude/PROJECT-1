<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomResponse;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    use CustomResponse;
    public function searchNames(Request $request)
    {
        $validator = Validator::make($request->all(),
        [
            'string' => 'required|string',
        ]);

        if($validator->fails()){
            return self::customResponse('errors', $validator->errors(), 422);
        }
        $customers = Customer::searchNames($request->string);

        if (count($customers) == 0) {
            return self::customResponse('No matches to return', null, 404);
        } else {
            return self::customResponse('Matches returned', $customers, 200);
        }
    }
}
