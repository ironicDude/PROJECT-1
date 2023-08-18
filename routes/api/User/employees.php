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

    Route::get('/employees/{employee}/salary', [EmployeeController::class, 'getSalary'])
        ->name('employees.salary.get');

    Route::post('/employees/{employee}/salary', [EmployeeController::class, 'setSalary'])
        ->name('employees.salary.set');

    Route::get('/employees/{employee}/roles', [EmployeeController::class, 'getRoles'])
        ->name('employees.role.get');

    Route::post('/employees/{employee}/role', [EmployeeController::class, 'setRole'])
        ->name('employees.role.set');

    Route::get('/employees/{employee}/personal-email', [EmployeeController::class, 'getPersonalEmail'])
        ->name('employees.personal_email.get');

    Route::post('/employees/{employee}/personal-email', [EmployeeController::class, 'setPersonalEmail'])
        ->name('employees.personal_email.set');

    Route::get('/employees/{employee}/date-of-joining', [EmployeeController::class, 'getDateOfJoining'])
        ->name('employees.date_of_joining.get');

    Route::put('/employees/update-info', [EmployeeController::class, 'updateInfo'])
        ->name('employees.update');

    Route::get('/employees/payments/{employee}', [EmployeeController::class, 'getPayments'])
        ->name('employees.payments.get');

    Route::put('/employees/{employee}/roles/{role}/update', [EmployeeController::class, 'updateRole'])
        ->name('employee.role.update');

    Route::delete('/employees/{employee}/roles/{role}/delete', [EmployeeController::class, 'updateRole'])
        ->name('employee.role.update');

    Route::get('/employees', [EmployeeController::class, 'index'])
        ->name('employee.index');

    Route::get('/employees/{employee}/show', [EmployeeController::class, 'show'])
        ->name('employee.show');

    Route::get('/employees/{employee}/delete', [EmployeeController::class, 'destroy'])
        ->name('employee.delete');

    //Customer
    Route::get('customers/search/names', [CustomerController::class, 'searchNames'])
        ->name('customers.name.search');

});

Route::get('/restore', [UserController::class, 'restore'])
    ->name('user.restore')
    ->middleware(['signed', 'throttle:6,1']);


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
