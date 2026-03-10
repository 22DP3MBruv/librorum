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
        $cleanedIsbn = $this->cleanIsbn($isbn);
        
        if (empty($cleanedIsbn)) {
            Log::warning("ISBN cleaning resulted in empty value for input: {$isbn}");
            return null;
        }
        
        // Try with isbn: prefix first
        $result = $this->fetchFromGoogleBooks($cleanedIsbn, $cleanedIsbn, true);
        
        // If not found, try without the isbn: prefix as a fallback
        if (!$result) {
            Log::info("ISBN search with prefix failed for {$cleanedIsbn}, attempting without prefix");
            $result = $this->fetchFromGoogleBooks($cleanedIsbn, $cleanedIsbn, false);
        }
        
        return $result;
    }

    /**
     * Fetch from Google Books API
     */
    private function fetchFromGoogleBooks($isbn, $searchedIsbn = null, $useIsbnPrefix = true)
    {
        try {
            $url = "https://www.googleapis.com/books/v1/volumes";
            
            // Build query based on prefix preference
            // First attempt: use isbn:value format (Google Books API standard)
            // Second attempt: use just the value as a general search
            if ($useIsbnPrefix) {
                $query = "isbn:{$isbn}";
                $logQuery = "isbn:{$isbn}";
            } else {
                $query = $isbn;
                $logQuery = $isbn;
            }
            
            $params = [
                'q' => $query,
                'maxResults' => 1
            ];

            if ($this->googleBooksApiKey) {
                $params['key'] = $this->googleBooksApiKey;
            }

            Log::debug("Fetching from Google Books with query: {$logQuery}");
            
            $response = Http::get($url, $params);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['items'][0])) {
                    Log::info("Book found for ISBN: {$isbn}");
                    return $this->normalizeGoogleBooksData($data['items'][0], $searchedIsbn);
                } else {
                    Log::info("No items found for ISBN: {$isbn}");
                }
            } else {
                Log::warning("Google Books API returned status {$response->status()} for ISBN: {$isbn}");
            }
        } catch (Exception $e) {
            Log::warning("Google Books API exception for ISBN {$isbn}: " . $e->getMessage());
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
     * Search books by genre
     */
    public function searchByGenre($genre, $limit = 10)
    {
        try {
            // Use subject: field for category/genre searches
            $query = "subject:\"{$genre}\"";

            $params = [
                'q' => $query,
                'maxResults' => min($limit, 40)
            ];

            if ($this->googleBooksApiKey) {
                $params['key'] = $this->googleBooksApiKey;
            }

            Log::debug("Searching Google Books by genre with query: {$query}");
            
            $response = Http::get('https://www.googleapis.com/books/v1/volumes', $params);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['items'])) {
                    Log::info("Found " . count($data['items']) . " books for genre: {$genre}");
                    return array_map([$this, 'normalizeGoogleBooksData'], $data['items']);
                } else {
                    Log::info("No items found for genre: {$genre}");
                }
            } else {
                Log::warning("Google Books API returned status {$response->status()} for genre: {$genre}");
            }
        } catch (Exception $e) {
            Log::warning("Google Books genre search failed for '{$genre}': " . $e->getMessage());
        }

        return [];
    }


    /**
     * Normalize Google Books data to our format
     */
    private function normalizeGoogleBooksData($data, $searchedIsbn = null)
    {
        $volumeInfo = $data['volumeInfo'] ?? [];
        $saleInfo = $data['saleInfo'] ?? [];

        $isbn10 = $this->extractIsbn10FromGoogleBooks($volumeInfo);
        $isbn13 = $this->extractIsbn13FromGoogleBooks($volumeInfo);
        
        // Use ISBN-13 as primary, fallback to ISBN-10, then to searched ISBN
        $primaryIsbn = $isbn13 ?? $isbn10 ?? $searchedIsbn;
        
        // If we have a searched ISBN but no specific ISBN-10/13, try to determine which type it is
        if (!$isbn10 && !$isbn13 && $searchedIsbn) {
            if (strlen($searchedIsbn) === 10) {
                $isbn10 = $searchedIsbn;
            } elseif (strlen($searchedIsbn) === 13) {
                $isbn13 = $searchedIsbn;
            }
        }

        $coverImage = $this->getBestCoverImage($volumeInfo);
        Log::debug("Cover image for '{$volumeInfo['title']}': " . ($coverImage ? "Found" : "Not found"));

        return [
            'title' => $volumeInfo['title'] ?? null,
            'authors' => $volumeInfo['authors'] ?? [],
            'isbn' => $primaryIsbn,
            'isbn10' => $isbn10,
            'isbn13' => $isbn13,
            'publisher' => $volumeInfo['publisher'] ?? null,
            'publish_date' => $volumeInfo['publishedDate'] ?? null,
            'publication_year' => isset($volumeInfo['publishedDate']) ? (int)substr($volumeInfo['publishedDate'], 0, 4) : null,
            'page_count' => $volumeInfo['pageCount'] ?? null,
            'description' => $volumeInfo['description'] ?? null,
            'cover_image_url' => $coverImage,
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
     * Get the best quality cover image available from Google Books
     * Uses the last link as it should be the best quality
     */
    private function getBestCoverImage($volumeInfo)
    {
        if (!isset($volumeInfo['imageLinks'])) {
            Log::debug("No imageLinks found in volumeInfo");
            return null;
        }

        $imageLinks = $volumeInfo['imageLinks'];
        Log::debug("Available image sizes: " . implode(', ', array_keys($imageLinks)));
        
        // Use the last link as it should be the best quality
        $url = end($imageLinks);
        
        if ($url) {
            // Ensure HTTPS
            $url = str_replace('http://', 'https://', $url);
            
            Log::info("Selected cover image URL length: " . strlen($url));
            
            return $url;
        }

        Log::warning("No image found in imageLinks despite having imageLinks object. Available keys: " . json_encode(array_keys($imageLinks)));
        return null;
    }

    /**
     * Clean and format ISBN
     * Removes all non-alphanumeric characters (hyphens, spaces, etc.)
     * Keeps only digits and X (valid for ISBN-10 check digit)
     * Converts to uppercase
     */
    private function cleanIsbn($isbn)
    {
        // Remove all characters except digits and X (X is valid check digit for ISBN-10)
        $cleaned = preg_replace('/[^0-9X]/', '', strtoupper($isbn));
        
        Log::debug("ISBN cleaned from '{$isbn}' to '{$cleaned}'");
        
        return $cleaned;
    }
}