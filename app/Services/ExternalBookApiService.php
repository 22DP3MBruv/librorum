<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ExternalBookApiService
{
    private $googleBooksApiKey;

    public function __construct()
    {
        $this->googleBooksApiKey = env('GOOGLE_BOOKS_API_KEY');
    }

    /**
     * Fetch book data from Google Books API by ISBN
     */
    public function fetchBookByIsbn($isbn)
    {
        $isbn = $this->cleanIsbn($isbn);
        return $this->fetchFromGoogleBooks($isbn);
    }

    /**
     * Fetch from Google Books API
     */
    private function fetchFromGoogleBooks($isbn)
    {
        try {
            $url = "https://www.googleapis.com/books/v1/volumes";
            $params = [
                'q' => "isbn:{$isbn}",
                'maxResults' => 1
            ];

            if ($this->googleBooksApiKey) {
                $params['key'] = $this->googleBooksApiKey;
            }

            $response = Http::get($url, $params);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['items'][0])) {
                    return $this->normalizeGoogleBooksData($data['items'][0]);
                }
            }
        } catch (Exception $e) {
            Log::warning("Google Books API failed for ISBN {$isbn}: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Search books by title and/or author
     */
    public function searchBooks($title, $author = null, $limit = 10)
    {
        return $this->searchGoogleBooks($title, $author, $limit);
    }

    /**
     * Search Google Books
     */
    private function searchGoogleBooks($title, $author = null, $limit = 10)
    {
        try {
            $query = "intitle:{$title}";
            if ($author) {
                $query .= "+inauthor:{$author}";
            }

            $params = [
                'q' => $query,
                'maxResults' => $limit
            ];

            if ($this->googleBooksApiKey) {
                $params['key'] = $this->googleBooksApiKey;
            }

            $response = Http::get('https://www.googleapis.com/books/v1/volumes', $params);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['items'])) {
                    return array_map([$this, 'normalizeGoogleBooksData'], $data['items']);
                }
            }
        } catch (Exception $e) {
            Log::warning("Google Books search failed: " . $e->getMessage());
        }

        return [];
    }

    /**
     * Normalize Google Books data to our format
     */
    private function normalizeGoogleBooksData($data)
    {
        $volumeInfo = $data['volumeInfo'] ?? [];
        $saleInfo = $data['saleInfo'] ?? [];

        return [
            'title' => $volumeInfo['title'] ?? null,
            'authors' => $volumeInfo['authors'] ?? [],
            'author' => isset($volumeInfo['authors'][0]) ? $volumeInfo['authors'][0] : null,
            'isbn' => $this->extractIsbnFromGoogleBooks($volumeInfo),
            'isbn10' => $this->extractIsbn10FromGoogleBooks($volumeInfo),
            'isbn13' => $this->extractIsbn13FromGoogleBooks($volumeInfo),
            'publisher' => $volumeInfo['publisher'] ?? null,
            'publish_date' => $volumeInfo['publishedDate'] ?? null,
            'publication_year' => isset($volumeInfo['publishedDate']) ? (int)substr($volumeInfo['publishedDate'], 0, 4) : null,
            'page_count' => $volumeInfo['pageCount'] ?? null,
            'description' => $volumeInfo['description'] ?? null,
            'cover_image_url' => $volumeInfo['imageLinks']['thumbnail'] ?? null,
            'language' => $volumeInfo['language'] ?? 'en',
            'genre' => isset($volumeInfo['categories'][0]) ? $volumeInfo['categories'][0] : null,
            'subjects' => $volumeInfo['categories'] ?? [],
            'external_ids' => [
                'google_books' => $data['id'] ?? null
            ],
            'source' => 'google_books'
        ];
    }

    /**
     * Extract ISBN from Google Books volume info
     */
    private function extractIsbnFromGoogleBooks($volumeInfo)
    {
        if (!isset($volumeInfo['industryIdentifiers'])) {
            return null;
        }

        foreach ($volumeInfo['industryIdentifiers'] as $identifier) {
            if (in_array($identifier['type'], ['ISBN_13', 'ISBN_10'])) {
                return $identifier['identifier'];
            }
        }

        return null;
    }

    /**
     * Extract ISBN-10 from Google Books volume info
     */
    private function extractIsbn10FromGoogleBooks($volumeInfo)
    {
        if (!isset($volumeInfo['industryIdentifiers'])) {
            return null;
        }

        foreach ($volumeInfo['industryIdentifiers'] as $identifier) {
            if ($identifier['type'] === 'ISBN_10') {
                return $identifier['identifier'];
            }
        }

        return null;
    }

    /**
     * Extract ISBN-13 from Google Books volume info
     */
    private function extractIsbn13FromGoogleBooks($volumeInfo)
    {
        if (!isset($volumeInfo['industryIdentifiers'])) {
            return null;
        }

        foreach ($volumeInfo['industryIdentifiers'] as $identifier) {
            if ($identifier['type'] === 'ISBN_13') {
                return $identifier['identifier'];
            }
        }

        return null;
    }

    /**
     * Clean and format ISBN
     */
    private function cleanIsbn($isbn)
    {
        return preg_replace('/[^0-9X]/', '', strtoupper($isbn));
    }
}