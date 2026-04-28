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
     * Get book data from Google Books API by ISBN
     */
    public function fetchBookByIsbn($isbn)
    {
        $cleanedIsbn = $this->cleanIsbn($isbn);
        
        if (empty($cleanedIsbn)) {
            Log::warning("ISBN cleaning resulted in empty value for input: {$isbn}");
            return null;
        }
        
        // First try with isbn: prefix
        $result = $this->fetchFromGoogleBooks($cleanedIsbn, $cleanedIsbn, true);
        
        // If not found, try without isbn: prefix as a fallback option
        if (!$result) {
            Log::info("ISBN search with prefix failed for {$cleanedIsbn}, attempting without prefix");
            $result = $this->fetchFromGoogleBooks($cleanedIsbn, $cleanedIsbn, false);
        }
        
        return $result;
    }

    /**
     * Import book data from Google Books API using either prefixed or non-prefixed ISBN search
     */
    private function fetchFromGoogleBooks($isbn, $searchedIsbn = null, $useIsbnPrefix = true)
    {
        try {
            $url = "https://www.googleapis.com/books/v1/volumes";
            
            // Build query based on prefix preferences
            // First attempt: use isbn:value format (Google Books API standard)
            // Second attempt: use only value as a general search
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
     * Search for books by genre using Google Books API
     */
    public function searchByGenre($genre, $limit = 10)
    {
        $query = "subject:\"{$genre}\"";
        $allItems = [];
        $pageSize = 20; // Google Books API allows up to 40, but it doesn't seem to return more than 20 reliably
        $startIndex = 0;

        try {
            while (count($allItems) < $limit) {
                $fetchCount = min($pageSize, $limit - count($allItems));

                $params = [
                    'q'          => $query,
                    'maxResults' => $fetchCount,
                    'startIndex' => $startIndex,
                ];

                if ($this->googleBooksApiKey) {
                    $params['key'] = $this->googleBooksApiKey;
                }

                Log::debug("Searching Google Books by genre '{$genre}', startIndex={$startIndex}, maxResults={$fetchCount}");

                $response = Http::get('https://www.googleapis.com/books/v1/volumes', $params);

                if (!$response->successful()) {
                    Log::warning("Google Books API returned status {$response->status()} for genre: {$genre}");
                    break;
                }

                $data = $response->json();

                if (empty($data['items'])) {
                    Log::info("No more items found for genre: {$genre} at startIndex={$startIndex}");
                    break;
                }

                $allItems   = array_merge($allItems, $data['items']);
                $startIndex += count($data['items']);

                // If the returned results are fewer than the requested number, it means there are no more results
                if (count($data['items']) < $fetchCount) {
                    break;
                }
            }
        } catch (Exception $e) {
            Log::warning("Google Books genre search failed for '{$genre}': " . $e->getMessage());
        }

        Log::info("Found " . count($allItems) . " books for genre: {$genre}");
        return array_map([$this, 'normalizeGoogleBooksData'], array_slice($allItems, 0, $limit));
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
        
        // Use ISBN-13 as primary, fallback to ISBN-10, then searched ISBN
        $primaryIsbn = $isbn13 ?? $isbn10 ?? $searchedIsbn;
        
        // If there is a searched ISBN but no specific ISBN-10/13, try to determine which type it is
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
     * Get ISBN from Google Books volume information
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
     * Get ISBN-10 from Google Books volume information
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
     * Get ISBN-13 from Google Books volume information
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
     * Get the best quality cover image from Google Books
     * Uses the last link as it should have the best quality
     */
    private function getBestCoverImage($volumeInfo)
    {
        if (!isset($volumeInfo['imageLinks'])) {
            Log::debug("No imageLinks found in volumeInfo");
            return null;
        }

        $imageLinks = $volumeInfo['imageLinks'];
        Log::debug("Available image sizes: " . implode(', ', array_keys($imageLinks)));
        
        // Use the last link as it should have the best quality
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
        // Remove all characters except digits and X (X is valid for ISBN-10 check digit)
        $cleaned = preg_replace('/[^0-9X]/', '', strtoupper($isbn));
        
        Log::debug("ISBN cleaned from '{$isbn}' to '{$cleaned}'");
        
        return $cleaned;
    }
}