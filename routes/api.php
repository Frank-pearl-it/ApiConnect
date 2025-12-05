<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AutoTaskCompanyController,
    AutoTaskInvoiceController,
    AuthController,
    RoleController,
    Pax8Controller,
    Pax8WebhookController,

};

/*
|--------------------------------------------------------------------------
| 🔒 SYSTEM INTEGRATION ROUTES
|--------------------------------------------------------------------------
|THESE ROUTES ARE USED BY EXTERNAL SYSTEMS AND INTERNAL METHODS
|THE MIDDLEWARE IS SET UP SO THAT ONLY ORIGINS IN THE CORS ALLOWLIST CAN ACCESS THESE.
|THE FRONTEND URL IS BLOCKED BY THE MIDDLEWARE.
*/
Route::prefix('integrations')
    ->middleware(['restrict.integrations'])
    ->group(function () {
        // COMPANIES
        Route::get('autotask/companies/{id}', [AutoTaskCompanyController::class, 'getAutoTaskCompanyById'])
            ->name('autotask.get');
        Route::post('snelstart/companies/{id}', [AutoTaskCompanyController::class, 'addSnelstartCompany'])
            ->name('snelstart.add');
        Route::put('snelstart/companies/{id}', [AutoTaskCompanyController::class, 'updateSnelstartCompany'])
            ->name('snelstart.update');
        Route::delete('snelstart/companies', [AutoTaskCompanyController::class, 'deleteSnelstartCompany'])
            ->name('snelstart.delete');

        // INVOICES
        Route::post('snelstart/invoices/{id}', [AutoTaskInvoiceController::class, 'addSnelstartInvoice'])
            ->name('snelstart.add.invoice');
        Route::put('snelstart/invoices/{id}', [AutoTaskInvoiceController::class, 'updateSnelstartInvoice'])
            ->name('snelstart.update.invoice');
        Route::delete('snelstart/invoices', [AutoTaskInvoiceController::class, 'deleteSnelstartInvoice'])
            ->name('snelstart.delete.invoice');

        // PAX8 PRODUCTS
        Route::post('/pax8/webhook', [Pax8WebhookController::class, 'handle']);

    });

/*
|--------------------------------------------------------------------------
| 🌐 FRONTEND AUTH & LOGIN
|--------------------------------------------------------------------------
| Routes used by your Quasar SPA or client app.
| These are public endpoints used for login, password reset, etc.
*/

Route::prefix('auth')->group(function () {
    Route::post('client/changePsw', [AuthController::class, 'changePsw']);
    Route::post('client/initLogin', [AuthController::class, 'initLogin']);
    Route::post('client/finishLogin', [AuthController::class, 'finishLogin']);

    Route::get('employee/microsoftUrl', [AuthController::class, 'getMicrosoftUrl']);
    Route::get('employee/microsoftCallback', [AuthController::class, 'microsoftCallback']);
});

Route::post('sendResetLink', [AuthController::class, 'sendResetLink']);

/*
|--------------------------------------------------------------------------
| 🧑‍💻 SANCTUM AUTHENTICATED ROUTES
|--------------------------------------------------------------------------
| Everything in here requires a logged-in user (frontend).
| We'll also enforce dynamic permissions later using our new middleware.
| These routes are protected by a middleware which confirms that the user is logged in and hasnt tampered with their permissions
*/

Route::middleware(['auth:sanctum', 'frontend.integrity'])
    ->group(function () {
        Route::post('resetPassword', [AuthController::class, 'resetPassword']);

        // ROLES & PERMISSIONS
        Route::get('roles/permissions', [RoleController::class, 'permissions']);
        Route::apiResource('roles', RoleController::class);
        Route::prefix('pax8')->middleware('role:viewProducts')->group(function () {
            Route::get('products', [Pax8Controller::class, 'listProducts']);
            Route::get('products/{productId}', [Pax8Controller::class, 'getProduct']);
            Route::get('products/{productId}/pricing', [Pax8Controller::class, 'getProductPricing']);
            Route::get('products/{productId}/dependencies', [Pax8Controller::class, 'getProductDependencies']);
        });
    });
