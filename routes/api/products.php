<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\Product\AllergyController;
use App\Http\Controllers\Product\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Resources\CustomResponse;
use App\Models\Interaction;

// search
Route::get('{product}', [ProductController::class, 'show'])
                ->name('product.show');

Route::get('labellers/{string}', [ProductController::class, 'searchLabellers'])
                ->name('product.labellers.search');

Route::get('/routes/{string}', [ProductController::class, 'searchRoutes'])
                ->name('product.routes.search');

Route::get('/dosage_forms/{string}', [ProductController::class, 'searchDosageForms'])
                ->name('product.dosage_forms.search');

Route::get('/categories/{string}', [ProductController::class, 'searchCategories'])
                ->name('product.categories.search');

Route::get('/', [ProductController::class, 'index'])
                ->name('products.get');

Route::get('/names/{string}', [ProductController::class, 'searchNames'])
                ->name('product.name.search');

// allergy
Route::post('allergy/toggle/{product}', [AllergyController::class, 'toggleAllergy'])
                ->name('product.allergy.toggle')
                ->middleware('auth');

Route::get('allergy/check/{product}', [AllergyController::class, 'checkAllergy'])
                ->name('product.allergy.check')
                ->middleware('auth');

Route::get('allergies/index', [AllergyController::class, 'index'])
                ->name('product.allergy.get')
                ->middleware('auth');


// cart


Route::post('cart/store/{purchasedProduct}', [CartController::class, 'store'])
                ->name('cart.product.store')
                ->middleware('auth');

Route::delete('cart/{cart}/remove/{purchasedProduct}', [CartController::class, 'remove'])
                ->name('cart.remove')
                ->middleware('auth');

Route::put('cart/{cart}/quantity/update/{purchasedProduct}', [CartController::class, 'updateQuantity'])
                ->name('cart.quantity.update')
                ->middleware('auth');

Route::get('cart/{cart}/show', [CartController::class, 'show'])
                ->name('cart.show')
                ->middleware('auth');

Route::put('cart/{cart}/address/store', [CartController::class, 'storeAddress'])
                ->name('cart.address.store')
                ->middleware('auth');

Route::get('cart/{cart}/address/show', [CartController::class, 'getAddress'])
                ->name('cart.address.show')
                ->middleware('auth');

Route::post('cart/{cart}/checkout', [CartController::class, 'checkout'])
                ->name('cart.checkout')
                ->middleware('auth');

Route::get('cart/{cart}/quantity/show', [CartController::class, 'getQuantity'])
                ->name('cart.address.show')
                ->middleware('auth');

Route::get('cart/{cart}/total/show', [CartController::class, 'getTotal'])
                ->name('cart.total.show')
                ->middleware('auth');

Route::delete('cart/{cart}/clear', [CartController::class, 'clear'])
                ->name('cart.clear')
                ->middleware('auth');

Route::post('/cart/{cart}/prescriptions/store', [CartController::class, 'storePrescriptions'])
                ->name('cart.prescriptions.store')
                ->middleware('auth');

Route::get('/cart/{cart}/prescriptions/show', [CartController::class, 'checkPrescriptionsUpload'])
                ->name('cart.prescriptions.show')
                ->middleware('auth');
