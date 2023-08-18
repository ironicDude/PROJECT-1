<?php

use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\AssignOrdersController;
use App\Http\Controllers\Order\InStoreOrderController;
use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\VacancyController;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth', 'forceLogout'])->group(function () {

    Route::get('/', [ApplicationController::class, 'index'])
        ->name('applications.index');

    Route::get('{application}', [ApplicationController::class, 'show'])
        ->name('applications.show');

    Route::put('accept/{application}', [ApplicationController::class, 'accept'])
        ->name('applications.store');

    Route::put('reject/{application}', [ApplicationController::class, 'reject'])
        ->name('applications.delete');

});

