<?php

use App\Http\Controllers\AssignOrdersController;
use App\Http\Controllers\Order\InStoreOrderController;
use App\Http\Controllers\Order\OrderController;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth', 'forceLogout'])->group(function () {

    Route::get('/customers/{customer}', [OrderController::class, 'getCustomerOrders'])
        ->name('customer.orders.get');

    Route::get('{order}', [OrderController::class, 'show'])
        ->name('order.show');

    Route::get('{order}/prescriptions', [OrderController::class, 'getPrescriptions'])
        ->name('order.prescriptions.get');

    Route::get('/', [OrderController::class, 'index'])
        ->name('orders.get');

    Route::get('/delivery_boys', [AssignOrdersController::class, 'delivery_boys'])
        ->name('delivery.boys');
        
    Route::post('/assign', [AssignOrdersController::class, 'assign'])
        ->name('order.assign');

});
