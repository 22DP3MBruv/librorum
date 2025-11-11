<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReadingProgress extends Model
{
    use HasFactory;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'progress_id';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'reading_progress';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'book_id',
        'current_page',
        'last_updated',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_updated' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the reading progress.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get the book that this progress is for.
     */
    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id', 'book_id');
    }
}
