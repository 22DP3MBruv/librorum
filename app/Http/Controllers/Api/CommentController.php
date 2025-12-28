<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Thread;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Display a listing of comments for a thread.
     */
    public function index($threadId)
    {
        $thread = Thread::findOrFail($threadId);
        
        $comments = Comment::with('user')
            ->where('thread_id', $threadId)
            ->orderBy('created_at', 'asc')
            ->get();

        return CommentResource::collection($comments);
    }

    /**
     * Store a newly created comment.
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

        return new CommentResource($comment);
    }

    /**
     * Display the specified comment.
     */
    public function show($threadId, $id)
    {
        $comment = Comment::with('user')
            ->where('thread_id', $threadId)
            ->findOrFail($id);

        return new CommentResource($comment);
    }

    /**
     * Update the specified comment.
     */
    public function update(Request $request, $threadId, $id)
    {
        $comment = Comment::where('thread_id', $threadId)->findOrFail($id);

        // Check if user owns the comment
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
     * Remove the specified comment.
     */
    public function destroy(Request $request, $threadId, $id)
    {
        $comment = Comment::where('thread_id', $threadId)->findOrFail($id);

        // Check if user owns the comment or is admin
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
