<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'notification_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'actor_id',
        'type',
        'message',
        'related_id',
        'related_type',
        'is_read',
        'read_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who receives this notification.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get the user who triggered this notification (actor).
     */
    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id', 'user_id');
    }

    /**
     * Get the related model (polymorphic).
     */
    public function related()
    {
        return $this->morphTo('related', 'related_type', 'related_id');
    }

    /**
     * Scope to get only unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope to get only read notifications.
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Mark notification as unread.
     */
    public function markAsUnread()
    {
        $this->update([
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    /**
     * Create a notification for comment reply.
     */
    public static function createCommentReply($comment, $actor)
    {
        // Get the parent comment's author
        $parentComment = Comment::find($comment->parent_comment_id);
        if (!$parentComment || $parentComment->user_id === $actor->user_id) {
            return null; // Don't notify if replying to yourself
        }

        return self::create([
            'user_id' => $parentComment->user_id,
            'actor_id' => $actor->user_id,
            'type' => 'comment_reply',
            'message' => "{$actor->username} replied to your comment",
            'related_id' => $comment->comment_id,
            'related_type' => 'App\Models\Comment',
        ]);
    }

    /**
     * Create a notification for thread reply.
     */
    public static function createThreadReply($comment, $thread, $actor)
    {
        if ($thread->user_id === $actor->user_id) {
            return null; // Don't notify if commenting on your own thread
        }

        return self::create([
            'user_id' => $thread->user_id,
            'actor_id' => $actor->user_id,
            'type' => 'thread_reply',
            'message' => "{$actor->username} commented on your thread: {$thread->title}",
            'related_id' => $comment->comment_id,
            'related_type' => 'App\Models\Comment',
        ]);
    }

    /**
     * Create a notification for like.
     */
    public static function createLike($like, $likeable, $actor)
    {
        if ($likeable->user_id === $actor->user_id) {
            return null; // Don't notify if liking your own content
        }

        $type = $likeable instanceof Thread ? 'thread_like' : 'comment_like';
        $contentType = $likeable instanceof Thread ? 'thread' : 'comment';

        return self::create([
            'user_id' => $likeable->user_id,
            'actor_id' => $actor->user_id,
            'type' => $type,
            'message' => "{$actor->username} liked your {$contentType}",
            'related_id' => $likeable->getKey(),
            'related_type' => get_class($likeable),
        ]);
    }

    /**
     * Create a notification for new follower.
     */
    public static function createNewFollower($followedUser, $follower)
    {
        return self::create([
            'user_id' => $followedUser->user_id,
            'actor_id' => $follower->user_id,
            'type' => 'new_follower',
            'message' => "{$follower->username} started following you",
            'related_id' => $followedUser->user_id,
            'related_type' => 'App\Models\User',
        ]);
    }

    /**
     * Create a notification for follow request.
     */
    public static function createFollowRequest($followedUser, $follower)
    {
        return self::create([
            'user_id' => $followedUser->user_id,
            'actor_id' => $follower->user_id,
            'type' => 'follow_request',
            'message' => "{$follower->username} wants to follow you",
            'related_id' => $followedUser->user_id,
            'related_type' => 'App\Models\User',
        ]);
    }

    /**
     * Create a notification for content moderation.
     */
    public static function createContentModerated($contentOwner, $moderator, $content, $action, $reason = null)
    {
        $contentType = class_basename(get_class($content));
        $message = "Your {$contentType} was {$action} by a moderator";
        if ($reason) {
            $message .= ": {$reason}";
        }

        return self::create([
            'user_id' => $contentOwner->user_id,
            'actor_id' => $moderator->user_id,
            'type' => 'content_moderated',
            'message' => $message,
            'related_id' => $content->getKey(),
            'related_type' => get_class($content),
        ]);
    }

    /**
     * Create a notification for user being banned (flagged).
     */
    public static function createUserBanned($user, $moderator, $reason)
    {
        $message = "Your account has been banned by a moderator";
        if ($reason) {
            $message .= ": {$reason}";
        }

        return self::create([
            'user_id' => $user->user_id,
            'actor_id' => $moderator->user_id,
            'type' => 'user_banned',
            'message' => $message,
            'related_id' => $user->user_id,
            'related_type' => 'App\Models\User',
        ]);
    }

    /**
     * Create a notification for follow request accepted.
     */
    public static function createFollowRequestAccepted($follower, $followee)
    {
        return self::create([
            'user_id' => $follower->user_id,
            'actor_id' => $followee->user_id,
            'type' => 'follow_request_accepted',
            'message' => "{$followee->username} accepted your follow request",
            'related_id' => $followee->user_id,
            'related_type' => 'App\Models\User',
        ]);
    }
}
