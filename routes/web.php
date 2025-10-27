<?php

use Illuminate\Support\Facades\Route;

 
Route::get('/{any}', function () {
    return redirect(config('app.frontend_url', 'http://localhost:9000'));
})->where('any', '.*');