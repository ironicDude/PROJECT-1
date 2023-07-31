<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\Product\AllergyController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Product\PurchasedProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Resources\CustomResponse;
use App\Models\Interaction;
use App\Models\PurchasedProduct;

// search
// Show a specific product's information
Route::get('/get/{product}', [ProductController::class, 'show'])
                ->name('product.show');

// Search products by labellers
Route::get('/search/labellers', [ProductController::class, 'searchLabellers'])
                ->name('product.labellers.search');

// Search products by routes
Route::get('/search/routes', [ProductController::class, 'searchRoutes'])
                ->name('product.routes.search');

// Search products by dosage forms
Route::get('/search/dosage_forms', [ProductController::class, 'searchDosageForms'])
                ->name('product.dosage_forms.search');

// Search products by categories
Route::get('/search/categories', [ProductController::class, 'searchCategories'])
                ->name('product.categories.search');

// Get all products
Route::get('/', [ProductController::class, 'index'])
                ->name('products.get');

// Search products by names
Route::get('/search/names', [ProductController::class, 'searchNames'])
                ->name('product.name.search');

// allergy
// Toggle allergy status of a product for the authenticated user
Route::post('allergy/toggle/{product}', [AllergyController::class, 'toggleAllergy'])
                ->name('product.allergy.toggle')
                ->middleware('auth');

// Check if a product is marked as an allergy for the authenticated user
Route::get('allergy/check/{product}', [AllergyController::class, 'checkAllergy'])
                ->name('product.allergy.check')
                ->middleware('auth');

// Get all products marked as allergies for the authenticated user
Route::get('allergies/index', [AllergyController::class, 'index'])
                ->name('product.allergy.get')
                ->middleware('auth');


// cart
// Add a product to the cart for the authenticated user
Route::post('cart/store/{purchasedProduct}', [CartController::class, 'store'])
                ->name('cart.product.store')
                ->middleware('auth');

// Remove a product from the cart for the authenticated user
Route::delete('cart/{cart}/remove/{purchasedProduct}', [CartController::class, 'remove'])
                ->name('cart.remove')
                ->middleware('auth');

// Update the quantity of a product in the cart for the authenticated user
Route::put('cart/{cart}/quantity/update/{purchasedProduct}', [CartController::class, 'updateQuantity'])
                ->name('cart.quantity.update')
                ->middleware('auth');

// Show the contents of the cart for the authenticated user
Route::get('cart/{cart}/show', [CartController::class, 'show'])
                ->name('cart.show')
                ->middleware('auth');

// Store address information for the cart checkout for the authenticated user
Route::put('cart/{cart}/address/store', [CartController::class, 'storeAddress'])
                ->name('cart.address.store')
                ->middleware('auth');

// Get the stored address information for the cart for the authenticated user
Route::get('cart/{cart}/address/show', [CartController::class, 'getAddress'])
                ->name('cart.address.show')
                ->middleware('auth');

// Proceed to checkout for the cart for the authenticated user
Route::post('cart/{cart}/checkout', [CartController::class, 'checkout'])
                ->name('cart.checkout')
                ->middleware('auth');

// Get the quantity of a specific product in the cart for the authenticated user
Route::get('cart/{cart}/quantity/show', [CartController::class, 'getQuantity'])
                ->name('cart.address.show')
                ->middleware('auth');

// Get the total price of the cart for the authenticated user
Route::get('cart/{cart}/total/show', [CartController::class, 'getTotal'])
                ->name('cart.total.show')
                ->middleware('auth');

// Clear the cart for the authenticated user
Route::delete('cart/{cart}/clear', [CartController::class, 'clear'])
                ->name('cart.clear')
                ->middleware('auth');

// Store prescriptions for the cart for the authenticated user
Route::post('/cart/{cart}/prescriptions/store', [CartController::class, 'storePrescriptions'])
                ->name('cart.prescriptions.store')
                ->middleware('auth');

Route::delete('/cart/{cart}/prescriptions/delete', [CartController::class, 'deletePrescriptions'])
                ->name('cart.prescriptions.delete')
                ->middleware('auth');

// Check if prescriptions are uploaded for the cart for the authenticated user
Route::get('/cart/{cart}/prescriptions/show', [CartController::class, 'checkPrescriptionsUpload'])
                ->name('cart.prescriptions.show')
                ->middleware('auth');

// stock levels feature
Route::get('/purchasedProducts', [PurchasedProductController::class, 'index'])
                ->name('products.purchasedProducts.get')
                ->middleware('auth');

Route::get('/purchasedProducts/{purchasedProduct}/price/get', [PurchasedProductController::class, 'getPrice'])
                ->name('products.purchasedProducts.price.get')
                ->middleware('auth');

Route::get('/purchasedProducts/{purchasedProduct}/minimumStockLevel/get', [PurchasedProductController::class, 'getMinimumStockLevel'])
                ->name('products.purchasedProducts.minimumStockLevel.get')
                ->middleware('auth');

Route::get('/purchasedProducts/{purchasedProduct}/orderLimit/get', [PurchasedProductController::class, 'getOrderLimit'])
                ->name('products.purchasedProducts.orderLimit.get')
                ->middleware('auth');

Route::post('/purchasedProducts/{purchasedProduct}/price/set', [PurchasedProductController::class, 'setPrice'])
                ->name('products.purchasedProducts.price.set')
                ->middleware('auth');

Route::post('/purchasedProducts/{purchasedProduct}/minimumStockLevel/set', [PurchasedProductController::class, 'setMinimumStockLevel'])
                ->name('products.purchasedProducts.minimumStockLevel.set')
                ->middleware('auth');

Route::post('/purchasedProducts/{purchasedProduct}/orderLimit/set', [PurchasedProductController::class, 'setOrderLimit'])
                ->name('products.purchasedProducts.orderLimit.set')
                ->middleware('auth');
