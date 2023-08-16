<?php

use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

// Route::middleware(['auth', 'forceLogout'])->group(function () {
Route::get('/', [PaymentController::class, 'index'])
    ->name('payments.index');

Route::post('/create/{employee}', [PaymentController::class, 'create'])
    ->name('payments.create');

Route::get('/show/{payment}', [PaymentController::class, 'show'])
    ->name('payments.show');

Route::put('/update/{payment}', [PaymentController::class, 'update'])
    ->name('payments.update');

// });
