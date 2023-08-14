<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\EmployeeResource;
use Illuminate\Http\Request;
use App\Http\Resources\CustomResponse;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    use CustomResponse;

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

    public function getRole(Employee $employee)
    {
        $role = $employee->getRole();
        return self::customResponse('Role returned', $role, 200);
    }

    public function setRole(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|string|in:administrator,pharmacist,inventory manager',
        ]);

        if ($validator->fails()) {
            return self::customResponse('errors', $validator->errors(), 422);
        }

        $role = Auth::user()->setRole($request->input('role'));
        return self::customResponse('Role set', $role, 200);
    }

    public function getDateOfJoining(Employee $employee)
    {
        $date = $employee->getDateOfJoining();
        return self::customResponse('Date of joining returned', $date, 200);
    }

    public function updateInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'personalEmail' => "required|string|email|not_in:". Auth::user()->getEmail(),
        ], ['personalEmail.not_in' => 'The personal email should be different from the work email']);

        // If the validation fails, return a JSON response with the validation errors and status code 422 (Unprocessable Entity).
        if ($validator->fails()) {
            return self::customResponse('errors', $validator->errors(), 422);
        }

        $newInfo = $request->toArray();
        $employeeInfo = new EmployeeResource(Auth::user()->updateEmployeeInfo($newInfo));
        return self::customResponse('Employee with new info', $employeeInfo, 200);
    }
// جلب جميع الموظفين
    public function index()
    {
        $schedules = User::all();
        return response()->json($schedules);
    }
  
        public function show($id)
        {
            $employee = User::find($id);
    
            if (!$employee) {
                return response()->json(['error' => 'الموظف غير موجود.'], 404);
            }
    
            return response()->json($employee);
        }
    

    // اظافة موظف جديد
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:employees,email',
            'password' => 'required|string',
            'address' => 'required|string',
            'date_of_birth' => 'required|date',
            'gender' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'account_status' => 'required|string',
            'salary' => 'required|numeric',
            'personal_email' => 'required|email',
            'date_of_joining' => 'required|date',
        ]);
    
        // تخزين الصورة إذا تم تحميلها
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('employee_images', 'public');
        }
    
        $first_name = $request->input('first_name');
        $last_name = $request->input('last_name');
        $email = $request->input('email');
        $password = $request->input('password');
        $address = $request->input('address');
        $date_of_birth = $request->input('date_of_birth');
        $gender = $request->input('gender');
        $image = $request->input('image');
        $account_status = $request->input('account_status');
        $salary = $request->input('salary');
        $personal_email = $request->input('personal_email');
        $date_of_joining = $request->input('date_of_joining');
    
        $employee = new Employee();
    
        $employee->first_name = $first_name;
        $employee->last_name = $last_name;
        $employee->email = $email;
        $employee->password = $password;
        $employee->address = $address;
        $employee->date_of_birth = $date_of_birth;
        $employee->gender = $gender;
        $employee->image = $image;
        $employee->account_status = $account_status;
        $employee->salary = $salary;
        $employee->personal_email = $personal_email;
        $employee->date_of_joining = $date_of_joining;
        $employee->save();
        
        return response()->json([
            'success'=>'تمت إضافة الموظف بنجاح.',
        ]);
    } 
    // تعديل بينات موظف
    public function update(Request $request, $id)
{
    $employee = User::find($id);

    if (!$employee) {
        return response()->json(['error' => 'الموظف غير موجود.'], 404);
    }

    $validatedData = $request->validate([
        'first_name' => 'required|string',
        'last_name' => 'required|string',
        'email' => 'required|email|unique:employees,email,' . $id,
        'password' => 'required|string',
        'address' => 'required|string',
        'date_of_birth' => 'required|date',
        'gender' => 'required|string',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'account_status' => 'required|string',
        'salary' => 'required|numeric',
        'personal_email' => 'required|email',
        'date_of_joining' => 'required|date',
    ]);

    // تخزين الصورة إذا تم تحميلها
    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('employee_images', 'public');
        $employee->image = $imagePath;
    }

    $employee->first_name = $request->input('first_name');
    $employee->last_name = $request->input('last_name');
    $employee->email = $request->input('email');
    $employee->password = $request->input('password');
    $employee->address = $request->input('address');
    $employee->date_of_birth = $request->input('date_of_birth');
    $employee->gender = $request->input('gender');
    $employee->account_status = $request->input('account_status');
    $employee->salary = $request->input('salary');
    $employee->personal_email = $request->input('personal_email');
    $employee->date_of_joining = $request->input('date_of_joining');
    $employee->save();

    return response()->json(['success' => 'تم تحديث بيانات الموظف بنجاح.']);
}
public function destroy($id)
{
    $employee = User::find($id);

    if (!$employee) {
        return response()->json(['error' => 'الموظف غير موجود.'], 404);
    }

    $employee->delete();

    return response()->json(['success' => 'تم حذف الموظف بنجاح.']);
}


}
