<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Book;
use App\Models\Thread;
use App\Models\Comment;
use App\Models\ReadingProgress;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Get admin dashboard statistics
     */
    public function getStatistics(Request $request)
    {
        $user = $request->user();

        // Check if user is admin or moderator
        if (!$user->isModerator()) {
            return response()->json([
                'message' => 'Unauthorized. Admin/Moderator access required.',
                'message_lv' => 'Nav atļauts. Nepieciešama administratora/moderatora piekļuve.'
            ], 403);
        }

        // Get all statistics
        $totalUsers = User::count();
        $activeUsers = User::where('created_at', '>=', now()->subDays(30))->count();
        $flaggedUsers = User::where('is_flagged', true)->count();
        
        $totalBooks = Book::count();
        $totalThreads = Thread::count();
        $totalComments = Comment::count();
        
        $totalReadingProgress = ReadingProgress::count();
        $completedBooks = ReadingProgress::where('status', 'completed')->count();
        $currentlyReading = ReadingProgress::where('status', 'reading')->count();

        // Get role distribution
        $usersByRole = User::selectRaw('role, COUNT(*) as count')
            ->groupBy('role')
            ->get()
            ->pluck('count', 'role')
            ->toArray();

        // Get recent activity (last 7 days)
        $recentUsers = User::where('created_at', '>=', now()->subDays(7))->count();
        $recentThreads = Thread::where('created_at', '>=', now()->subDays(7))->count();
        $recentComments = Comment::where('created_at', '>=', now()->subDays(7))->count();

        // Get most active users
        $mostActiveUsers = User::withCount(['threads', 'comments'])
            ->orderByDesc('threads_count')
            ->orderByDesc('comments_count')
            ->limit(10)
            ->get()
            ->map(function ($user) {
                return [
                    'user_id' => $user->user_id,
                    'username' => $user->username,
                    'threads_count' => $user->threads_count,
                    'comments_count' => $user->comments_count,
                    'total_activity' => $user->threads_count + $user->comments_count,
                ];
            });

        // Get popular books (most threads/comments)
        $popularBooks = Book::withCount(['threads', 'comments'])
            ->orderByDesc('threads_count')
            ->limit(10)
            ->get()
            ->map(function ($book) {
                return [
                    'book_id' => $book->book_id,
                    'title' => $book->title,
                    'author' => $book->author,
                    'threads_count' => $book->threads_count,
                    'comments_count' => $book->comments_count,
                ];
            });

        return response()->json([
            'data' => [
                'overview' => [
                    'total_users' => $totalUsers,
                    'active_users_30d' => $activeUsers,
                    'flagged_users' => $flaggedUsers,
                    'total_books' => $totalBooks,
                    'total_threads' => $totalThreads,
                    'total_comments' => $totalComments,
                    'total_reading_progress' => $totalReadingProgress,
                    'completed_books' => $completedBooks,
                    'currently_reading' => $currentlyReading,
                ],
                'users_by_role' => $usersByRole,
                'recent_activity' => [
                    'new_users_7d' => $recentUsers,
                    'new_threads_7d' => $recentThreads,
                    'new_comments_7d' => $recentComments,
                ],
                'most_active_users' => $mostActiveUsers,
                'popular_books' => $popularBooks,
            ]
        ]);
    }
}
