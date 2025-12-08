<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public authentication routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public book routes (read-only)
Route::get('/books', [BookController::class, 'index']);
Route::get('/books/search', [BookController::class, 'search']);
Route::get('/books/external-search', [BookController::class, 'externalSearch']);
Route::get('/books/popular', [BookController::class, 'popular']);
Route::get('/books/tag/{tag}', [BookController::class, 'byTag']);
Route::get('/books/{identifier}', [BookController::class, 'show']);

// Protected authentication routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Protected book routes (admin only)
    Route::post('/books', [BookController::class, 'store']);
    Route::post('/books/import-isbn', [BookController::class, 'importByIsbn']);
    Route::put('/books/{id}', [BookController::class, 'update']);
    Route::post('/books/{id}/sync', [BookController::class, 'syncWithExternalApi']);
    Route::delete('/books/{id}', [BookController::class, 'destroy']);
});