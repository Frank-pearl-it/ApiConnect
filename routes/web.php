<?php

use Illuminate\Support\Facades\Route;

 
Route::get('/{any}', function () {
    $frontendUrl = rtrim(config('app.frontend_url', 'http://localhost:9000'), '/');
    return redirect($frontendUrl . '/#/');

})->where('any', '.*');