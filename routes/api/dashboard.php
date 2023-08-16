<?php

use App\Http\Controllers\Dashboard\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'forceLogout'])->group(function () {
Route::get('/revenue', [DashboardController::class, 'getRevenue'])
    ->name('revenue.get');

Route::get('/newbies-count', [DashboardController::class, 'countNewCustomers'])
    ->name('newbies.count');

Route::get('/best-selling', [DashboardController::class, 'getBestSellingProducts'])
    ->name('best.selling.products.get');

Route::get('/most-profitable', [DashboardController::class, 'getMostProfitableProducts'])
    ->name('most.profitable.products.get');

Route::get('/orders-count', [DashboardController::class, 'countOrders'])
    ->name('orders.count');

Route::get('/customers-chart', [DashboardController::class, 'chartNewAndLostCustomers'])
    ->name('customers.chart');

Route::get('/orders-chart', [DashboardController::class, 'chartOrders'])
    ->name('orders.chart');

Route::get('/revenue-chart', [DashboardController::class, 'chartRevenue'])
    ->name('revenue.chart');

Route::get('/profit-chart', [DashboardController::class, 'chartProfit'])
    ->name('profit.chart');

});
