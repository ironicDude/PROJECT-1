<?php

use App\Http\Controllers\Drug\DrugController;
use App\Http\Controllers\Drug\DrugInteractionController;
use Illuminate\Support\Facades\Route;

// Route to search drugs by keyword or criteria
Route::get('interaction', [DrugController::class, 'search'])
    ->name('drug.search');

// Route to check if two drugs have any interactions by their IDs
Route::get('check/{id}/{interactingId}', [DrugInteractionController::class, 'checkInteraction'])
    ->name('drug.interaction.check');

// Route to get a list of drug interactions between two specific drugs by their names or identifiers
Route::get('get/{firstDrug}/{secondDrug}', [DrugInteractionController::class, 'index'])
    ->name('drug.interaction.products.get');
