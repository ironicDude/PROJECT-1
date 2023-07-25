<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\User\UserController;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('user/{user_id}', [UserController::class, 'activateOrDeactivate'])
    ->middleware('auth')
    ->name('user.deactivateOrActivate');

Route::get('user/address/show', [UserController::class, 'getAddress'])
    ->middleware('auth')
    ->name('user.address.show');

Route::get('customers/customer/{customer}/orders', [OrderController::class, 'index'])
    ->middleware('auth')
    ->name('customer.orders.get');

Route::get('customers/customer/order/{order}', [OrderController::class, 'show'])
    ->middleware('auth')
    ->name('customer.order.show');


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
