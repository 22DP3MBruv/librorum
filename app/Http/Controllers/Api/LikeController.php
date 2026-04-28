<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Like;
use App\Models\Thread;
use App\Models\Comment;
use App\Models\Notification;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    /**
     * Toggles like for a thread or comment.
     */
    public function toggle(Request $request)
    {
        $validated = $request->validate([
            'target_type' => 'required|in:thread,comment',
            'target_id' => 'required|integer',
        ]);

        $existingLike = Like::where('user_id', $request->user()->user_id)
            ->where('target_type', $validated['target_type'])
            ->where('target_id', $validated['target_id'])
            ->first();

        if ($existingLike) {
            // Removes like
            $existingLike->delete();
            return response()->json([
                'liked' => false,
                'message' => 'Like removed',
                'message_lv' => 'Patika noņemts'
            ]);
        } else {
            // Adds like
            $like = Like::create([
                'user_id' => $request->user()->user_id,
                'target_type' => $validated['target_type'],
                'target_id' => $validated['target_id'],
            ]);

            // Creates notification for content owner
            if ($validated['target_type'] === 'thread') {
                $likeable = Thread::find($validated['target_id']);
            } else {
                $likeable = Comment::find($validated['target_id']);
            }

            if ($likeable) {
                Notification::createLike($like, $likeable, $request->user());
            }

            return response()->json([
                'liked' => true,
                'message' => 'Liked',
                'message_lv' => 'Patika'
            ]);
        }
    }

    /**
     * Gets like count and user's like status for target.
     */
    public function status(Request $request)
    {
        $validated = $request->validate([
            'target_type' => 'required|in:thread,comment',
            'target_id' => 'required|integer',
        ]);

        $likeCount = Like::where('target_type', $validated['target_type'])
            ->where('target_id', $validated['target_id'])
            ->count();

        $userLiked = false;
        if ($request->user()) {
            $userLiked = Like::where('user_id', $request->user()->user_id)
                ->where('target_type', $validated['target_type'])
                ->where('target_id', $validated['target_id'])
                ->exists();
        }

        return response()->json([
            'like_count' => $likeCount,
            'user_liked' => $userLiked
        ]);
    }
}
