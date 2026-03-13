<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Thread;
use App\Models\Notification;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Parāda komentāru sarakstu konkrētajai diskusijai.
     */
    public function index(Request $request, $threadId)
    {
        $thread = Thread::findOrFail($threadId);
        
        $comments = Comment::with('user')
            ->visible($request->user()) // Pass null if not authenticated
            ->withCount('likes')
            ->where('thread_id', $threadId)
            ->orderBy('created_at', 'asc')
            ->get();

        return CommentResource::collection($comments);
    }

    /**
     * Saglabā jauni izveidotu komentāru.
     */
    public function store(Request $request, $threadId)
    {
        $thread = Thread::findOrFail($threadId);

        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $comment = Comment::create([
            'thread_id' => $threadId,
            'user_id' => $request->user()->user_id,
            'content' => $validated['content'],
        ]);

        $comment->load('user');
        $comment->loadCount('likes');

        // Izveido paziņojumu diskusijas īpašniekam
        Notification::createThreadReply($comment, $thread, $request->user());

        return new CommentResource($comment);
    }

    /**
     * Parāda konkrētu komentāru.
     */
    public function show($threadId, $id)
    {
        $comment = Comment::with('user')
            ->where('thread_id', $threadId)
            ->findOrFail($id);

        return new CommentResource($comment);
    }

    /**
     * Atjaunina konkrētu komentāru.
     */
    public function update(Request $request, $threadId, $id)
    {
        $comment = Comment::where('thread_id', $threadId)->findOrFail($id);

        // Pārbauda, vai lietotājs ir komentāra īpašnieks
        if ($comment->user_id !== $request->user()->user_id) {
            return response()->json([
                'message' => 'Unauthorized',
                'message_lv' => 'Nav atļaujas'
            ], 403);
        }

        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $comment->update($validated);
        $comment->load('user');

        return new CommentResource($comment);
    }

    /**
     * Izdzēš konkrētu komentāru.
     */
    public function destroy(Request $request, $threadId, $id)
    {
        $comment = Comment::where('thread_id', $threadId)->findOrFail($id);

        // Pārbauda, vai lietotājs ir komentāra īpašnieks vai administrators
        if ($comment->user_id !== $request->user()->user_id && $request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized',
                'message_lv' => 'Nav atļaujas'
            ], 403);
        }

        $comment->delete();

        return response()->json([
            'message' => 'Comment deleted successfully',
            'message_lv' => 'Komentārs veiksmīgi izdzēsts'
        ]);
    }
}
