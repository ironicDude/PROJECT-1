<?php

use App\Http\Controllers\Order\InStoreOrderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'forceLogout'])->group(function () {

Route::post('/create', [InStoreOrderController::class, 'addNewOrder'])
                ->name('in-store-order.product.store');

Route::get('/index-desc', [InStoreOrderController::class, 'indexDescendingly'])
                ->name('in-store-order.index.desc');

                Route::middleware(['checkForPaidOrders'])->group(function () {

Route::post('/store/{inStoreOrder}/{purchasedProduct}', [InStoreOrderController::class, 'store'])
                ->name('in-store-order.product.store');

Route::delete('/remove/{inStoreOrder}/{purchasedProduct}', [InStoreOrderController::class, 'remove'])
                ->name('in-store-order.product.remove');

Route::put('/update/{inStoreOrder}/{purchasedProduct}', [InStoreOrderController::class, 'updateQuantity'])
                ->name('in-store-order.product.quantity.update');

Route::post('/checkout/{inStoreOrder}', [InStoreOrderController::class, 'checkout'])
                ->name('in-store-order.checkout');

Route::delete('/clear/{inStoreOrder}', [InStoreOrderController::class, 'clear'])
                ->name('in-store-order.clear');

Route::post('/prescriptions/store/{inStoreOrder}', [InStoreOrderController::class, 'storePrescriptions'])
                ->name('in-store-order.prescriptions.store');

Route::delete('/prescriptions/delete/{inStoreOrder}', [InStoreOrderController::class, 'deletePrescriptions'])
                ->name('in-store-order.prescriptions.delete');

Route::get('/prescriptions/show/{inStoreOrder}', [InStoreOrderController::class, 'checkForPrescriptions'])
                ->name('in-store-order.prescriptions.show');

Route::delete('/delete/{inStoreOrder}', [InStoreOrderController::class, 'delete'])
                ->name('in-store-order.delete');
            });

});
