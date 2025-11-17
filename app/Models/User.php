<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'user_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'email',
        'password_hash',
        'role',
        'join_date',
        'name', // Add support for name field
        'password', // Add support for password field
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password_hash' => 'hashed',
            'join_date' => 'datetime',
        ];
    }

    /**
     * Get the reading progress for the user.
     */
    public function readingProgress()
    {
        return $this->hasMany(ReadingProgress::class, 'user_id', 'user_id');
    }

    /**
     * Get the threads created by the user.
     */
    public function threads()
    {
        return $this->hasMany(Thread::class, 'user_id', 'user_id');
    }

    /**
     * Get the comments made by the user.
     */
    public function comments()
    {
        return $this->hasMany(Comment::class, 'user_id', 'user_id');
    }

    /**
     * Get the users that this user is following.
     */
    public function following()
    {
        return $this->belongsToMany(User::class, 'following', 'follower_id', 'followee_id', 'user_id', 'user_id')
                    ->withTimestamps(['created_at']);
    }

    /**
     * Get the users that are following this user.
     */
    public function followers()
    {
        return $this->belongsToMany(User::class, 'following', 'followee_id', 'follower_id', 'user_id', 'user_id')
                    ->withTimestamps(['created_at']);
    }

    /**
     * Get the likes made by the user.
     */
    public function likes()
    {
        return $this->hasMany(Like::class, 'user_id', 'user_id');
    }

    /**
     * Get the name attribute (maps to username for compatibility)
     */
    public function getNameAttribute()
    {
        return $this->username;
    }

    /**
     * Set the name attribute (maps to username for compatibility)
     */
    public function setNameAttribute($value)
    {
        $this->attributes['username'] = $value;
    }

    /**
     * Get the password attribute (maps to password_hash for compatibility)
     */
    public function getPasswordAttribute()
    {
        return $this->password_hash;
    }

    /**
     * Set the password attribute (maps to password_hash for compatibility)
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password_hash'] = $value;
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if the user is a regular user.
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Scope to get only admins.
     */
    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    /**
     * Scope to get only regular users.
     */
    public function scopeRegularUsers($query)
    {
        return $query->where('role', 'user');
    }
}
