<?php

use App\Http\Controllers\Dashboard\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'forceLogout'])->group(function () {
Route::get('/revenue', [DashboardController::class, 'getRevenue'])
    ->name('revenue.get');

Route::get('/count-newbies', [DashboardController::class, 'countNewCustomers'])
    ->name('newbies.count');

Route::get('/best-selling', [DashboardController::class, 'getBestSellingProducts'])
    ->name('best.selling.products.get');

});
