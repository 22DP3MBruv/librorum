<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Thread;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class AccountSettingsController extends Controller
{
    /**
     * Update the user's username
     */
    public function updateUsername(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|min:3|max:50|unique:users,username,' . $request->user()->user_id . ',user_id|alpha_dash',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'message_lv' => 'Validācija neizdevās',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Verify password
        if (!Hash::check($request->password, $user->password_hash)) {
            return response()->json([
                'message' => 'Password is incorrect',
                'message_lv' => 'Parole ir nepareiza',
                'errors' => [
                    'password' => ['The password is incorrect']
                ]
            ], 422);
        }

        $user->username = $request->username;
        $user->save();

        return response()->json([
            'message' => 'Username updated successfully',
            'message_lv' => 'Lietotājvārds veiksmīgi atjaunināts',
            'data' => [
                'username' => $user->username
            ]
        ]);
    }

    /**
     * Update the user's password
     */
    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => ['required', 'string', Password::min(8)->mixedCase()->numbers(), 'confirmed'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'message_lv' => 'Validācija neizdevās',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Verify current password
        if (!Hash::check($request->current_password, $user->password_hash)) {
            return response()->json([
                'message' => 'Current password is incorrect',
                'message_lv' => 'Pašreizējā parole ir nepareiza',
                'errors' => [
                    'current_password' => ['The current password is incorrect']
                ]
            ], 422);
        }

        // Update password
        $user->password_hash = Hash::make($request->new_password);
        $user->save();

        // Revoke all tokens to force re-login
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Password updated successfully. Please log in again.',
            'message_lv' => 'Parole veiksmīgi atjaunināta. Lūdzu, piesakieties vēlreiz.',
        ]);
    }

    /**
     * Delete all user's threads and comments
     */
    public function deleteUserContent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'message_lv' => 'Validācija neizdevās',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Verify password
        if (!Hash::check($request->password, $user->password_hash)) {
            return response()->json([
                'message' => 'Password is incorrect',
                'message_lv' => 'Parole ir nepareiza',
                'errors' => [
                    'password' => ['The password is incorrect']
                ]
            ], 422);
        }

        // Delete all comments
        $commentsCount = $user->comments()->count();
        $user->comments()->delete();

        // Delete all threads
        $threadsCount = $user->threads()->count();
        $user->threads()->delete();

        return response()->json([
            'message' => 'All your content has been deleted',
            'message_lv' => 'Viss jūsu saturs ir dzēsts',
            'data' => [
                'threads_deleted' => $threadsCount,
                'comments_deleted' => $commentsCount
            ]
        ]);
    }

    /**
     * Delete the user's account permanently
     */
    public function deleteAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
            'confirmation' => 'required|string|in:DELETE',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'message_lv' => 'Validācija neizdevās',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Verify password
        if (!Hash::check($request->password, $user->password_hash)) {
            return response()->json([
                'message' => 'Password is incorrect',
                'message_lv' => 'Parole ir nepareiza',
                'errors' => [
                    'password' => ['The password is incorrect']
                ]
            ], 422);
        }

        // Delete all related data
        $user->readingProgress()->delete();
        $user->comments()->delete();
        $user->threads()->delete();
        $user->sentFollowRequests()->delete();
        $user->receivedFollowRequests()->delete();
        $user->following()->detach();
        $user->followers()->detach();
        $user->tokens()->delete();
        
        // Delete notifications related to this user
        \App\Models\Notification::where('user_id', $user->user_id)
            ->orWhere('notifier_id', $user->user_id)
            ->delete();
        
        // Delete likes
        \App\Models\Like::where('user_id', $user->user_id)->delete();

        // Delete the user account
        $user->delete();

        return response()->json([
            'message' => 'Your account has been permanently deleted',
            'message_lv' => 'Jūsu konts ir neatgriezeniski dzēsts'
        ]);
    }
}
