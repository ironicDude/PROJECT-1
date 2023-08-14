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

//order
Route::post('/create/purchase', [ProductController::class, 'purchase'])
    ->name('product.purchase');

Route::get('/get/purchase/{id}', [ProductController::class, 'getPurchase'])
    ->name('product.get.purchase');

Route::post('/prices', [ProductController::class, 'withPrices'])
    ->name('product.prices');


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

Route::middleware(['auth', 'forceLogout'])->group(function() {

// allergy
// Toggle allergy status of a product for the authenticated user
Route::post('allergy/toggle/{product}', [AllergyController::class, 'toggleAllergy'])
                ->name('product.allergy.toggle');

// Check if a product is marked as an allergy for the authenticated user
Route::get('allergy/check/{product}', [AllergyController::class, 'checkAllergy'])
                ->name('product.allergy.check');

// Get all products marked as allergies for the authenticated user
Route::get('allergies/index', [AllergyController::class, 'index'])
                ->name('product.allergy.get');

// stock levels feature
Route::get('/purchasedProducts', [PurchasedProductController::class, 'index'])
                ->name('products.purchasedProducts.get');

Route::get('/purchasedProducts/{purchasedProduct}/price/get', [PurchasedProductController::class, 'getPrice'])
                ->name('products.purchasedProducts.price.get');

Route::get('/purchasedProducts/{purchasedProduct}/minimumStockLevel/get', [PurchasedProductController::class, 'getMinimumStockLevel'])
                ->name('products.purchasedProducts.minimumStockLevel.get');

Route::get('/purchasedProducts/{purchasedProduct}/orderLimit/get', [PurchasedProductController::class, 'getOrderLimit'])
                ->name('products.purchasedProducts.orderLimit.get');

Route::post('/purchasedProducts/{purchasedProduct}/price/set', [PurchasedProductController::class, 'setPrice'])
                ->name('products.purchasedProducts.price.set');

Route::post('/purchasedProducts/{purchasedProduct}/minimumStockLevel/set', [PurchasedProductController::class, 'setMinimumStockLevel'])
                ->name('products.purchasedProducts.minimumStockLevel.set');

Route::post('/purchasedProducts/{purchasedProduct}/orderLimit/set', [PurchasedProductController::class, 'setOrderLimit'])
                ->name('products.purchasedProducts.orderLimit.set');

//rating
Route::post('/{product}/rate/', [RatingController::class, 'rate'])
                ->name('product.rate');

Route::get('/{product}/rating/get', [RatingController::class, 'getRating'])
                ->name('product.rate.get');

Route::get('user/{product}/rating/get', [RatingController::class, 'getUserRatingToProduct'])
                ->name('user.product.rating.get');
//wishlist
Route::post('/wishlist/toggle/{product}/', [WishlistController::class, 'toggleWishlistProduct'])
                ->name('product.wishlist.toggle');

Route::get('/wishlist/check/{product}', [WishlistController::class, 'checkIfWishlisted'])
                ->name('product.wishlist.check');
});

