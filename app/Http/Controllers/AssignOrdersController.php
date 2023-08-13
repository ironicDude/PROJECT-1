<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class AssignOrdersController extends Controller
{

    public function delivery_boys(){
        return response()->json([
            'delivery_boys' => User::query()
                ->join('employee_role', 'employee_role.employee_id', '=', 'users.id')
                ->join('roles', 'employee_role.role_id', '=', 'roles.id')
                ->where('roles.role', '=', 'delivery_boy')
                ->get()
        ]);
    }
    public function assign(Request $request){

        $request->validate([
            'employee_id' => ['required', 'exists:users,id'],
            'order_id' => ['required', 'exists:orders,id'],
        ]);

        Order::query()->find($request['order_id'])->update([
           'employee_id' => $request['employee_id'],
           'status' => "En Route"
        ]);

        return response()->json(['order' => Order::query()->find($request['order_id'])]);

    }

}
