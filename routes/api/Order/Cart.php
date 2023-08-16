<?php

use App\Http\Controllers\Product\CartController;
use App\Http\Controllers\Product\AllergyController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Product\PurchasedProductController;
use App\Http\Controllers\Product\RatingController;
use App\Http\Controllers\Product\WishlistController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Resources\CustomResponse;
use App\Models\Interaction;
use App\Models\PurchasedProduct;


Route::middleware(['auth', 'forceLogout'])->group(function() {

// Add a product to the cart for the authenticated user
Route::post('/store/{purchasedProduct}', [CartController::class, 'store'])
                ->name('cart.product.store');

// Remove a product from the cart for the authenticated user
Route::delete('/{cart}/remove/{purchasedProduct}', [CartController::class, 'remove'])
                ->name('cart.remove');

// Update the quantity of a product in the cart for the authenticated user
Route::put('/{cart}/quantity/update/{purchasedProduct}', [CartController::class, 'updateQuantity'])
                ->name('cart.quantity.update');

// Show the contents of the cart for the authenticated user
Route::get('/{cart}/show', [CartController::class, 'show'])
                ->name('cart.show');

// Store address information for the cart checkout for the authenticated user
Route::put('/{cart}/address/store', [CartController::class, 'storeAddress'])
                ->name('cart.address.store');

// Get the stored address information for the cart for the authenticated user
Route::get('/{cart}/address/show', [CartController::class, 'getAddress'])
                ->name('cart.address.show');

// Proceed to checkout for the cart for the authenticated user
Route::post('/{cart}/checkout', [CartController::class, 'checkout'])
                ->name('cart.checkout');

// Get the quantity of a specific product in the cart for the authenticated user
Route::get('/{cart}/quantity/show', [CartController::class, 'getQuantity'])
                ->name('cart.address.show');

// Get the total price of the cart for the authenticated user
Route::get('/{cart}/total/show', [CartController::class, 'getTotal'])
                ->name('cart.total.show');

// Clear the cart for the authenticated user
Route::delete('/{cart}/clear', [CartController::class, 'clear'])
                ->name('cart.clear');

// Store prescriptions for the cart for the authenticated user
Route::post('/{cart}/prescriptions/store', [CartController::class, 'storePrescriptions'])
                ->name('cart.prescriptions.store');

Route::delete('/{cart}/prescriptions/delete', [CartController::class, 'deletePrescriptions'])
                ->name('cart.prescriptions.delete');

// Check if prescriptions are uploaded for the cart for the authenticated user
Route::get('/{cart}/prescriptions/show', [CartController::class, 'checkForPrescriptions'])
                ->name('cart.prescriptions.show');
});

