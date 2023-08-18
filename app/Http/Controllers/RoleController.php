<?php

namespace App\Http\Controllers;

use App\Http\Resources\CustomResponse;
use Illuminate\Http\Request;
use App\Models\EmployeeRole;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    use CustomResponse;
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),
        [
            'role' => 'required|string',
        ]);
        if($validator->fails()){
            return self::customResponse('errors', $validator->errors(), 422);
        }
        $role = Role::create($request->all());
        return self::customResponse('Role created', $role, 200);
    }

    public function index()
    {
        return self::customResponse('roled returned', Role::all(), 200);
    }

    public function delete(Role $role)
    {
        $role->delete();
        return self::customResponse('roled returned', $role, 200);
    }

    public function update(Role $role, Request $request)
    {
        $validator = Validator::make($request->all(),
        [
            'role' => 'required|string',
        ]);
        if($validator->fails()){
            return self::customResponse('errors', $validator->errors(), 422);
        }
        $role = $role->update($request->all());
        return self::customResponse('Role updated', $role, 200);
    }

}

