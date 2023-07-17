<?php

use App\Http\Controllers\DrugInteractionController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Resources\CustomResponse;
use App\Models\Interaction;

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
