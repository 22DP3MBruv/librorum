<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThreadResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->thread_id,
            'user_id' => $this->user_id,
            'book_id' => $this->book_id,
            'title' => $this->title,
            'content' => $this->content,
            'scope' => $this->scope,
            'page_number' => $this->page_number,
            'comments_count' => $this->when(isset($this->comments_count), $this->comments_count),
            'likes_count' => $this->when(isset($this->likes_count), $this->likes_count),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            'author' => $this->when($this->relationLoaded('user'), function () {
                return [
                    'id' => $this->user->user_id,
                    'name' => $this->user->username,
                    'email' => $this->user->email,
                ];
            }),
            'book' => $this->when($this->relationLoaded('book'), function () {
                return [
                    'id' => $this->book->book_id,
                    'title' => $this->book->title,
                    'authors' => $this->book->authors,
                    'isbn' => $this->book->isbn,
                ];
            }),
            'comments' => $this->when(
                $this->relationLoaded('comments'),
                CommentResource::collection($this->comments)
            ),
        ];
    }
}
