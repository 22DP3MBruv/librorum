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
     * Dabū statistiku par lietotājiem, diskusijām, komentāriem un grāmatām (admin/moderator piekļuve)
     */
    public function getStatistics(Request $request)
    {
        $user = $request->user();

        // Pārbauda, vai lietotājs ir admins vai moderators
        if (!$user->isModerator()) {
            return response()->json([
                'message' => 'Unauthorized. Admin/Moderator access required.',
                'message_lv' => 'Nav atļauts. Nepieciešama administratora/moderatora piekļuve.'
            ], 403);
        }

        // Dabū visu statistiku
        $totalUsers = User::count();
        $activeUsers = User::where('created_at', '>=', now()->subDays(30))->count();
        $flaggedUsers = User::where('is_flagged', true)->count();
        
        $totalBooks = Book::count();
        $totalThreads = Thread::count();
        $totalComments = Comment::count();
        
        $completedBooks = ReadingProgress::where('status', 'completed')->count();

        // Dabū jaunāko aktivitāti (pēdējās 7 dienas)
        $recentUsers = User::where('created_at', '>=', now()->subDays(7))->count();
        $recentThreads = Thread::where('created_at', '>=', now()->subDays(7))->count();
        $recentComments = Comment::where('created_at', '>=', now()->subDays(7))->count();

        // Dabū populārākās grāmatas pēc diskusiju un komentāru skaita
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
                    'completed_books' => $completedBooks,
                ],
                'recent_activity' => [
                    'new_users_7d' => $recentUsers,
                    'new_threads_7d' => $recentThreads,
                    'new_comments_7d' => $recentComments,
                ],
                'popular_books' => $popularBooks,
            ]
        ]);
    }

    /**
     * Padara lietotāju par administratoru
     */
    public function makeAdmin(Request $request, $userId)
    {
        $admin = $request->user();

        // Tikai admini var piešķirt admin lomu
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
     * Atņem administratora lomu no lietotāja
     */
    public function removeAdmin(Request $request, $userId)
    {
        $admin = $request->user();

        // Tikai admini var atņemt admin lomu
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

        // Neļauj atņemt admin lomu sev
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
     * Dabū lietotāju sarakstu ar meklēšanu un filtrēšanu (admin/moderator piekļuve)
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
     * Izdzēš diskusiju (admin only)
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
     * Izdzēš komentāru (admin only)
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
