<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class AssignOrdersController extends Controller
{
    // public function
    public function delivery_boys(){
        // get all the delivery boys who are available
        $delivery_boys = User::query()
            ->join('employee_role', 'employee_role.employee_id', '=', 'users.id')
            ->join('roles', 'employee_role.role_id', '=', 'roles.id')
            ->where('roles.role', '=', 'delivery_boy')
            ->where('users.account_status', '=', 'active')
            ->get();

        // check if there are any available delivery boys
        if ($delivery_boys->count() > 0) {
            // return the available delivery boys as json
            return response()->json([
                'delivery_boys' => $delivery_boys
            ]);
        } else {
            // return an error message if there are no available delivery boys
            return response()->json([
                'error' => 'There are no available delivery boys at the moment'
            ]);
        }
    }

    public function assign(Request $request){

        $request->validate([
            'employee_id' => ['required', 'exists:users,id'],
            'order_id' => ['required', 'exists:orders,id'],
        ]);

        Order::query()->find($request['order_id'])->update([
           'delivery_employee_id' => $request['employee_id'],
           'status' => "En Route"
        ]);
        user::query()->find($request['employee_id'])->update([
            'account_status' => "busy"
         ]);

        return response()->json(['order' => Order::query()->find($request['order_id'])]);

    }

}
