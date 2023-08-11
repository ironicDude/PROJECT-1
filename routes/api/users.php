<?php

use App\Http\Controllers\User\EmployeeController;
use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Product\WishlistController;
use App\Http\Controllers\User\UserController;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::middleware(['auth', 'forceLogout'])->group(function () {

    Route::get('/wishlist', [WishlistController::class, 'getWishlist'])
                ->name('user.wishlist.get');

    // Activate or deactivate a user (employee) based on user_id
    Route::post('user/{user_id}', [UserController::class, 'activateOrDeactivate'])
        ->name('user.deactivateOrActivate');

    // User address routes
    Route::get('/{user}/address', [UserController::class, 'getAddress'])
        ->name('user.address.get');

    Route::post('address-set', [UserController::class, 'setAddress'])
        ->name('user.address.set');

    // User image routes
    Route::get('/{user}/image', [UserController::class, 'getImage'])
        ->name('user.image.get');

    Route::post('/image', [UserController::class, 'setImage'])
        ->name('user.image.set');

    // User first name routes
    Route::get('/{user}/first-name', [UserController::class, 'getFirstName'])
        ->name('user.first_name.get');

    Route::post('first-name', [UserController::class, 'setFirstName'])
        ->name('user.first_name.set');

    // User last name routes
    Route::get('/{user}/last-name', [UserController::class, 'getLastName'])
        ->name('user.last_name.get');

    Route::post('last-name', [UserController::class, 'setLastName'])
        ->name('user.last_name.set');

    // User mobile routes
    Route::get('/{user}/mobile', [UserController::class, 'getMobile'])
        ->name('user.mobile.get');

    Route::post('mobile', [UserController::class, 'setMobile'])
        ->name('user.mobile.set');

    // User date of birth routes
    Route::get('/{user}/date-of-birth', [UserController::class, 'getDateOfBirth'])
        ->name('user.date_of_birth.get');

    Route::post('date-of-birth', [UserController::class, 'setDateOfBirth'])
        ->name('user.date_of_birth.set');

    // User gender routes
    Route::get('/{user}/gender', [UserController::class, 'getGender'])
        ->name('user.gender.get');

    Route::post('gender', [UserController::class, 'setGender'])
        ->name('user.gender.set');

    // User account status route
    Route::get('/{user}/account-status', [UserController::class, 'getAccountStatus'])
        ->name('user.account_status.get');

    // User type route
    Route::get('/{user}/type', [UserController::class, 'getType'])
        ->name('user.type.get');

    Route::put('/update-info', [UserController::class, 'updateInfo'])
        ->name('user.update');

    Route::delete('/delete', [UserController::class, 'deleteSoftly'])
        ->name('user.delete');

    //EMPLOYEES
    // Employee salary routes
    Route::get('/employees/{employee}/salary', [EmployeeController::class, 'getSalary'])
        ->name('employees.salary.get');

    Route::post('/employees/{employee}/salary', [EmployeeController::class, 'setSalary'])
        ->name('employees.salary.set');

    //Employe role routes
    Route::get('/employees/{employee}/role', [EmployeeController::class, 'getRole'])
        ->name('employees.role.get');

    Route::post('/employees/{employee}/role', [EmployeeController::class, 'setRole'])
        ->name('employees.role.set');

    //Employee personal email routes
    Route::get('/employees/{employee}/personal-email', [EmployeeController::class, 'getPersonalEmail'])
        ->name('employees.personal_email.get');

    Route::post('/employees/{employee}/personal-email', [EmployeeController::class, 'setPersonalEmail'])
        ->name('employees.personal_email.set');

    //Employee date of joining route
    Route::get('/employees/{employee}/date-of-joining', [EmployeeController::class, 'getDateOfJoining'])
        ->name('employees.date_of_joining.get');

    Route::put('/employees/update-info', [EmployeeController::class, 'updateInfo'])
        ->name('employees.update');


});

Route::get('/restore', [UserController::class, 'restore'])
    ->name('user.restore')
    ->middleware('signed');


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
