<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DrugInteractionController;

Route::get('interaction/{string}', [DrugInteractionController::class, 'search'])
                ->name('interaction.search');

Route::get('{id}/{interactingId}', [DrugInteractionController::class, 'checkInteraction'])
                ->name('interaction.check');

Route::get('{firstDrug}/drugs/{secondDrug}', [DrugInteractionController::class, 'index'])
                ->name('interaction.products.get');
