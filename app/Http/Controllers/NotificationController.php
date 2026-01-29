<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the authenticated user.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $perPage = $request->input('per_page', 20);
        $unreadOnly = $request->input('unread_only', false);

        $query = Notification::where('user_id', $user->user_id)
            ->with('actor:user_id,username')
            ->orderBy('created_at', 'desc');

        if ($unreadOnly) {
            $query->unread();
        }

        $notifications = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
        ]);
    }

    /**
     * Get unread notification count.
     */
    public function unreadCount()
    {
        $user = Auth::user();
        
        $count = Notification::where('user_id', $user->user_id)
            ->unread()
            ->count();

        return response()->json([
            'success' => true,
            'unread_count' => $count,
        ]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead($id)
    {
        $user = Auth::user();
        
        $notification = Notification::where('notification_id', $id)
            ->where('user_id', $user->user_id)
            ->firstOrFail();

        $notification->markAsRead();
        
        // Refresh to get updated values from database
        $notification->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
            'notification' => $notification,
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        
        $updated = Notification::where('user_id', $user->user_id)
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
            'updated_count' => $updated,
        ]);
    }

    /**
     * Mark a specific notification as unread.
     */
    public function markAsUnread($id)
    {
        $user = Auth::user();
        
        $notification = Notification::where('notification_id', $id)
            ->where('user_id', $user->user_id)
            ->firstOrFail();

        $notification->markAsUnread();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as unread',
            'notification' => $notification,
        ]);
    }

    /**
     * Delete a specific notification.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        
        $notification = Notification::where('notification_id', $id)
            ->where('user_id', $user->user_id)
            ->firstOrFail();

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted',
        ]);
    }

    /**
     * Delete all read notifications.
     */
    public function deleteAllRead()
    {
        $user = Auth::user();
        
        $deleted = Notification::where('user_id', $user->user_id)
            ->read()
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'All read notifications deleted',
            'deleted_count' => $deleted,
        ]);
    }

    /**
     * Get notification settings/preferences.
     */
    public function getSettings()
    {
        $user = Auth::user();
        
        // If you implement notification preferences in the future
        return response()->json([
            'success' => true,
            'settings' => [
                'comment_replies' => true,
                'thread_replies' => true,
                'likes' => true,
                'new_followers' => true,
                'follow_requests' => true,
                'moderation_actions' => true,
            ],
        ]);
    }
}
