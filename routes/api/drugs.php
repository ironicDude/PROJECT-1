<?php

use App\Http\Controllers\Drug\DrugController;
use App\Http\Controllers\Drug\DrugInteractionController;
use Illuminate\Support\Facades\Route;

Route::get('interaction/{string}', [DrugInteractionController::class, 'search'])
                ->name('drug.search');

Route::get('{id}/{interactingId}', [DrugInteractionController::class, 'checkInteraction'])
                ->name('drug.interaction.check');

Route::get('{firstDrug}/drugs/{secondDrug}', [DrugController::class, 'index'])
                ->name('drug.interaction.products.get');
