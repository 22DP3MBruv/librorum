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
     * Parāda autentificētā lietotāja lasīšanas progresa sarakstu.
     * Opcijas kārtā atbalsta user_id vaicājuma parametru, lai iegūtu citu lietotāju progresu.
     */
    public function index(Request $request)
    {
        $userId = $request->query('user_id');
        
        if ($userId) {
            // Nosauc cita lietotāja lasīšanas progresu
            $progress = ReadingProgress::where('user_id', $userId)
                ->with('book')
                ->orderBy('last_updated', 'desc')
                ->get();
        } else {
            // Nosauc autentificētā lietotāja lasīšanas progresu
            $user = Auth::user();
            $progress = ReadingProgress::where('user_id', $user->user_id)
                ->with('book')
                ->orderBy('last_updated', 'desc')
                ->get();
        }
        
        return ReadingProgressResource::collection($progress);
    }

    /**
     * Saglabā jauni izveidotu lasīšanas progresu.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'book_id' => 'required|exists:books,book_id',
            'current_page' => 'nullable|integer|min:0',
            'status' => 'required|in:want_to_read,reading,completed,dropped',
        ]);

        $user = Auth::user();

        // Pārbauda, vai progress jau eksistē
        $existing = ReadingProgress::where('user_id', $user->user_id)
            ->where('book_id', $validated['book_id'])
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Reading progress already exists for this book',
                'message_lv' => 'Lasīšanas progress šai grāmatai jau eksistē',
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
     * Parāda konkrētu lasīšanas progresu.
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
     * Atjaunina konkrētu lasīšanas progresu.
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
     * Izdzēš konkrēto lasīšanas progresu.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        
        $progress = ReadingProgress::where('progress_id', $id)
            ->where('user_id', $user->user_id)
            ->firstOrFail();

        $progress->delete();

        return response()->json([
            'message' => 'Reading progress deleted successfully',
            'message_lv' => 'Lasīšanas progress veiksmīgi izdzēsts'
        ]);
    }

    /**
     * Dabū lasīšanas progresu konkrētai grāmatai.
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
                'message' => 'No reading progress found for this book',
                'message_lv' => 'Šai grāmatai nav atrasts lasīšanas progress'
            ], 404);
        }

        return new ReadingProgressResource($progress);
    }
}
