<?php


use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'forceLogout'])->group(function () {

    Route::post('store', [RoleController::class, 'store'])
        ->name('roles.store');

    Route::get('/', [RoleController::class, 'index'])
        ->name('roles.index');

    Route::delete('delete/{role}', [RoleController::class, 'delete'])
        ->name('roles.delete');

    Route::put('update/{role}', [RoleController::class, 'update'])
        ->name('roles.update');

});
