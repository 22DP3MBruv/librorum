<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Book extends Model
{
    use HasFactory;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'book_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'author',
        'isbn',
        'isbn10',
        'isbn13',
        'cover_image_url',
        'page_count',
        'tag',
        'description',
        'language',
        'publisher',
        'subjects',
        'authors',
        'publish_date',
        'publication_year',
        'genre',
        'external_ids',
        'last_api_sync',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'subjects' => 'array',
        'authors' => 'array',
        'external_ids' => 'array',
        'publish_date' => 'date',
        'last_api_sync' => 'datetime',
        'publication_year' => 'integer',
        'page_count' => 'integer',
    ];

    /**
     * Get the reading progress records for this book.
     */
    public function readingProgress()
    {
        return $this->hasMany(ReadingProgress::class, 'book_id', 'book_id');
    }

    /**
     * Get the threads discussing this book.
     */
    public function threads()
    {
        return $this->hasMany(Thread::class, 'book_id', 'book_id');
    }

    /**
     * Get users who are reading this book.
     */
    public function readers()
    {
        return $this->hasManyThrough(User::class, ReadingProgress::class, 'book_id', 'user_id', 'book_id', 'user_id');
    }
}
