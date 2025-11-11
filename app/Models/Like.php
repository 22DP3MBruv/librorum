<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Like extends Model
{
    use HasFactory;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'like_id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'target_type',
        'target_id',
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
        ];
    }

    /**
     * Get the user that made the like.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get the target of the like (polymorphic relationship).
     */
    public function target()
    {
        return $this->morphTo('target', 'target_type', 'target_id');
    }

    /**
     * Get the thread if this is a thread like.
     */
    public function thread()
    {
        return $this->belongsTo(Thread::class, 'target_id', 'thread_id')
                    ->where('target_type', 'thread');
    }

    /**
     * Get the comment if this is a comment like.
     */
    public function comment()
    {
        return $this->belongsTo(Comment::class, 'target_id', 'comment_id')
                    ->where('target_type', 'comment');
    }
}
