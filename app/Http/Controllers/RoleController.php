<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmployeeRole;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;

class RoleController extends Controller
{
    //
    public function assignRole(Request $request)
    {
        $request->validate([
            // 'employee_id' => 'required|exists:employees,id',
            'employee_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id',
        ]);
    
        $employeeRole = new EmployeeRole([
            'employee_id' => $request->input('employee_id'),
            'role_id' => $request->input('role_id'),
        ]);
    
        $employeeRole->save();
    
        return response()->json(['message' => 'تم تعيين الدور بنجاح.']);
    }
}

