<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'content',
        'images',
        'poll_options',
        'poll_ends_at',
        'hashtags',
        'likes_count',
        'comments_count',
        'shares_count',
        'is_published',
        'is_featured',
    ];

    protected $casts = [
        'images' => 'array',
        'poll_options' => 'array',
        'hashtags' => 'array',
        'poll_ends_at' => 'datetime',
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
    ];

    /**
     * Get the user who created this post.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all comments for this post.
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get likes for this post.
     */
    public function likes()
    {
        return $this->hasMany(PostLike::class);
    }

    /**
     * Get shares for this post.
     */
    public function shares()
    {
        return $this->hasMany(PostShare::class);
    }

    /**
     * Get poll votes for this post.
     */
    public function pollVotes()
    {
        return $this->hasMany(PollVote::class);
    }

    /**
     * Check if user has liked this post.
     */
    public function isLikedBy($user): bool
    {
        return $user ? PostLike::hasUserLiked($this->id, $user->id) : false;
    }

    /**
     * Check if user has voted on this poll.
     */
    public function hasUserVoted($user): bool
    {
        return $user ? PollVote::hasUserVoted($this->id, $user->id) : false;
    }

    /**
     * Get actual like count from database.
     */
    public function getActualLikesCount(): int
    {
        return $this->likes()->count();
    }

    /**
     * Get actual share count from database.
     */
    public function getActualSharesCount(): int
    {
        return $this->shares()->count();
    }

    /**
     * Check if user can edit this post.
     */
    public function canUserEdit($user): bool
    {
        return $user && ($user->id === $this->user_id || $user->hasRole(['admin', 'super_admin']));
    }

    /**
     * Check if poll is still active.
     */
    public function isPollActive(): bool
    {
        return $this->type === 'poll'
            && $this->poll_ends_at
            && $this->poll_ends_at > now();
    }

    /**
     * Increment likes count.
     */
    public function incrementLikes(): void
    {
        $this->increment('likes_count');
    }

    /**
     * Increment comments count.
     */
    public function incrementComments(): void
    {
        $this->increment('comments_count');
    }

    /**
     * Increment shares count.
     */
    public function incrementShares(): void
    {
        $this->increment('shares_count');
    }

    /**
     * Scope for published posts.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope for featured posts.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for posts by type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for recent posts.
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Search posts by content or hashtags.
     */
    public function scopeSearch($query, $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('content', 'like', "%{$keyword}%")
                ->orWhereJsonContains('hashtags', $keyword);
        });
    }
}
