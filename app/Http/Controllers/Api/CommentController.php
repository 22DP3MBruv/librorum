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
     * Shows comment list for specific thread.
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
     * Saves newly created comment.
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

        // Creates notification for thread owner
        Notification::createThreadReply($comment, $thread, $request->user());

        return new CommentResource($comment);
    }

    /**
     * Shows specific comment.
     */
    public function show($threadId, $id)
    {
        $comment = Comment::with('user')
            ->where('thread_id', $threadId)
            ->findOrFail($id);

        return new CommentResource($comment);
    }

    /**
     * Updates specific comment.
     */
    public function update(Request $request, $threadId, $id)
    {
        $comment = Comment::where('thread_id', $threadId)->findOrFail($id);

        // Checks if user is comment owner
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
     * Deletes specific comment.
     */
    public function destroy(Request $request, $threadId, $id)
    {
        $comment = Comment::where('thread_id', $threadId)->findOrFail($id);

        // Checks if user is comment owner or administrator
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
