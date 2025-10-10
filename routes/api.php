<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\AutoTaskCompanyController;
use App\Http\Controllers\AutoTaskInvoiceController;

// COMPANIES
// get a company from snelstart
Route::get('/autotask/companies/{id}', [AutoTaskCompanyController::class, 'getAutoTaskCompanyById'])
    ->name('autotask.get');

// Add a company to SnelStart (from Autotask)
Route::post('/snelstart/companies/{id}', [AutoTaskCompanyController::class, 'addSnelstartCompany'])
    ->name('snelstart.add');

// Update a company in SnelStart (by Autotask ID)
Route::put('/snelstart/companies/{id}', [AutoTaskCompanyController::class, 'updateSnelstartCompany'])
    ->name('snelstart.update');

// Delete a company from SnelStart (by BTW number passed in request body)
Route::delete('/snelstart/companies', [AutoTaskCompanyController::class, 'deleteSnelstartCompany'])
    ->name('snelstart.delete');

    
// INVOICES

// Add an invoice to SnelStart (from Autotask)
Route::post('/snelstart/invoices/{id}', [AutoTaskInvoiceController::class, 'addSnelstartInvoice'])
    ->name('snelstart.add.invoice');

// Update an invoice in SnelStart (by Autotask ID)
Route::put('/snelstart/invoices/{id}', [AutoTaskInvoiceController::class, 'updateSnelstartInvoice'])
    ->name('snelstart.update.invoice');

// Delete an invoice from SnelStart (by Autotask ID passed in request body)
Route::delete('/snelstart/invoices', [AutoTaskInvoiceController::class, 'deleteSnelstartInvoice'])
    ->name('snelstart.delete.invoice');