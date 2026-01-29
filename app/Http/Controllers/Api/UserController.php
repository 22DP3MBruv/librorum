<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\FollowRequest;
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
        $hasPendingRequest = false;
        if ($viewer) {
            $isFollowing = $viewer->following()->where('followee_id', $userId)->exists();
            $hasPendingRequest = $viewer->hasPendingRequestTo($userId);
        }

        return response()->json([
            'data' => [
                'user_id' => $user->user_id,
                'name' => $user->username,
                'username' => $user->username,
                'email' => $user->email,
                'created_at' => $user->created_at,
                'is_following' => $isFollowing,
                'has_pending_request' => $hasPendingRequest,
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

        // Check if there's already a request (any status)
        $existingRequest = FollowRequest::where('follower_id', $currentUser->user_id)
            ->where('followee_id', $userId)
            ->first();

        if ($existingRequest) {
            if ($existingRequest->status === 'pending') {
                return response()->json([
                    'message' => 'You already have a pending follow request to this user',
                    'message_lv' => 'Jums jau ir neapstiprinājts sekošanas pieprasījums šim lietotājam'
                ], 422);
            } else {
                // Update existing rejected/accepted request to pending
                $existingRequest->update(['status' => 'pending']);
            }
        } else {
            // Create new follow request
            $existingRequest = FollowRequest::create([
                'follower_id' => $currentUser->user_id,
                'followee_id' => $userId,
                'status' => 'pending'
            ]);
        }

        // If user requires follow approval, send notification
        if ($userToFollow->require_follow_approval) {
            Notification::createFollowRequest($userToFollow, $currentUser);

            return response()->json([
                'message' => 'Follow request sent',
                'message_lv' => 'Sekošanas pieprasījums nosūtīts',
                'has_pending_request' => true
            ]);
        } else {
            // Directly follow the user
            $currentUser->following()->attach($userId);
            Notification::createNewFollower($userToFollow, $currentUser);

            return response()->json([
                'message' => 'Successfully followed user',
                'message_lv' => 'Veiksmīgi sākāt sekot lietotājam'
            ]);
        }
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

    /**
     * Cancel a follow request
     */
    public function cancelFollowRequest(Request $request, $userId)
    {
        $currentUser = $request->user();
        
        $followRequest = FollowRequest::where('follower_id', $currentUser->user_id)
            ->where('followee_id', $userId)
            ->where('status', 'pending')
            ->first();

        if (!$followRequest) {
            return response()->json([
                'message' => 'No pending follow request found',
                'message_lv' => 'Nav atrasts neapstiprinājums sekošanas pieprasījums'
            ], 404);
        }

        $followRequest->delete();

        return response()->json([
            'message' => 'Follow request cancelled',
            'message_lv' => 'Sekošanas pieprasījums atcelts'
        ]);
    }

    /**
     * Get pending follow requests for the authenticated user
     */
    public function getFollowRequests(Request $request)
    {
        $currentUser = $request->user();
        
        $requests = $currentUser->receivedFollowRequests()
            ->where('status', 'pending')
            ->with('follower')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($request) {
                return [
                    'request_id' => $request->request_id,
                    'follower' => [
                        'user_id' => $request->follower->user_id,
                        'name' => $request->follower->username,
                        'username' => $request->follower->username,
                    ],
                    'created_at' => $request->created_at,
                ];
            });

        return response()->json([
            'data' => $requests
        ]);
    }

    /**
     * Accept a follow request
     */
    public function acceptFollowRequest(Request $request, $requestId)
    {
        $currentUser = $request->user();
        
        $followRequest = FollowRequest::where('request_id', $requestId)
            ->where('followee_id', $currentUser->user_id)
            ->where('status', 'pending')
            ->first();

        if (!$followRequest) {
            return response()->json([
                'message' => 'Follow request not found',
                'message_lv' => 'Sekošanas pieprasījums nav atrasts'
            ], 404);
        }

        $followRequest->accept();

        return response()->json([
            'message' => 'Follow request accepted',
            'message_lv' => 'Sekošanas pieprasījums apstiprināts'
        ]);
    }

    /**
     * Reject a follow request
     */
    public function rejectFollowRequest(Request $request, $requestId)
    {
        $currentUser = $request->user();
        
        $followRequest = FollowRequest::where('request_id', $requestId)
            ->where('followee_id', $currentUser->user_id)
            ->where('status', 'pending')
            ->first();

        if (!$followRequest) {
            return response()->json([
                'message' => 'Follow request not found',
                'message_lv' => 'Sekošanas pieprasījums nav atrasts'
            ], 404);
        }

        $followRequest->reject();

        return response()->json([
            'message' => 'Follow request rejected',
            'message_lv' => 'Sekošanas pieprasījums noraidīts'
        ]);
    }
}
