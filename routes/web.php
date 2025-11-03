<?php

use Illuminate\Support\Facades\Route;

// Vue SPA - catch all routes and serve the app
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
