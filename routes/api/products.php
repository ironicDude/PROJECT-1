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


// cart
// Add a product to the cart for the authenticated user
Route::post('cart/store/{purchasedProduct}', [CartController::class, 'store'])
                ->name('cart.product.store');

// Remove a product from the cart for the authenticated user
Route::delete('cart/{cart}/remove/{purchasedProduct}', [CartController::class, 'remove'])
                ->name('cart.remove');

// Update the quantity of a product in the cart for the authenticated user
Route::put('cart/{cart}/quantity/update/{purchasedProduct}', [CartController::class, 'updateQuantity'])
                ->name('cart.quantity.update');

// Show the contents of the cart for the authenticated user
Route::get('cart/{cart}/show', [CartController::class, 'show'])
                ->name('cart.show');

// Store address information for the cart checkout for the authenticated user
Route::put('cart/{cart}/address/store', [CartController::class, 'storeAddress'])
                ->name('cart.address.store');

// Get the stored address information for the cart for the authenticated user
Route::get('cart/{cart}/address/show', [CartController::class, 'getAddress'])
                ->name('cart.address.show');

// Proceed to checkout for the cart for the authenticated user
Route::post('cart/{cart}/checkout', [CartController::class, 'checkout'])
                ->name('cart.checkout');

// Get the quantity of a specific product in the cart for the authenticated user
Route::get('cart/{cart}/quantity/show', [CartController::class, 'getQuantity'])
                ->name('cart.address.show');

// Get the total price of the cart for the authenticated user
Route::get('cart/{cart}/total/show', [CartController::class, 'getTotal'])
                ->name('cart.total.show');

// Clear the cart for the authenticated user
Route::delete('cart/{cart}/clear', [CartController::class, 'clear'])
                ->name('cart.clear');

// Store prescriptions for the cart for the authenticated user
Route::post('/cart/{cart}/prescriptions/store', [CartController::class, 'storePrescriptions'])
                ->name('cart.prescriptions.store');

Route::delete('/cart/{cart}/prescriptions/delete', [CartController::class, 'deletePrescriptions'])
                ->name('cart.prescriptions.delete');

// Check if prescriptions are uploaded for the cart for the authenticated user
Route::get('/cart/{cart}/prescriptions/show', [CartController::class, 'checkForPrescriptions'])
                ->name('cart.prescriptions.show');

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

