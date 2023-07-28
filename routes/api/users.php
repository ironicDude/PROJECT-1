<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\User\UserController;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Activate or deactivate a user (employee) based on user_id
Route::post('user/{user_id}', [UserController::class, 'activateOrDeactivate'])
    ->middleware('auth')
    ->name('user.deactivateOrActivate');

// Get the address of the authenticated user
Route::get('user/address/show', [UserController::class, 'getAddress'])
    ->middleware('auth')
    ->name('user.address.show');

// Get all orders for a specific customer
Route::get('customers/{customer}/orders', [OrderController::class, 'index'])
    ->middleware('auth')
    ->name('customer.orders.get');

// Show details of a specific order for a customer
Route::get('customers/orders/{order}', [OrderController::class, 'show'])
    ->middleware('auth')
    ->name('customer.order.show');

// Get prescriptions for a specific order of a customer
Route::get('customers/orders/{order}/prescriptions', [OrderController::class, 'getPrescriptions'])
    ->middleware('auth')
    ->name('customer.order.show');

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
