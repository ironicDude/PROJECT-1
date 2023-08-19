<?php

namespace App\Http\Controllers\User;

use App\Exceptions\EmployeeIsAlreadyAssignedThisRoleException;
use App\Http\Controllers\Controller;
use App\Http\Resources\Payment\PaymentCollection;
use App\Http\Resources\User\EmployeeCollection;
use App\Http\Resources\User\EmployeeResource;
use Illuminate\Http\Request;
use App\Http\Resources\CustomResponse;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    use CustomResponse;

    public function index()
    {
        return new EmployeeCollection(Employee::paginate(15));
    }

    public function show(Employee $employee)
    {
        return self::customResponse('employee', new EmployeeResource($employee), 200);
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();
        return self::customResponse('employee deleted', null, 200);
    }

    public function getPersonalEmail(Employee $employee)
    {
        $email = $employee->getPersonalEmail();
        return self::customResponse('Personal email returned', $email, 200);
    }

    public function setPersonalEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
        ]);

        if ($validator->fails()) {
            return self::customResponse('errors', $validator->erros(), 422);
        }

        $email = Auth::user()->setPersonalEmail($request->input('email'));
        return self::customResponse('Personal email set', $email, 200);
    }

    public function getSalary(Employee $employee)
    {
        $salary = $employee->getSalary();
        return self::customResponse('Salary returned', $salary, 200);
    }


    public function setSalary(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'salary' => 'required|max:255|numeric',
        ]);

        if ($validator->fails()) {
            return self::customResponse('erros', $validator->errors(), 422);
        }

        $salary = Auth::user()->setSalary($request->salary);
        return self::customResponse('Salary set', $salary, 200);
    }

    public function getDateOfJoining(Employee $employee)
    {
        $date = $employee->getDateOfJoining();
        return self::customResponse('Date of joining returned', $date, 200);
    }

    public function updateInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'personalEmail' => "required|string|email|not_in:" . Auth::user()->getEmail(),
        ], ['personalEmail.not_in' => 'The personal email should be different from the work email']);

        if ($validator->fails()) {
            return self::customResponse('errors', $validator->errors(), 422);
        }

        $newInfo = $request->toArray();
        $employeeInfo = new EmployeeResource(Auth::user()->updateEmployeeInfo($newInfo));
        return self::customResponse('Employee with new info', $employeeInfo, 200);
    }

    public function setRole(Employee $employee, Role $role)
    {
        $this->authorize('manageRoles', $employee);
        try {
            $role = $employee->setRole($role);
        } catch(EmployeeIsAlreadyAssignedThisRoleException $e) {
            return self::customResponse($e->getMessage(), null, 422);
        }
        return self::customResponse('Role set', $role, 200);
    }

    public function getPayments(Employee $employee)
    {
        $this->authorize('viewPayments', $employee);
        return self::customResponse('Payments returned', new PaymentCollection($employee->receivedPayments), 200);
    }

    public function getRoles(Employee $employee)
    {
        $this->authorize('manageRoles', $employee);
        return self::customResponse('Roles returned', $employee->getRoles(), 200);
    }

    public function updateRole(Employee $employee, Role $role, Role $newRole)
    {
        $this->authorize('manageRoles', $employee);
        $employee->updateRole($role, $newRole);
        return self::customResponse('role updated', $role, 200);
    }

    public function deleteRole(Employee $employee, Role $role)
    {
        $this->authorize('manageRoles', $employee);
        $employee->deleteRole($role);
        return self::customResponse('role deleted', null, 200);
    }


}
