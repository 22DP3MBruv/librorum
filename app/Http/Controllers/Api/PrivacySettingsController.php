<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PrivacySettingsController extends Controller
{
    /**
     * Get the authenticated user's privacy settings
     */
    public function show(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'data' => [
                'profile_visibility' => $user->profile_visibility,
                'reading_progress_visibility' => $user->reading_progress_visibility,
                'activity_visibility' => $user->activity_visibility,
                'allow_follows' => $user->allow_follows,
                'require_follow_approval' => $user->require_follow_approval,
            ]
        ]);
    }

    /**
     * Update the authenticated user's privacy settings
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'profile_visibility' => 'sometimes|in:public,followers,private',
            'reading_progress_visibility' => 'sometimes|in:public,followers,private',
            'activity_visibility' => 'sometimes|in:public,followers,private',
            'allow_follows' => 'sometimes|boolean',
            'require_follow_approval' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'message_lv' => 'Validācijas kļūda',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $user->update($validator->validated());

        return response()->json([
            'message' => 'Privacy settings updated successfully',
            'message_lv' => 'Privātuma iestatījumi veiksmīgi atjaunināti',
            'data' => [
                'profile_visibility' => $user->profile_visibility,
                'reading_progress_visibility' => $user->reading_progress_visibility,
                'activity_visibility' => $user->activity_visibility,
                'allow_follows' => $user->allow_follows,
                'require_follow_approval' => $user->require_follow_approval,
            ]
        ]);
    }
}
