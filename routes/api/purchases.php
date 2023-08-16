<?php

use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Purchase\PurchaseController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'forceLogout'])->group(function () {
Route::get('/', [PurchaseController::class, 'index'])
    ->name('purchases.index');

Route::get('/show/{purchase}', [PurchaseController::class, 'show'])
    ->name('purchases.show');

});
