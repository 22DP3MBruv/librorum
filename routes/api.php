<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\ReadingProgressController;
use App\Http\Controllers\Api\ThreadController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PrivacySettingsController;
use App\Http\Controllers\Api\ModerationController;
use App\Http\Controllers\Api\AdminController;
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
Route::get('/books/{identifier}', [BookController::class, 'show']);

// Protected authentication routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Privacy settings routes
    Route::get('/user/privacy', [PrivacySettingsController::class, 'show']);
    Route::put('/user/privacy', [PrivacySettingsController::class, 'update']);
    
    // User social routes
    Route::get('/user/profile/{userId}', [UserController::class, 'show']);
    Route::get('/user/followers', [UserController::class, 'followers']);
    Route::get('/user/following', [UserController::class, 'following']);
    Route::get('/user/{userId}/followers', [UserController::class, 'followers']);
    Route::get('/user/{userId}/following', [UserController::class, 'following']);
    Route::post('/user/follow/{userId}', [UserController::class, 'follow']);
    Route::delete('/user/unfollow/{userId}', [UserController::class, 'unfollow']);
    
    // Reading Progress routes
    Route::get('/reading-progress', [ReadingProgressController::class, 'index']);
    Route::post('/reading-progress', [ReadingProgressController::class, 'store']);
    Route::get('/reading-progress/{id}', [ReadingProgressController::class, 'show']);
    Route::put('/reading-progress/{id}', [ReadingProgressController::class, 'update']);
    Route::delete('/reading-progress/{id}', [ReadingProgressController::class, 'destroy']);
    Route::get('/reading-progress/book/{bookId}', [ReadingProgressController::class, 'getByBook']);
    
    // Thread routes
    Route::get('/threads', [ThreadController::class, 'index']);
    Route::post('/threads', [ThreadController::class, 'store']);
    Route::get('/threads/{id}', [ThreadController::class, 'show']);
    Route::put('/threads/{id}', [ThreadController::class, 'update']);
    Route::delete('/threads/{id}', [ThreadController::class, 'destroy']);
    Route::get('/books/{bookId}/threads', [ThreadController::class, 'forBook']);
    
    // Comment routes
    Route::get('/threads/{threadId}/comments', [CommentController::class, 'index']);
    Route::post('/threads/{threadId}/comments', [CommentController::class, 'store']);
    Route::get('/threads/{threadId}/comments/{id}', [CommentController::class, 'show']);
    Route::put('/threads/{threadId}/comments/{id}', [CommentController::class, 'update']);
    Route::delete('/threads/{threadId}/comments/{id}', [CommentController::class, 'destroy']);
    
    // Like routes
    Route::post('/likes/toggle', [LikeController::class, 'toggle']);
    Route::get('/likes/status', [LikeController::class, 'status']);
    
    // Admin and Moderation routes (admin only)
    Route::get('/admin/statistics', [AdminController::class, 'getStatistics']);
    Route::post('/moderation/flag-user/{userId}', [ModerationController::class, 'flagUser']);
    Route::post('/moderation/unflag-user/{userId}', [ModerationController::class, 'unflagUser']);
    Route::get('/moderation/flagged-users', [ModerationController::class, 'getFlaggedUsers']);
    
    // Protected book routes (admin only)
    Route::post('/books', [BookController::class, 'store']);
    Route::post('/books/import-isbn', [BookController::class, 'importByIsbn']);
    Route::put('/books/{id}', [BookController::class, 'update']);
    Route::post('/books/{id}/sync', [BookController::class, 'syncWithExternalApi']);
    Route::delete('/books/{id}', [BookController::class, 'destroy']);
});