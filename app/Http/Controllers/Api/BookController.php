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
     * Dabū grāmatu sarakstu ar meklēšanas, filtrēšanas un kārtošanas iespējām
     */
    public function index(Request $request)
    {
        $query = Book::query();

        // Meklēšana pēc nosaukuma, autora vai ISBN
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%");
            });
        }

        // Filtrēšana pēc žanra
        if ($request->has('genre')) {
            $query->where('genre', $request->get('genre'));
        }

        // Filtrēšana pēc ISBN (ieskaitot isbn10 un isbn13)
        if ($request->has('isbn')) {
            $isbn = $request->get('isbn');
            $query->where(function ($q) use ($isbn) {
                $q->where('isbn', $isbn)
                  ->orWhere('isbn10', $isbn)
                  ->orWhere('isbn13', $isbn);
            });
        }

        // Kārtošana
        $sortBy = $request->get('sort', 'title');
        $sortOrder = $request->get('order', 'asc');
        
        if (in_array($sortBy, ['title', 'author', 'publication_year', 'created_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Paginācija
        $perPage = min($request->get('per_page', 15), 50); // Max 50 per page
        $books = $query->paginate($perPage);

        return BookResource::collection($books);
    }

    /**
     * Dabū konkrētu grāmatu pēc ID vai ISBN
     */
    public function show($identifier)
    {
        // Mēģināt atrast pēc book_id, pēc tam pēc ISBN
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
     * Izveido jaunu grāmatu (admin only)
     */
    public function store(Request $request)
    {
        // Pārbauda, vai lietotājs ir admin
        if (!$request->user() || !$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized to add books',
                'message_lv' => 'Nav atļaujas pievienot grāmatas'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'authors' => 'nullable|array',
            'isbn' => 'required|string|unique:books,isbn',
            'isbn10' => 'nullable|string',
            'isbn13' => 'nullable|string',
            'publication_year' => 'nullable|integer|min:1000|max:' . date('Y'),
            'genre' => 'nullable|string|max:100',
            'page_count' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
            'cover_image_url' => 'nullable|url',
            'publisher' => 'nullable|string|max:255',
            'language' => 'nullable|string|max:10',
            'subjects' => 'nullable|array',
            'external_ids' => 'nullable|array',
            'publish_date' => 'nullable|date',
            'last_api_sync' => 'nullable|date',
        ], [
            'title.required' => 'Nosaukums ir obligāts',
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

        $book = Book::create($validator->validated());

        return new BookResource($book);
    }

    /**
     * Atjaunina grāmatu (admin only)
     */
    public function update(Request $request, $id)
    {
        // Pārbauda, vai lietotājs ir admin
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
            'authors' => 'sometimes|array',
            'isbn' => 'sometimes|string|unique:books,isbn,' . $book->book_id . ',book_id',
            'isbn10' => 'nullable|string',
            'isbn13' => 'nullable|string',
            'publication_year' => 'nullable|integer|min:1000|max:' . date('Y'),
            'genre' => 'nullable|string|max:100',
            'page_count' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
            'cover_image_url' => 'nullable|url',
            'publisher' => 'nullable|string|max:255',
            'language' => 'nullable|string|max:10',
            'subjects' => 'nullable|array',
            'external_ids' => 'nullable|array',
            'publish_date' => 'nullable|date',
            'last_api_sync' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'message_lv' => 'Validācijas kļūda',
                'errors' => $validator->errors()
            ], 422);
        }

        $book->update($validator->validated());

        return new BookResource($book);
    }

    /**
     * Izdzēš grāmatu (admin only)
     */
    public function destroy(Request $request, $id)
    {
        // Pārbauda, vai lietotājs ir admin
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
     * Meklē grāmatas pēc nosaukuma, autora, ISBN vai visu lauku kombinācijas, ar papildu filtriem un kārtošanu
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'q' => 'required|string|min:2',
            'filter' => 'in:title,author,isbn,all',
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

        if ($request->has('year')) {
            $query->where('publication_year', $request->get('year'));
        }

        $books = $query->orderBy('title')->paginate(15);

        return BookResource::collection($books);
    }

    /**
     * Importē grāmatu pēc ISBN no ārējām datubāzēm (admin only)
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
        
        // Pārbauda, grāmata ar šo ISBN jau eksistē
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

        // Dabū grāmatas datus no ārējām datubāzēm
        $bookApiService = new ExternalBookApiService();
        $bookData = $bookApiService->fetchBookByIsbn($isbn);

        if (!$bookData) {
            return response()->json([
                'message' => 'Book with this ISBN was not found in external databases',
                'message_lv' => 'Grāmata ar šo ISBN nav atrasta ārējās datubāzēs'
            ], 404);
        }

        // Validē, vai ir vismaz viens derīgs ISBN (isbn10 vai isbn13)
        if (empty($bookData['isbn']) && empty($bookData['isbn10']) && empty($bookData['isbn13'])) {
            return response()->json([
                'message' => 'Book found but has no valid ISBN identifiers',
                'message_lv' => 'Grāmata atrasta, bet tai nav derīgu ISBN identifikatoru'
            ], 422);
        }

        
    
        $bookData['last_api_sync'] = now();
        
        try {
            $book = Book::create($bookData);
        } catch (\Exception $e) {
            \Log::error('Book creation failed: ' . $e->getMessage(), ['bookData' => $bookData]);
            return response()->json([
                'message' => 'Failed to create book: ' . $e->getMessage(),
                'message_lv' => 'Neizdevās izveidot grāmatu',
                'error' => $e->getMessage(),
                'book_data' => $bookData
            ], 422);
        }

        return response()->json([
            'message' => 'Book imported successfully',
            'message_lv' => 'Grāmata veiksmīgi importēta',
            'book' => new BookResource($book),
            'source' => $source
        ], 201);
    }

    /**
     * Importē grāmatas pēc žanra no ārējām datubāzēm (admin only)
     */
    public function batchImportByGenre(Request $request)
    {
        // Pārbauda, vai lietotājs ir admin
        if (!$request->user() || !$request->user()->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized to import books',
                'message_lv' => 'Nav atļaujas importēt grāmatas'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'genre' => 'required|string|min:2|max:100',
            'limit' => 'required|integer|min:1|max:40'
        ], [
            'genre.required' => 'Žanrs ir obligāts',
            'genre.min' => 'Žanrs jābūt vismaz 2 rakstzīmes garams',
            'limit.required' => 'Grāmatu skaits ir obligāts',
            'limit.min' => 'Grāmatu skaitam jābūt vismaz 1',
            'limit.max' => 'Grāmatu skaits nevar pārsniegt 40'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'message_lv' => 'Validācijas kļūda',
                'errors' => $validator->errors()
            ], 422);
        }

        $genre = $request->get('genre');
        $limit = $request->get('limit');

        $bookApiService = new ExternalBookApiService();
        $booksData = $bookApiService->searchByGenre($genre, $limit);

        if (empty($booksData)) {
            return response()->json([
                'message' => 'No books found for the specified genre',
                'message_lv' => 'Grāmatas norādītajam žanram nav atrastas'
            ], 404);
        }

        $imported = [];
        $skipped = [];
        $failed = [];

        foreach ($booksData as $bookData) {
            try {
                // Izmet grāmatu, ja tai nav derīga ISBN (nevar importēt bez ISBN, jo tas ir galvenais identifikators)
                if (empty($bookData['isbn']) && empty($bookData['isbn10']) && empty($bookData['isbn13'])) {
                    $skipped[] = [
                        'title' => $bookData['title'] ?? 'Unknown',
                        'reason' => 'No valid ISBN found'
                    ];
                    continue;
                }

                // Pārbauda, vai grāmata ar šo ISBN jau eksistē datubāzē (lai izvairītos no dublikātiem)
                $existingBook = Book::where('isbn', $bookData['isbn'] ?? null)
                                  ->orWhere('isbn10', $bookData['isbn10'] ?? null)
                                  ->orWhere('isbn13', $bookData['isbn13'] ?? null)
                                  ->first();

                if ($existingBook) {
                    $skipped[] = [
                        'title' => $bookData['title'] ?? 'Unknown',
                        'reason' => 'Book already exists in database'
                    ];
                    continue;
                }

                // Pievieno metadata
                $bookData['last_api_sync'] = now();
                $source = $bookData['source'] ?? 'unknown';
                unset($bookData['source']);

                // Izveido grāmatu datubāzē
                $book = Book::create($bookData);
                
                $imported[] = [
                    'book_id' => $book->book_id,
                    'title' => $book->title,
                    'isbn' => $book->isbn,
                    'source' => $source
                ];

            } catch (\Exception $e) {
                \Log::error('Batch import - Book creation failed: ' . $e->getMessage(), [
                    'title' => $bookData['title'] ?? 'Unknown',
                    'isbn' => $bookData['isbn'] ?? 'Unknown'
                ]);
                
                $failed[] = [
                    'title' => $bookData['title'] ?? 'Unknown',
                    'reason' => 'Failed to create book: ' . $e->getMessage()
                ];
            }
        }

        return response()->json([
            'message' => 'Batch import completed',
            'message_lv' => 'Partijas importēšana pabeigta',
            'genre' => $genre,
            'summary' => [
                'requested' => $limit,
                'total_processed' => count($booksData),
                'imported' => count($imported),
                'skipped' => count($skipped),
                'failed' => count($failed)
            ],
            'imported' => $imported,
            'skipped' => $skipped,
            'failed' => $failed
        ], 201);
    }

    /**
     * Sinhronizē grāmatas datus ar ārējām datubāzēm pēc ISBN (admin only)
     */
    public function syncWithExternalApi(Request $request, $id)
    {
        // Pārbauda, vai lietotājs ir admin
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

        // Saglabā esošos laukus, kurus nevēlamies pārrakstīt ar API datiem
        $preserveFields = ['created_at', 'updated_at'];
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