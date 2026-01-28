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
        'profile_visibility',
        'reading_progress_visibility',
        'activity_visibility',
        'allow_follows',
        'require_follow_approval',
        'is_flagged',
        'flagged_at',
        'flag_reason',
        'flagged_by',
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
            'allow_follows' => 'boolean',
            'require_follow_approval' => 'boolean',
            'is_flagged' => 'boolean',
            'flagged_at' => 'datetime',
        ];
    }

    /**
     * Get the attributes with default values.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'profile_visibility' => 'public',
        'reading_progress_visibility' => 'public',
        'activity_visibility' => 'public',
        'allow_follows' => true,
        'require_follow_approval' => false,
    ];

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

    /**
     * Check if a viewer can see this user's profile
     */
    public function canViewProfile($viewer): bool
    {
        if (!$viewer) {
            return $this->profile_visibility === 'public';
        }

        if ($viewer->user_id === $this->user_id) {
            return true;
        }

        if ($this->profile_visibility === 'public') {
            return true;
        }

        if ($this->profile_visibility === 'followers') {
            return $this->followers()->where('follower_id', $viewer->user_id)->exists();
        }

        return false; // private
    }

    /**
     * Check if a viewer can see this user's reading progress
     */
    public function canViewReadingProgress($viewer): bool
    {
        if (!$viewer) {
            return $this->reading_progress_visibility === 'public';
        }

        if ($viewer->user_id === $this->user_id) {
            return true;
        }

        if ($this->reading_progress_visibility === 'public') {
            return true;
        }

        if ($this->reading_progress_visibility === 'followers') {
            return $this->followers()->where('follower_id', $viewer->user_id)->exists();
        }

        return false; // private
    }

    /**
     * Check if a viewer can see this user's activity (threads/comments)
     */
    public function canViewActivity($viewer): bool
    {
        if (!$viewer) {
            return $this->activity_visibility === 'public';
        }

        if ($viewer->user_id === $this->user_id) {
            return true;
        }

        if ($this->activity_visibility === 'public') {
            return true;
        }

        if ($this->activity_visibility === 'followers') {
            return $this->followers()->where('follower_id', $viewer->user_id)->exists();
        }

        return false; // private
    }

    /**
     * Check if a user can follow this user
     */
    public function canBeFollowed(): bool
    {
        return $this->allow_follows;
    }

    /**
     * Get the moderator who flagged this user
     */
    public function flaggedBy()
    {
        return $this->belongsTo(User::class, 'flagged_by', 'user_id');
    }

    /**
     * Check if the user is a moderator
     */
    public function isModerator(): bool
    {
        return$this->role === 'admin';
    }

    /**
     * Scope to get only unflagged users
     */
    public function scopeUnflagged($query)
    {
        return $query->where('is_flagged', false);
    }

    /**
     * Scope to get only flagged users
     */
    public function scopeFlagged($query)
    {
        return $query->where('is_flagged', true);
    }
}
