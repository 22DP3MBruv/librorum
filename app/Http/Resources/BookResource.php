<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->book_id,
            'title' => $this->title,
            'author' => $this->author,
            'authors' => $this->authors,
            'isbn' => $this->isbn,
            'isbn10' => $this->isbn10,
            'isbn13' => $this->isbn13,
            'publication_year' => $this->publication_year,
            'publish_date' => $this->publish_date,
            'genre' => $this->genre,
            'tag' => $this->tag,
            'page_count' => $this->page_count,
            'description' => $this->description,
            'cover_image_url' => $this->cover_image_url,
            'language' => $this->language,
            'publisher' => $this->publisher,
            'subjects' => $this->subjects,
            'external_ids' => $this->external_ids,
            'last_api_sync' => $this->last_api_sync,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Include relationship counts when available
            'discussions_count' => $this->whenCounted('threads'),
            'readers_count' => $this->whenCounted('readers'),
            
            // Include relationships when loaded
            'discussions' => $this->whenLoaded('threads'),
            'reading_progress' => $this->whenLoaded('readingProgress'),
        ];
    }
}