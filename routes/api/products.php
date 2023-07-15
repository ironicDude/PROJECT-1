<?php

use App\Http\Controllers\DrugInteractionController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Resources\CustomResponse;
use App\Models\Interaction;

Route::get('/', [ProductController::class, 'index'])
                ->name('products.get');

Route::get('{string}', [ProductController::class, 'search'])
                ->name('product.search');

Route::get('product/{id}', [ProductController::class, 'show'])
                ->name('product.show');

Route::get('labellers/{string}', [ProductController::class, 'searchLabellers'])
                ->name('product.labellers.search');

Route::get('/routes', [ProductController::class, 'getRoutes'])
                ->name('product.routes.get');

