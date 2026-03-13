<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Dabū visus autentificētā lietotāja paziņojumus.
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
     * Dabū neizlasīto paziņojumu skaitu.
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
     * Marķē konkrētu paziņojumu kā izlasītu.
     */
    public function markAsRead($id)
    {
        $user = Auth::user();
        
        $notification = Notification::where('notification_id', $id)
            ->where('user_id', $user->user_id)
            ->firstOrFail();

        $notification->markAsRead();
        
        // Atjaunina, lai iegūtu atjauninātās vērtības no datubāzes
        $notification->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
            'notification' => $notification,
        ]);
    }

    /**
     * Marķē visus paziņojumus kā izlasītus.
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
     * Marķē konkrētu paziņojumu kā neizlasītu.
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
     * Izdzēš konkrētu paziņojumu.
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
     * Izdzēš visus izlasītus paziņojumus.
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

    
}
