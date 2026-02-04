<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Book;
use App\Models\Thread;
use App\Models\Comment;
use App\Models\ReadingProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
                    'authors' => $book->authors,
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

    /**
     * Make a user an admin
     */
    public function makeAdmin(Request $request, $userId)
    {
        $admin = $request->user();

        // Only admins can make other users admins
        if (!$admin->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized. Admin access required.',
                'message_lv' => 'Nav atļauts. Nepieciešama administratora piekļuve.'
            ], 403);
        }

        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
                'message_lv' => 'Lietotājs nav atrasts'
            ], 404);
        }

        if ($user->isAdmin()) {
            return response()->json([
                'message' => 'User is already an admin',
                'message_lv' => 'Lietotājs jau ir administrators'
            ], 422);
        }

        $user->update(['role' => 'admin']);

        return response()->json([
            'message' => 'User promoted to admin successfully',
            'message_lv' => 'Lietotājs veiksmīgi paaugstināts par administratoru',
            'data' => [
                'user_id' => $user->user_id,
                'username' => $user->username,
                'role' => $user->role,
            ]
        ]);
    }

    /**
     * Remove admin role from a user
     */
    public function removeAdmin(Request $request, $userId)
    {
        $admin = $request->user();

        // Only admins can remove admin role
        if (!$admin->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized. Admin access required.',
                'message_lv' => 'Nav atļauts. Nepieciešama administratora piekļuve.'
            ], 403);
        }

        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
                'message_lv' => 'Lietotājs nav atrasts'
            ], 404);
        }

        // Prevent removing admin role from yourself
        if ($user->user_id === $admin->user_id) {
            return response()->json([
                'message' => 'Cannot remove admin role from yourself',
                'message_lv' => 'Nevar noņemt administratora lomu sev'
            ], 422);
        }

        if (!$user->isAdmin()) {
            return response()->json([
                'message' => 'User is not an admin',
                'message_lv' => 'Lietotājs nav administrators'
            ], 422);
        }

        $user->update(['role' => 'user']);

        return response()->json([
            'message' => 'Admin role removed successfully',
            'message_lv' => 'Administratora loma veiksmīgi noņemta',
            'data' => [
                'user_id' => $user->user_id,
                'username' => $user->username,
                'role' => $user->role,
            ]
        ]);
    }

    /**
     * Get all users with pagination and filtering
     */
    public function getUsers(Request $request)
    {
        $admin = $request->user();

        if (!$admin->isModerator()) {
            return response()->json([
                'message' => 'Unauthorized. Admin/Moderator access required.',
                'message_lv' => 'Nav atļauts. Nepieciešama administratora/moderatora piekļuve.'
            ], 403);
        }

        $perPage = $request->get('per_page', 50);
        $search = $request->get('search');
        $role = $request->get('role');

        $query = User::query();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role) {
            $query->where('role', $role);
        }

        $users = $query->orderBy('created_at', 'desc')
                       ->paginate($perPage);

        return response()->json([
            'data' => $users->items(),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'last_page' => $users->lastPage(),
            ]
        ]);
    }

    /**
     * Delete a thread (admin only)
     */
    public function deleteThread(Request $request, $threadId)
    {
        $admin = $request->user();

        if (!$admin->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized. Admin access required.',
                'message_lv' => 'Nav atļauts. Nepieciešama administratora piekļuve.'
            ], 403);
        }

        $thread = Thread::find($threadId);

        if (!$thread) {
            return response()->json([
                'message' => 'Thread not found',
                'message_lv' => 'Diskusija nav atrasta'
            ], 404);
        }

        $thread->delete();

        return response()->json([
            'message' => 'Thread deleted successfully',
            'message_lv' => 'Diskusija veiksmīgi izdzēsta'
        ]);
    }

    /**
     * Delete a comment (admin only)
     */
    public function deleteComment(Request $request, $commentId)
    {
        $admin = $request->user();

        if (!$admin->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized. Admin access required.',
                'message_lv' => 'Nav atļauts. Nepieciešama administratora piekļuve.'
            ], 403);
        }

        $comment = Comment::find($commentId);

        if (!$comment) {
            return response()->json([
                'message' => 'Comment not found',
                'message_lv' => 'Komentārs nav atrasts'
            ], 404);
        }

        $comment->delete();

        return response()->json([
            'message' => 'Comment deleted successfully',
            'message_lv' => 'Komentārs veiksmīgi izdzēsts'
        ]);
    }
}
