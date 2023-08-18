<?php

namespace App\Http\Controllers;

use App\Exceptions\EmployeeAlreadyPaidException;
use App\Http\Resources\CustomResponse;
use App\Http\Resources\Payment\PaymentCollection;
use App\Http\Resources\Payment\PaymentResource;
use App\Models\Employee;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    use CustomResponse;

    public function index()
    {
        $this->authorize('manage', Payment::class);
        return new PaymentCollection(Payment::paginate(15));
    }

    public function create(Request $request, Employee $employee)
    {
        $this->authorize('manage', Payment::class);
        $validator = Validator::make(
            $request->all(),
            [
                'amount' => 'nullable|numeric|min:0'
            ]
        );
        if ($validator->fails()) {
            return self::customResponse('errors', $validator->errors(), 422);
        }
        try {
            $payment = Payment::make($request->amount, $employee);
        } catch(EmployeeAlreadyPaidException $e) {
            return self::customResponse($e->getMessage(), null, 422);
        }
        return self::customResponse('Payment made', new PaymentResource($payment), 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment)
    {
        $this->authorize('view', $payment);
        return self::customResponse('Payment returned', new PaymentResource($payment), 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Payment $payment)
    {
        $this->authorize('update', $payment);
        $validator = Validator::make(
            $request->all(),
            [
                'amount' => 'required|numeric|min:0'
            ]
        );
        if ($validator->fails()) {
            return self::customResponse('errors', $validator->errors(), 422);
        }
        $payment = $payment->edit($request->amount);
        return self::customResponse('Payment updated', new PaymentResource($payment), 200);
    }


}
