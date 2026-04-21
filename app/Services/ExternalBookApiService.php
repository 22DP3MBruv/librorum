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
     * Iegūt grāmatas datus no Google Books API pēc ISBN
     */
    public function fetchBookByIsbn($isbn)
    {
        $cleanedIsbn = $this->cleanIsbn($isbn);
        
        if (empty($cleanedIsbn)) {
            Log::warning("ISBN cleaning resulted in empty value for input: {$isbn}");
            return null;
        }
        
        // Vispirms mēģināt ar isbn: prefiksu
        $result = $this->fetchFromGoogleBooks($cleanedIsbn, $cleanedIsbn, true);
        
        // Ja nav atrasts, mēģināt bez isbn: prefiksa kā rezerves opcija
        if (!$result) {
            Log::info("ISBN search with prefix failed for {$cleanedIsbn}, attempting without prefix");
            $result = $this->fetchFromGoogleBooks($cleanedIsbn, $cleanedIsbn, false);
        }
        
        return $result;
    }

    /**
     * Iegūt datus no Google Books API
     */
    private function fetchFromGoogleBooks($isbn, $searchedIsbn = null, $useIsbnPrefix = true)
    {
        try {
            $url = "https://www.googleapis.com/books/v1/volumes";
            
            // Veidot vaicājumu, pamatojoties uz prefiksa preferences
            // Pirmais mēģinājums: izmantot isbn:vērtība formātu (Google Books API standarts)
            // Otrais mēģinājums: izmantot tikai vērtību kā vispārīgu meklēšanu
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
     * Meklēt grāmatas pēc žanra
     */
    public function searchByGenre($genre, $limit = 10)
    {
        $query = "subject:\"{$genre}\"";
        $allItems = [];
        $pageSize = 40; // Google Books API ļauj maksimāli 40 rezultātus vienā pieprasījumā
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

                // Ja atgrieztie rezultāti ir mazāki nekā pieprasītais skaits, tas nozīmē, ka nav vairāk rezultātu
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
     * Normalizēt Google Books datus mūsu formātā
     */
    private function normalizeGoogleBooksData($data, $searchedIsbn = null)
    {
        $volumeInfo = $data['volumeInfo'] ?? [];
        $saleInfo = $data['saleInfo'] ?? [];

        $isbn10 = $this->extractIsbn10FromGoogleBooks($volumeInfo);
        $isbn13 = $this->extractIsbn13FromGoogleBooks($volumeInfo);
        
        // Izmantot ISBN-13 kā primāro, rezerve ISBN-10, tad meklētajam ISBN
        $primaryIsbn = $isbn13 ?? $isbn10 ?? $searchedIsbn;
        
        // Ja ir meklētais ISBN, bet nav konkrēts ISBN-10/13, mēģināt noteikt, kurš tips tas ir
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
     * Iegūt ISBN no Google Books sējuma informācijas
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
     * Iegūt ISBN-10 no Google Books sējuma informācijas
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
     * Iegūt ISBN-13 no Google Books sējuma informācijas
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
     * Iegūt labākās kvalitātes vāka attēlu no Google Books
     * Izmanto pēdējo saiti, jo tai vajadzētu būt labākajai kvalitātei
     */
    private function getBestCoverImage($volumeInfo)
    {
        if (!isset($volumeInfo['imageLinks'])) {
            Log::debug("No imageLinks found in volumeInfo");
            return null;
        }

        $imageLinks = $volumeInfo['imageLinks'];
        Log::debug("Available image sizes: " . implode(', ', array_keys($imageLinks)));
        
        // Izmantot pēdējo saiti, jo tai vajadzētu būt labākajai kvalitātei
        $url = end($imageLinks);
        
        if ($url) {
            // Nodrošināt HTTPS
            $url = str_replace('http://', 'https://', $url);
            
            Log::info("Selected cover image URL length: " . strlen($url));
            
            return $url;
        }

        Log::warning("No image found in imageLinks despite having imageLinks object. Available keys: " . json_encode(array_keys($imageLinks)));
        return null;
    }

    /**
     * Attīrīt un formatēt ISBN
     * Noņem visas ne-burtu-ciparu rakstzīmes (defīses, atstarpes utt.)
     * Saglabā tikai ciparus un X (derīgs ISBN-10 pārbaudes ciparam)
     * Pārvērš uz lielajiem burtiem
     */
    private function cleanIsbn($isbn)
    {
        // Noņemt visas rakstzīmes izņemot ciparus un X (X ir derīgs pārbaudes cipars ISBN-10)
        $cleaned = preg_replace('/[^0-9X]/', '', strtoupper($isbn));
        
        Log::debug("ISBN cleaned from '{$isbn}' to '{$cleaned}'");
        
        return $cleaned;
    }
}