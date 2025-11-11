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
        'cover_image_url',
        'page_count',
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
