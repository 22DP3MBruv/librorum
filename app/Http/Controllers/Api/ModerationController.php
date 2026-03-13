<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ModerationController extends Controller
{
    /**
     * Marķē lietotāju (tikai moderatori un admini)
     * Mērķētie lietototāji tiek slēpti diskusijās un komentāros
     */
    public function flagUser(Request $request, $userId)
    {
        $moderator = $request->user();

        // Pārbauda, vai lietotājs ir moderators vai administrators
        if (!$moderator->isModerator()) {
            return response()->json([
                'message' => 'Unauthorized. Moderator access required.',
                'message_lv' => 'Nav atļauts. Nepieciešama moderatora piekļuve.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'message_lv' => 'Validācijas kļūda',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
                'message_lv' => 'Lietotājs nav atrasts'
            ], 404);
        }

        // Neatļauj bloķēt moderatorus vai administratorus
        if ($user->isModerator()) {
            return response()->json([
                'message' => 'Cannot ban moderators or admins',
                'message_lv' => 'Nevar bloķēt moderatorus vai administratorus'
            ], 403);
        }

        // Nebloķē jau bloķētus lietotājus
        if ($user->is_flagged) {
            return response()->json([
                'message' => 'User is already banned',
                'message_lv' => 'Lietotājs jau ir bloķēts'
            ], 422);
        }

        $user->update([
            'is_flagged' => true,
            'flagged_at' => now(),
            'flag_reason' => $request->reason,
            'flagged_by' => $moderator->user_id,
        ]);

        // Izveido paziņojumu bloķētajam lietotājam (marķēšana = bloķēšana)
        Notification::createUserBanned($user, $moderator, $request->reason);

        return response()->json([
            'message' => 'User banned successfully',
            'message_lv' => 'Lietotājs veiksmīgi bloķēts',
            'data' => [
                'user_id' => $user->user_id,
                'username' => $user->username,
                'is_flagged' => $user->is_flagged,
                'flagged_at' => $user->flagged_at,
                'flag_reason' => $user->flag_reason,
            ]
        ]);
    }

    /**
     * Noņem marķējumu no lietotāja (tikai moderatori un admini)
     */
    public function unflagUser(Request $request, $userId)
    {
        $moderator = $request->user();

        // Pārbauda, vai lietotājs ir moderators vai administrators
        if (!$moderator->isModerator()) {
            return response()->json([
                'message' => 'Unauthorized. Moderator access required.',
                'message_lv' => 'Nav atļauts. Nepieciešama moderatora piekļuve.'
            ], 403);
        }

        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'message' => 'User not found',
                'message_lv' => 'Lietotājs nav atrasts'
            ], 404);
        }

        if (!$user->is_flagged) {
            return response()->json([
                'message' => 'User is not banned',
                'message_lv' => 'Lietotājs nav bloķēts'
            ], 422);
        }

        $user->update([
            'is_flagged' => false,
            'flagged_at' => null,
            'flag_reason' => null,
            'flagged_by' => null,
        ]);

        return response()->json([
            'message' => 'User unbanned successfully',
            'message_lv' => 'Lietotāja bloķēšana veiksmīgi noņemta',
            'data' => [
                'user_id' => $user->user_id,
                'username' => $user->username,
                'is_flagged' => $user->is_flagged,
            ]
        ]);
    }

    /**
     * Dabū visus marķētus lietotājus (tikai moderatori un admini)
     */
    public function getFlaggedUsers(Request $request)
    {
        $moderator = $request->user();

        // Pārbauda, vai lietotājs ir moderators vai administrators
        if (!$moderator->isModerator()) {
            return response()->json([
                'message' => 'Unauthorized. Moderator access required.',
                'message_lv' => 'Nav atļauts. Nepieciešama moderatora piekļuve.'
            ], 403);
        }

        $flaggedUsers = User::flagged()
            ->with('flaggedBy:user_id,username')
            ->select('user_id', 'username', 'email', 'is_flagged', 'flagged_at', 'flag_reason', 'flagged_by')
            ->orderBy('flagged_at', 'desc')
            ->get()
            ->map(function ($user) {
                return [
                    'user_id' => $user->user_id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'is_flagged' => $user->is_flagged,
                    'flagged_at' => $user->flagged_at,
                    'flag_reason' => $user->flag_reason,
                    'flagged_by' => [
                        'user_id' => $user->flaggedBy?->user_id,
                        'username' => $user->flaggedBy?->username,
                    ]
                ];
            });

        return response()->json([
            'data' => $flaggedUsers
        ]);
    }
}
