<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Thread extends Model
{
    use HasFactory;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'thread_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'book_id',
        'title',
        'content',
        'scope',
        'page_number',
        'chapter_name',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the user that created the thread.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get the book that this thread discusses.
     */
    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id', 'book_id');
    }

    /**
     * Get the comments for the thread.
     */
    public function comments()
    {
        return $this->hasMany(Comment::class, 'thread_id', 'thread_id');
    }

    /**
     * Get the likes for the thread.
     */
    public function likes()
    {
        return $this->morphMany(Like::class, 'target', 'target_type', 'target_id', 'thread_id')
                    ->where('target_type', 'thread');
    }
}
