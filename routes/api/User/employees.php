<?php

use App\Http\Controllers\BackupController;
use App\Http\Controllers\User\EmployeeController;
use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Product\WishlistController;
use App\Http\Controllers\User\CustomerController;
use App\Http\Controllers\User\UserController;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::middleware(['auth', 'forceLogout'])->group(function () {

    //info
    Route::get('/{employee}/salary', [EmployeeController::class, 'getSalary'])
        ->name('employees.salary.get');

    Route::post('/{employee}/salary', [EmployeeController::class, 'setSalary'])
        ->name('employees.salary.set');

    Route::get('/{employee}/personal-email', [EmployeeController::class, 'getPersonalEmail'])
        ->name('employees.personal_email.get');

    Route::post('/{employee}/personal-email', [EmployeeController::class, 'setPersonalEmail'])
        ->name('employees.personal_email.set');

    Route::get('/{employee}/date-of-joining', [EmployeeController::class, 'getDateOfJoining'])
        ->name('employees.date_of_joining.get');

    Route::put('/update-info', [EmployeeController::class, 'updateInfo'])
        ->name('employees.update');

    Route::get('/payments/{employee}', [EmployeeController::class, 'getPayments'])
        ->name('employees.payments.get');


    //roles
    Route::put('roles/update/{employee}/{role}/{newRole}', [EmployeeController::class, 'updateRole'])
        ->name('employee.role.update');

    Route::delete('roles/delete/{employee}/{role}', [EmployeeController::class, 'deleteRole'])
        ->name('employee.role.update');

    Route::get('roles/{employee}', [EmployeeController::class, 'getRoles'])
        ->name('employeey.roles.get');

    Route::post('roles/set/{employee}/{role}', [EmployeeController::class, 'setRole'])
        ->name('employee.role.set');


    Route::get('/', [EmployeeController::class, 'index'])
        ->name('employee.index');

    Route::get('/show/{employee}', [EmployeeController::class, 'show'])
        ->name('employee.show');

    Route::get('/delete/{employee}', [EmployeeController::class, 'destroy'])
        ->name('employee.delete');

});


// Create a new Employee with hardcoded data (This is just a temporary route for testing or seeding purposes)
Route::post('create', function () {
    Employee::create([
        'first_name' => 'Mo',
        'last_name' => 'Mo',
        'email' => 'employee@j.com',
        'password' => 'password',
        'address' => 'address',
        'date_of_birth' => '2001-06-06',
        'gender_id' => '1',
        'image' => null,
        'salary' => '34',
        'personal_email' => 'hello@persona.com',
        'date_of_joining' => now(),
        'role_id' => '2',
        'money' => '89'
    ]);
});
