<?php

use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Resources\CustomResponse;
use App\Models\Interaction;

Route::get('/', [ProductController::class, 'index'])
                ->name('products.get');
Route::get('interactions', function(){
    return response()->json(Interaction::where('id', 668285)->first(), 200);
});
