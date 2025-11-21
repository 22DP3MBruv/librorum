<?php

use Illuminate\Support\Facades\Route;

// CSRF Cookie route for Sanctum
Route::get('/sanctum/csrf-cookie', [\Laravel\Sanctum\Http\Controllers\CsrfCookieController::class, 'show']);

// Vue SPA - catch all routes and serve the app
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
