<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FollowRequest extends Model
{
    use HasFactory;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'request_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'follower_id',
        'followee_id',
        'status',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who sent the follow request (follower).
     */
    public function follower()
    {
        return $this->belongsTo(User::class, 'follower_id', 'user_id');
    }

    /**
     * Get the user who received the follow request (followee).
     */
    public function followee()
    {
        return $this->belongsTo(User::class, 'followee_id', 'user_id');
    }

    /**
     * Scope to get only pending requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get only accepted requests.
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    /**
     * Scope to get only rejected requests.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Accept the follow request and create the following relationship.
     */
    public function accept()
    {
        $this->status = 'accepted';
        $this->save();

        // Create the following relationship
        $this->follower->following()->attach($this->followee_id);

        // Create accepted notification
        Notification::createFollowRequestAccepted($this->follower, $this->followee);

        return $this;
    }

    /**
     * Reject the follow request.
     */
    public function reject()
    {
        $this->status = 'rejected';
        $this->save();

        return $this;
    }
}
