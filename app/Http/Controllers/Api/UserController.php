<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Get a user's profile by ID
     */
    public function show(Request $request, $userId)
    {
        $user = \App\Models\User::find($userId);
        
        if (!$user) {
            return response()->json([
                'message' => 'User not found',
                'message_lv' => 'Lietotājs nav atrasts'
            ], 404);
        }

        $viewer = $request->user();

        // Check profile visibility privacy
        if (!$user->canViewProfile($viewer)) {
            return response()->json([
                'message' => 'This profile is private',
                'message_lv' => 'Šis profils ir privāts'
            ], 403);
        }

        $isFollowing = false;
        if ($viewer) {
            $isFollowing = $viewer->following()->where('followee_id', $userId)->exists();
        }

        return response()->json([
            'data' => [
                'user_id' => $user->user_id,
                'name' => $user->username,
                'username' => $user->username,
                'email' => $user->email,
                'created_at' => $user->created_at,
                'is_following' => $isFollowing,
                'can_view_reading_progress' => $user->canViewReadingProgress($viewer),
                'can_view_activity' => $user->canViewActivity($viewer),
                'can_be_followed' => $user->canBeFollowed()
            ]
        ]);
    }

    /**
     * Get a user's followers (defaults to authenticated user if no userId provided)
     */
    public function followers(Request $request, $userId = null)
    {
        $viewer = $request->user();

        // If userId is provided, fetch that user's followers, otherwise use authenticated user
        if ($userId) {
            $user = \App\Models\User::find($userId);
            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            // Check if viewer can see this user's profile
            if (!$user->canViewProfile($viewer)) {
                return response()->json([
                    'message' => 'This profile is private',
                    'message_lv' => 'Šis profils ir privāts'
                ], 403);
            }
        } else {
            $user = $viewer;
        }
        
        $followers = $user->followers()
            ->select(['users.user_id', 'users.username', 'users.email', 'users.created_at', 'following.created_at as followed_at'])
            ->get()
            ->map(function ($follower) {
                return [
                    'user_id' => $follower->user_id,
                    'name' => $follower->username,
                    'username' => $follower->username,
                    'email' => $follower->email,
                    'created_at' => $follower->created_at,
                    'pivot' => [
                        'created_at' => $follower->followed_at
                    ]
                ];
            });

        return response()->json([
            'data' => $followers
        ]);
    }

    /**
     * Get the users that a user is following (defaults to authenticated user if no userId provided)
     */
    public function following(Request $request, $userId = null)
    {
        $viewer = $request->user();

        // If userId is provided, fetch that user's following, otherwise use authenticated user
        if ($userId) {
            $user = \App\Models\User::find($userId);
            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            // Check if viewer can see this user's profile
            if (!$user->canViewProfile($viewer)) {
                return response()->json([
                    'message' => 'This profile is private',
                    'message_lv' => 'Šis profils ir privāts'
                ], 403);
            }
        } else {
            $user = $viewer;
        }
        
        $following = $user->following()
            ->select(['users.user_id', 'users.username', 'users.email', 'users.created_at', 'following.created_at as followed_at'])
            ->get()
            ->map(function ($followedUser) {
                return [
                    'user_id' => $followedUser->user_id,
                    'name' => $followedUser->username,
                    'username' => $followedUser->username,
                    'email' => $followedUser->email,
                    'created_at' => $followedUser->created_at,
                    'pivot' => [
                        'created_at' => $followedUser->followed_at
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

        // Check if the user allows follows
        if (!$userToFollow->canBeFollowed()) {
            return response()->json([
                'message' => 'This user does not allow new followers',
                'message_lv' => 'Šis lietotājs neatļauj jaunus sekotājus'
            ], 403);
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
