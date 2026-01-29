<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\ReadingProgressController;
use App\Http\Controllers\Api\ThreadController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\LikeController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PrivacySettingsController;
use App\Http\Controllers\Api\AccountSettingsController;
use App\Http\Controllers\Api\ModerationController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\NotificationController;
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

// Public thread routes (read-only)
Route::get('/threads', [ThreadController::class, 'index']);
Route::get('/threads/{id}', [ThreadController::class, 'show']);
Route::get('/books/{bookId}/threads', [ThreadController::class, 'forBook']);

// Public comment routes (read-only)
Route::get('/threads/{threadId}/comments', [CommentController::class, 'index']);
Route::get('/threads/{threadId}/comments/{id}', [CommentController::class, 'show']);

// Protected authentication routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Privacy settings routes
    Route::get('/user/privacy', [PrivacySettingsController::class, 'show']);
    Route::put('/user/privacy', [PrivacySettingsController::class, 'update']);
    
    // Account settings routes
    Route::put('/user/account/username', [AccountSettingsController::class, 'updateUsername']);
    Route::put('/user/account/password', [AccountSettingsController::class, 'updatePassword']);
    Route::delete('/user/account/content', [AccountSettingsController::class, 'deleteUserContent']);
    Route::delete('/user/account', [AccountSettingsController::class, 'deleteAccount']);
    
    // User social routes
    Route::get('/user/profile/{userId}', [UserController::class, 'show']);
    Route::get('/user/followers', [UserController::class, 'followers']);
    Route::get('/user/following', [UserController::class, 'following']);
    Route::get('/user/{userId}/followers', [UserController::class, 'followers']);
    Route::get('/user/{userId}/following', [UserController::class, 'following']);
    Route::post('/user/follow/{userId}', [UserController::class, 'follow']);
    Route::delete('/user/unfollow/{userId}', [UserController::class, 'unfollow']);
    
    // Follow request routes
    Route::get('/user/follow-requests', [UserController::class, 'getFollowRequests']);
    Route::post('/user/follow-requests/{requestId}/accept', [UserController::class, 'acceptFollowRequest']);
    Route::post('/user/follow-requests/{requestId}/reject', [UserController::class, 'rejectFollowRequest']);
    Route::delete('/user/follow-request/{userId}/cancel', [UserController::class, 'cancelFollowRequest']);
    
    // Reading Progress routes
    Route::get('/reading-progress', [ReadingProgressController::class, 'index']);
    Route::post('/reading-progress', [ReadingProgressController::class, 'store']);
    Route::get('/reading-progress/{id}', [ReadingProgressController::class, 'show']);
    Route::put('/reading-progress/{id}', [ReadingProgressController::class, 'update']);
    Route::delete('/reading-progress/{id}', [ReadingProgressController::class, 'destroy']);
    Route::get('/reading-progress/book/{bookId}', [ReadingProgressController::class, 'getByBook']);
    
    // Thread routes (write operations only - reads are public)
    Route::post('/threads', [ThreadController::class, 'store']);
    Route::put('/threads/{id}', [ThreadController::class, 'update']);
    Route::delete('/threads/{id}', [ThreadController::class, 'destroy']);
    
    // Comment routes (write operations only - reads are public)
    Route::post('/threads/{threadId}/comments', [CommentController::class, 'store']);
    Route::put('/threads/{threadId}/comments/{id}', [CommentController::class, 'update']);
    Route::delete('/threads/{threadId}/comments/{id}', [CommentController::class, 'destroy']);
    
    // Like routes
    Route::post('/likes/toggle', [LikeController::class, 'toggle']);
    Route::get('/likes/status', [LikeController::class, 'status']);
    
    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/{id}/mark-unread', [NotificationController::class, 'markAsUnread']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
    Route::delete('/notifications/read/all', [NotificationController::class, 'deleteAllRead']);
    Route::get('/notifications/settings', [NotificationController::class, 'getSettings']);
    
    // Admin and Moderation routes (admin only)
    Route::get('/admin/statistics', [AdminController::class, 'getStatistics']);
    Route::post('/admin/users/{userId}/make-admin', [AdminController::class, 'makeAdmin']);
    Route::post('/admin/users/{userId}/remove-admin', [AdminController::class, 'removeAdmin']);
    Route::get('/admin/users', [AdminController::class, 'getUsers']);
    Route::delete('/admin/threads/{threadId}', [AdminController::class, 'deleteThread']);
    Route::delete('/admin/comments/{commentId}', [AdminController::class, 'deleteComment']);
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