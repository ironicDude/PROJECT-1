<?php

use App\Http\Controllers\Drug\DrugController;
use App\Http\Controllers\Drug\DrugInteractionController;
use Illuminate\Support\Facades\Route;

Route::get('interaction', [DrugController::class, 'search'])
                ->name('drug.search');

Route::get('check/{id}/{interactingId}', [DrugInteractionController::class, 'checkInteraction'])
                ->name('drug.interaction.check');

Route::get('get/{firstDrug}/{secondDrug}', [DrugInteractionController::class, 'index'])
                ->name('drug.interaction.products.get');
