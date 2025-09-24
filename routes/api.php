<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
// Add a company to SnelStart (from Autotask)
Route::post('/snelstart/companies/{id}', [ApiController::class, 'addSnelstartCompany'])
    ->name('snelstart.add');

// Update a company in SnelStart (by Autotask ID)
Route::put('/snelstart/companies/{id}', [ApiController::class, 'updateSnelstartCompany'])
    ->name('snelstart.update');

// Delete a company from SnelStart (by BTW number passed in request body)
Route::delete('/snelstart/companies', [ApiController::class, 'deleteSnelstartCompany'])
    ->name('snelstart.delete');