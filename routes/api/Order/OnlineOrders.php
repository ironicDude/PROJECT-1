<?php

use App\Http\Controllers\Order\OnlineOrderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'forceLogout', 'checkForModifiableOnlineOrder'])->group(function () {

Route::post('/store/{onlineOrder}/{purchasedProduct}', [OnlineOrderController::class, 'store'])
                ->name('in-store-order.product.store');

Route::delete('/remove/{onlineOrder}/{purchasedProduct}', [OnlineOrderController::class, 'remove'])
                ->name('in-store-order.product.remove');

Route::put('/update/{onlineOrder}/{purchasedProduct}', [OnlineOrderController::class, 'updateQuantity'])
                ->name('in-store-order.product.quantity.update');

Route::post('/checkout/{onlineOrder}', [OnlineOrderController::class, 'checkout'])
                ->name('in-store-order.checkout');

Route::post('/prescriptions/store/{onlineOrder}', [OnlineOrderController::class, 'storePrescriptions'])
                ->name('in-store-order.prescriptions.store');

Route::delete('/prescriptions/delete/{onlineOrder}', [OnlineOrderController::class, 'deletePrescriptions'])
                ->name('in-store-order.prescriptions.delete');

Route::get('/prescriptions/show/{onlineOrder}', [OnlineOrderController::class, 'checkForPrescriptions'])
                ->name('in-store-order.prescriptions.show');

Route::delete('/delete/{onlineOrder}', [OnlineOrderController::class, 'delete'])
                ->name('in-store-order.delete');

Route::put('/shipping-address/store/{onlineOrder}', [OnlineOrderController::class, 'storeShippingAddress'])
                ->name('cart.address.store');

Route::get('/shipping-address/show/{onlineOrder}', [OnlineOrderController::class, 'getShippingAddress'])
                ->name('cart.address.show');
});
