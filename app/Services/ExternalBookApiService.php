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
        return $this->fetchFromGoogleBooks($cleanedIsbn, $cleanedIsbn);
    }

    /**
     * Fetch from Google Books API
     */
    private function fetchFromGoogleBooks($isbn, $searchedIsbn = null)
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
                    return $this->normalizeGoogleBooksData($data['items'][0], $searchedIsbn);
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
            'cover_image_url' => $this->getBestCoverImage($volumeInfo),
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
     * Priority: extraLarge > large > medium > thumbnail > smallThumbnail
     */
    private function getBestCoverImage($volumeInfo)
    {
        if (!isset($volumeInfo['imageLinks'])) {
            return null;
        }

        $imageLinks = $volumeInfo['imageLinks'];
        
        // Priority order for image quality
        $priorityOrder = ['extraLarge', 'large', 'medium', 'thumbnail', 'smallThumbnail'];
        
        foreach ($priorityOrder as $size) {
            if (isset($imageLinks[$size])) {
                // Replace zoom parameter to get higher quality
                $url = $imageLinks[$size];
                // Google Books thumbnails have a zoom parameter, increase it for better quality
                $url = str_replace('zoom=1', 'zoom=2', $url);
                $url = str_replace('http://', 'https://', $url); // Ensure HTTPS
                
                // Add edge=curl parameter to help with hotlink protection
                if (strpos($url, '?') !== false) {
                    $url .= '&edge=curl';
                } else {
                    $url .= '?edge=curl';
                }
                
                return $url;
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