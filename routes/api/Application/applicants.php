<?php

use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\AssignOrdersController;
use App\Http\Controllers\Order\InStoreOrderController;
use App\Http\Controllers\Order\OrderController;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth', 'forceLogout'])->group(function () {

    Route::get('/', [ApplicantController::class, 'index'])
        ->name('applicants.index');

    Route::get('{applicant}', [ApplicantController::class, 'show'])
        ->name('applicants.show');

    Route::post('/resume/{applicant}', [ApplicantController::class, 'showResume'])
        ->name('applicants.resume.show');

});

Route::post('/store', [ApplicantController::class, 'store'])
        ->name('applicants.store');

Route::post('/resume/store', [ApplicantController::class, 'storeResume'])
        ->name('applicants.resume.store');
