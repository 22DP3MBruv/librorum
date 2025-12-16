<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReadingProgressResource;
use App\Models\ReadingProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReadingProgressController extends Controller
{
    /**
     * Display a listing of the authenticated user's reading progress.
     */
    public function index()
    {
        $user = Auth::user();
        
        $progress = ReadingProgress::where('user_id', $user->user_id)
            ->with('book')
            ->orderBy('last_updated', 'desc')
            ->get();
        
        return ReadingProgressResource::collection($progress);
    }

    /**
     * Store a newly created reading progress in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'book_id' => 'required|exists:books,book_id',
            'current_page' => 'nullable|integer|min:0',
            'status' => 'required|in:want_to_read,reading,completed,dropped',
        ]);

        $user = Auth::user();

        // Check if progress already exists
        $existing = ReadingProgress::where('user_id', $user->user_id)
            ->where('book_id', $validated['book_id'])
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Reading progress already exists for this book',
                'data' => new ReadingProgressResource($existing)
            ], 409);
        }

        $progress = ReadingProgress::create([
            'user_id' => $user->user_id,
            'book_id' => $validated['book_id'],
            'current_page' => $validated['current_page'] ?? 0,
            'status' => $validated['status'],
            'last_updated' => now(),
        ]);

        $progress->load('book');

        return new ReadingProgressResource($progress);
    }

    /**
     * Display the specified reading progress.
     */
    public function show($id)
    {
        $user = Auth::user();
        
        $progress = ReadingProgress::where('progress_id', $id)
            ->where('user_id', $user->user_id)
            ->with('book')
            ->firstOrFail();
        
        return new ReadingProgressResource($progress);
    }

    /**
     * Update the specified reading progress in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'current_page' => 'nullable|integer|min:0',
            'status' => 'nullable|in:want_to_read,reading,completed,dropped',
        ]);

        $user = Auth::user();
        
        $progress = ReadingProgress::where('progress_id', $id)
            ->where('user_id', $user->user_id)
            ->firstOrFail();

        $progress->update([
            'current_page' => $validated['current_page'] ?? $progress->current_page,
            'status' => $validated['status'] ?? $progress->status,
            'last_updated' => now(),
        ]);

        $progress->load('book');

        return new ReadingProgressResource($progress);
    }

    /**
     * Remove the specified reading progress from storage.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        
        $progress = ReadingProgress::where('progress_id', $id)
            ->where('user_id', $user->user_id)
            ->firstOrFail();

        $progress->delete();

        return response()->json([
            'message' => 'Reading progress deleted successfully'
        ]);
    }

    /**
     * Get reading progress for a specific book.
     */
    public function getByBook($bookId)
    {
        $user = Auth::user();
        
        $progress = ReadingProgress::where('user_id', $user->user_id)
            ->where('book_id', $bookId)
            ->with('book')
            ->first();

        if (!$progress) {
            return response()->json([
                'message' => 'No reading progress found for this book'
            ], 404);
        }

        return new ReadingProgressResource($progress);
    }
}
