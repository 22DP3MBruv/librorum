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

    /**
     * Scope to filter out threads from flagged users and respect privacy settings
     */
    public function scopeVisible($query, $viewer = null)
    {
        return $query->whereHas('user', function($q) use ($viewer) {
            $q->where('is_flagged', false);
            
            // If no viewer, only show public threads
            if (!$viewer) {
                $q->where('activity_visibility', 'public');
            } else {
                // Show threads based on activity visibility
                $q->where(function($query) use ($viewer) {
                    $query->where('activity_visibility', 'public')
                        ->orWhere('user_id', $viewer->user_id)
                        ->orWhere(function($q) use ($viewer) {
                            $q->where('activity_visibility', 'followers')
                                ->whereHas('followers', function($fq) use ($viewer) {
                                    $fq->where('follower_id', $viewer->user_id);
                                });
                        });
                });
            }
        });
    }
}
