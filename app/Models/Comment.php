<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comment extends Model
{
    use HasFactory;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'comment_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'thread_id',
        'user_id',
        'content',
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
     * Get the thread that owns the comment.
     */
    public function thread()
    {
        return $this->belongsTo(Thread::class, 'thread_id', 'thread_id');
    }

    /**
     * Get the user that made the comment.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get the likes for the comment.
     */
    public function likes()
    {
        return $this->morphMany(Like::class, 'target', 'target_type', 'target_id', 'comment_id')
                    ->where('target_type', 'comment');
    }
}
