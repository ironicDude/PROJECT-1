<?php

use App\Http\Controllers\Order\OrderController;
use Illuminate\Support\Facades\Route;


Route::get('/delivery_boys' , [\App\Http\Controllers\AssignOrdersController::class, 'delivery_boys'])
    ->name('delivery.boy.available');

Route::get('/getOrders' , [\App\Http\Controllers\AssignOrdersController::class, 'getOrders'])
    ->name('get.all.orders');
    
Route::post('/assign' , [\App\Http\Controllers\AssignOrdersController::class, 'assign'])
    ->name('assign.delivery.boy');


Route::middleware(['auth', 'forceLogout'])->group(function () {

    Route::get('customer/{customer}', [OrderController::class, 'getCustomerOrders'])
    ->name('customer.orders.get');

    Route::get('{order}', [OrderController::class, 'show'])
    ->name('order.show');

    Route::get('{order}/prescriptions', [OrderController::class, 'getPrescriptions'])
    ->name('order.prescriptions.get');

    Route::get('/', [OrderController::class, 'index'])
    ->name('orders.get');
});


