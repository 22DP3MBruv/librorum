<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->comment_id,
            'thread_id' => $this->thread_id,
            'user_id' => $this->user_id,
            'content' => $this->content,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            'author' => $this->when($this->relationLoaded('user'), function () {
                return [
                    'id' => $this->user->user_id,
                    'name' => $this->user->username,
                    'email' => $this->user->email,
                ];
            }),
        ];
    }
}
