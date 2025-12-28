<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Get the authenticated user's followers
     */
    public function followers(Request $request)
    {
        $user = $request->user();
        
        $followers = $user->followers()
            ->select(['user_id', 'username', 'email', 'created_at'])
            ->withPivot('created_at')
            ->get()
            ->map(function ($follower) {
                return [
                    'user_id' => $follower->user_id,
                    'name' => $follower->username,
                    'username' => $follower->username,
                    'email' => $follower->email,
                    'created_at' => $follower->created_at,
                    'pivot' => [
                        'created_at' => $follower->pivot->created_at
                    ]
                ];
            });

        return response()->json([
            'data' => $followers
        ]);
    }

    /**
     * Get the users that the authenticated user is following
     */
    public function following(Request $request)
    {
        $user = $request->user();
        
        $following = $user->following()
            ->select(['user_id', 'username', 'email', 'created_at'])
            ->withPivot('created_at')
            ->get()
            ->map(function ($followedUser) {
                return [
                    'user_id' => $followedUser->user_id,
                    'name' => $followedUser->username,
                    'username' => $followedUser->username,
                    'email' => $followedUser->email,
                    'created_at' => $followedUser->created_at,
                    'pivot' => [
                        'created_at' => $followedUser->pivot->created_at
                    ]
                ];
            });

        return response()->json([
            'data' => $following
        ]);
    }

    /**
     * Follow a user
     */
    public function follow(Request $request, $userId)
    {
        $currentUser = $request->user();
        
        if ($currentUser->user_id == $userId) {
            return response()->json([
                'message' => 'You cannot follow yourself',
                'message_lv' => 'Jūs nevarat sekot sev'
            ], 422);
        }

        $userToFollow = \App\Models\User::find($userId);
        
        if (!$userToFollow) {
            return response()->json([
                'message' => 'User not found',
                'message_lv' => 'Lietotājs nav atrasts'
            ], 404);
        }

        // Check if already following
        if ($currentUser->following()->where('followee_id', $userId)->exists()) {
            return response()->json([
                'message' => 'You are already following this user',
                'message_lv' => 'Jūs jau sekojat šim lietotājam'
            ], 422);
        }

        $currentUser->following()->attach($userId);

        return response()->json([
            'message' => 'Successfully followed user',
            'message_lv' => 'Veiksmīgi sākāt sekot lietotājam'
        ]);
    }

    /**
     * Unfollow a user
     */
    public function unfollow(Request $request, $userId)
    {
        $currentUser = $request->user();
        
        $currentUser->following()->detach($userId);

        return response()->json([
            'message' => 'Successfully unfollowed user',
            'message_lv' => 'Veiksmīgi pārstājāt sekot lietotājam'
        ]);
    }
}
