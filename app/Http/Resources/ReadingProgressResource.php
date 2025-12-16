<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReadingProgressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->progress_id,
            'user_id' => $this->user_id,
            'book_id' => $this->book_id,
            'current_page' => $this->current_page,
            'last_updated' => $this->last_updated,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Include book information when loaded
            'book' => new BookResource($this->whenLoaded('book')),
        ];
    }
}
