<?php

use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\AssignOrdersController;
use App\Http\Controllers\Order\InStoreOrderController;
use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\VacancyController;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth', 'forceLogout'])->group(function () {

    Route::get('/', [VacancyController::class, 'index'])
        ->name('vacancies.index');

    Route::get('{vacancy}', [VacancyController::class, 'show'])
        ->name('vacancies.show');

    Route::post('store', [VacancyController::class, 'store'])
        ->name('vacancies.store');

    Route::delete('delete/{vacancy}', [VacancyController::class, 'destroy'])
        ->name('vacancies.delete');

    Route::put('update/{vacancy}', [VacancyController::class, 'update'])
        ->name('vacancies.update');

});

