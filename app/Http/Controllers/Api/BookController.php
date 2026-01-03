<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookResource;
use App\Models\Book;
use App\Services\ExternalBookApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookController extends Controller
{
    /**
     * Get all books with optional filtering and pagination
     */
    public function index(Request $request)
    {
        $query = Book::query();

        // Search by title or author
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%");
            });
        }

        // Filter by tag/category
        if ($request->has('tag')) {
            $query->where('tag', $request->get('tag'));
        }

        // Filter by genre
        if ($request->has('genre')) {
            $query->where('genre', $request->get('genre'));
        }

        // Sort options
        $sortBy = $request->get('sort', 'title');
        $sortOrder = $request->get('order', 'asc');
        
        if (in_array($sortBy, ['title', 'author', 'publication_year', 'created_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Paginate results
        $perPage = min($request->get('per_page', 15), 50); // Max 50 per page
        $books = $query->paginate($perPage);

        return BookResource::collection($books);
    }

    /**
     * Get a specific book by ID or ISBN
     */
    public function show($identifier)
    {
        // Try to find by book_id first, then by ISBN
        $book = Book::where('book_id', $identifier)
                   ->orWhere('isbn', $identifier)
                   ->first();

        if (!$book) {
            return response()->json([
                'message' => 'Book not found',
                'message_lv' => 'Grāmata nav atrasta'
            ], 404);
        }

        return new BookResource($book);
    }

    /**
     * Create a new book (admin only)
     */
    public function store(Request $request)
    {
        // Check if user is admin
        if (!$request->user() || !$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized to add books',
                'message_lv' => 'Nav atļaujas pievienot grāmatas'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'required|string|unique:books,isbn',
            'publication_year' => 'nullable|integer|min:1000|max:' . date('Y'),
            'genre' => 'nullable|string|max:100',
            'tag' => 'nullable|string|max:100',
            'page_count' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
            'cover_image_url' => 'nullable|url',
            'publisher' => 'nullable|string|max:255',
            'language' => 'nullable|string|max:10',
        ], [
            'title.required' => 'Nosaukums ir obligāts',
            'author.required' => 'Autors ir obligāts',
            'isbn.required' => 'ISBN ir obligāts',
            'isbn.unique' => 'Grāmata ar šo ISBN jau eksistē',
            'publication_year.integer' => 'Publikācijas gadam jābūt skaitlim',
            'page_count.integer' => 'Lappušu skaitam jābūt skaitlim',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'message_lv' => 'Validācijas kļūda',
                'errors' => $validator->errors()
            ], 422);
        }

        $book = Book::create($request->validated());

        return new BookResource($book);
    }

    /**
     * Update book information (admin only)
     */
    public function update(Request $request, $id)
    {
        // Check if user is admin
        if (!$request->user() || !$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized to edit books',
                'message_lv' => 'Nav atļaujas rediģēt grāmatas'
            ], 403);
        }

        $book = Book::find($id);
        
        if (!$book) {
            return response()->json([
                'message' => 'Book not found',
                'message_lv' => 'Grāmata nav atrasta'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'author' => 'sometimes|string|max:255',
            'isbn' => 'sometimes|string|unique:books,isbn,' . $book->book_id . ',book_id',
            'publication_year' => 'nullable|integer|min:1000|max:' . date('Y'),
            'genre' => 'nullable|string|max:100',
            'tag' => 'nullable|string|max:100',
            'page_count' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
            'cover_image_url' => 'nullable|url',
            'publisher' => 'nullable|string|max:255',
            'language' => 'nullable|string|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'message_lv' => 'Validācijas kļūda',
                'errors' => $validator->errors()
            ], 422);
        }

        $book->update($request->validated());

        return new BookResource($book);
    }

    /**
     * Delete a book (admin only)
     */
    public function destroy(Request $request, $id)
    {
        // Check if user is admin
        if (!$request->user() || !$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized to delete books',
                'message_lv' => 'Nav atļaujas dzēst grāmatas'
            ], 403);
        }

        $book = Book::find($id);
        
        if (!$book) {
            return response()->json([
                'message' => 'Book not found',
                'message_lv' => 'Grāmata nav atrasta'
            ], 404);
        }

        $book->delete();

        return response()->json([
            'message' => 'Book deleted successfully',
            'message_lv' => 'Grāmata veiksmīgi izdzēsta'
        ]);
    }

    /**
     * Get popular books (most discussed)
     */
    public function popular()
    {
        $books = Book::withCount('threads')
                    ->orderBy('threads_count', 'desc')
                    ->limit(10)
                    ->get();

        return BookResource::collection($books);
    }

    /**
     * Search books with advanced filters
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'q' => 'required|string|min:2',
            'filter' => 'in:title,author,isbn,all',
            'tag' => 'nullable|string',
            'year' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Search parameters are invalid',
                'message_lv' => 'Meklēšanas parametri nav pareizi',
                'errors' => $validator->errors()
            ], 422);
        }

        $query = Book::query();
        $searchTerm = $request->get('q');
        $filter = $request->get('filter', 'all');

        switch ($filter) {
            case 'title':
                $query->where('title', 'like', "%{$searchTerm}%");
                break;
            case 'author':
                $query->where('author', 'like', "%{$searchTerm}%");
                break;
            case 'isbn':
                $query->where('isbn', 'like', "%{$searchTerm}%");
                break;
            default: // 'all'
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('title', 'like', "%{$searchTerm}%")
                      ->orWhere('author', 'like', "%{$searchTerm}%")
                      ->orWhere('isbn', 'like', "%{$searchTerm}%")
                      ->orWhere('description', 'like', "%{$searchTerm}%");
                });
        }

        if ($request->has('tag')) {
            $query->where('tag', $request->get('tag'));
        }

        if ($request->has('year')) {
            $query->where('publication_year', $request->get('year'));
        }

        $books = $query->orderBy('title')->paginate(15);

        return BookResource::collection($books);
    }

    /**
     * Import book from external API by ISBN
     */
    public function importByIsbn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'isbn' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'ISBN is required',
                'message_lv' => 'ISBN ir obligāts',
                'errors' => $validator->errors()
            ], 422);
        }

        $isbn = $request->get('isbn');
        
        // Check if book already exists
        $existingBook = Book::where('isbn', $isbn)
                          ->orWhere('isbn10', $isbn)
                          ->orWhere('isbn13', $isbn)
                          ->first();

        if ($existingBook) {
            return response()->json([
                'message' => 'Book with this ISBN already exists',
                'message_lv' => 'Grāmata ar šo ISBN jau eksistē',
                'book' => new BookResource($existingBook)
            ], 409);
        }

        // Fetch from external API
        $bookApiService = new ExternalBookApiService();
        $bookData = $bookApiService->fetchBookByIsbn($isbn);

        if (!$bookData) {
            return response()->json([
                'message' => 'Book with this ISBN was not found in external databases',
                'message_lv' => 'Grāmata ar šo ISBN nav atrasta ārējās datubāzēs'
            ], 404);
        }

        // Create book with fetched data
        $bookData['last_api_sync'] = now();
        $book = Book::create($bookData);

        return response()->json([
            'message' => 'Book imported successfully',
            'message_lv' => 'Grāmata veiksmīgi importēta',
            'book' => new BookResource($book),
            'source' => $bookData['source'] ?? 'unknown'
        ], 201);
    }

    /**
     * Search external APIs for books
     */
    public function externalSearch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|min:2',
            'author' => 'nullable|string',
            'limit' => 'nullable|integer|min:1|max:20'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Search parameters are invalid',
                'message_lv' => 'Meklēšanas parametri nav pareizi',
                'errors' => $validator->errors()
            ], 422);
        }

        $bookApiService = new ExternalBookApiService();
        $results = $bookApiService->searchBooks(
            $request->get('title'),
            $request->get('author'),
            $request->get('limit', 10)
        );

        return response()->json([
            'results' => $results,
            'count' => count($results)
        ]);
    }

    /**
     * Sync book data with external APIs
     */
    public function syncWithExternalApi(Request $request, $id)
    {
        // Check if user is admin
        if (!$request->user() || !$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized to sync book data',
                'message_lv' => 'Nav atļaujas sinhronizēt grāmatu datus'
            ], 403);
        }

        $book = Book::find($id);
        
        if (!$book) {
            return response()->json([
                'message' => 'Book not found',
                'message_lv' => 'Grāmata nav atrasta'
            ], 404);
        }

        if (!$book->isbn && !$book->isbn10 && !$book->isbn13) {
            return response()->json([
                'message' => 'Book has no ISBN, synchronization is not possible',
                'message_lv' => 'Grāmatai nav ISBN, sinhronizācija nav iespējama'
            ], 422);
        }

        $bookApiService = new ExternalBookApiService();
        $isbn = $book->isbn13 ?: $book->isbn10 ?: $book->isbn;
        $bookData = $bookApiService->fetchBookByIsbn($isbn);

        if (!$bookData) {
            return response()->json([
                'message' => 'Book data not found in external databases',
                'message_lv' => 'Grāmatas dati nav atrasti ārējās datubāzēs'
            ], 404);
        }

        // Update book with fresh data, but preserve user-created content
        $preserveFields = ['tag', 'created_at', 'updated_at'];
        $updateData = array_diff_key($bookData, array_flip($preserveFields));
        $updateData['last_api_sync'] = now();
        
        $book->update($updateData);

        return response()->json([
            'message' => 'Book data synced successfully',
            'message_lv' => 'Grāmatas dati veiksmīgi sinhronizēti',
            'book' => new BookResource($book->fresh()),
            'source' => $bookData['source'] ?? 'unknown'
        ]);
    }
}