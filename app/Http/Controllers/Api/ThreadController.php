<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ThreadResource;
use App\Models\Thread;
use Illuminate\Http\Request;

class ThreadController extends Controller
{
    /**
     * Display a listing of threads.
     */
    public function index(Request $request)
    {
        $query = Thread::with(['user', 'book'])
            ->visible($request->user()) // Pass null if not authenticated
            ->withCount('comments');

        // Filter by book_id if provided
        if ($request->has('book_id')) {
            $query->where('book_id', $request->book_id);
        }

        // Filter by user_id if provided
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $threads = $query->orderBy('created_at', 'desc')->get();

        return ThreadResource::collection($threads);
    }

    /**
     * Get threads for a specific book.
     */
    public function forBook(Request $request, $bookId)
    {
        $threads = Thread::with(['user', 'book'])
            ->visible($request->user()) // Pass null if not authenticated
            ->withCount(['comments', 'likes'])
            ->where('book_id', $bookId)
            ->orderBy('created_at', 'desc')
            ->get();

        return ThreadResource::collection($threads);
    }

    /**
     * Store a newly created thread.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'book_id' => 'required|exists:books,book_id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'scope' => 'nullable|string|in:general,page',
            'page_number' => 'nullable|integer|min:1',
        ]);

        $thread = Thread::create([
            'user_id' => $request->user()->user_id,
            'book_id' => $validated['book_id'],
            'title' => $validated['title'],
            'content' => $validated['content'],
            'scope' => $validated['scope'] ?? 'general',
            'page_number' => $validated['page_number'] ?? null,
        ]);

        $thread->load(['user', 'book']);
        $thread->loadCount(['comments', 'likes']);

        return new ThreadResource($thread);
    }

    /**
     * Display the specified thread.
     */
    public function show($id)
    {
        $thread = Thread::with(['user', 'book', 'comments.user'])
            ->withCount(['comments', 'likes'])
            ->findOrFail($id);

        return new ThreadResource($thread);
    }

    /**
     * Update the specified thread.
     */
    public function update(Request $request, $id)
    {
        $thread = Thread::findOrFail($id);

        // Check if user owns the thread
        if ($thread->user_id !== $request->user()->user_id) {
            return response()->json([
                'message' => 'Unauthorized',
                'message_lv' => 'Nav atļaujas'
            ], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'scope' => 'nullable|string|in:general,page',
            'page_number' => 'nullable|integer|min:1',
        ]);

        $thread->update($validated);
        $thread->load(['user', 'book']);
        $thread->loadCount(['comments', 'likes']);

        return new ThreadResource($thread);
    }

    /**
     * Remove the specified thread.
     */
    public function destroy(Request $request, $id)
    {
        $thread = Thread::findOrFail($id);

        // Check if user owns the thread or is admin
        if ($thread->user_id !== $request->user()->user_id && $request->user()->role !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized',
                'message_lv' => 'Nav atļaujas'
            ], 403);
        }

        $thread->delete();

        return response()->json([
            'message' => 'Thread deleted successfully',
            'message_lv' => 'Diskusija veiksmīgi izdzēsta'
        ]);
    }
}
