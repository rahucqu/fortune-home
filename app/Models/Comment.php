<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'author_name',
        'author_email',
        'author_website',
        'status',
        'approved_at',
        'approved_by',
        'post_id',
        'user_id',
        'parent_id',
        'ip_address',
        'user_agent',
        'metadata',
        'likes_count',
        'replies_count',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'metadata' => 'array',
            'likes_count' => 'integer',
            'replies_count' => 'integer',
            'is_featured' => 'boolean',
        ];
    }

    // Relationships
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    public function approvedReplies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')->approved();
    }

    // Accessors
    public function getAuthorDisplayNameAttribute(): string
    {
        if ($this->user) {
            return $this->user->name;
        }

        return $this->author_name ?? 'Anonymous';
    }

    public function getAuthorDisplayEmailAttribute(): ?string
    {
        if ($this->user) {
            return $this->user->email;
        }

        return $this->author_email;
    }

    public function getIsGuestAttribute(): bool
    {
        return is_null($this->user_id);
    }

    public function getIsReplyAttribute(): bool
    {
        return !is_null($this->parent_id);
    }

    public function getIsApprovedAttribute(): bool
    {
        return $this->status === 'approved';
    }

    public function getIsPendingAttribute(): bool
    {
        return $this->status === 'pending';
    }

    public function getIsRejectedAttribute(): bool
    {
        return $this->status === 'rejected';
    }

    public function getIsSpamAttribute(): bool
    {
        return $this->status === 'spam';
    }

    public function getDepthAttribute(): int
    {
        $depth = 0;
        $parent = $this->parent;
        
        while ($parent) {
            $depth++;
            $parent = $parent->parent;
        }
        
        return $depth;
    }

    // Scopes
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', 'rejected');
    }

    public function scopeSpam(Builder $query): Builder
    {
        return $query->where('status', 'spam');
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeByPost(Builder $query, int $postId): Builder
    {
        return $query->where('post_id', $postId);
    }

    public function scopeTopLevel(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeReplies(Builder $query): Builder
    {
        return $query->whereNotNull('parent_id');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeFromRegisteredUsers(Builder $query): Builder
    {
        return $query->whereNotNull('user_id');
    }

    public function scopeFromGuests(Builder $query): Builder
    {
        return $query->whereNull('user_id');
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('content', 'like', "%{$search}%")
              ->orWhere('author_name', 'like', "%{$search}%")
              ->orWhere('author_email', 'like', "%{$search}%");
        });
    }

    public function scopeRecent(Builder $query, int $limit = 10): Builder
    {
        return $query->latest()->limit($limit);
    }

    // Helper methods
    public function approve(): void
    {
        $this->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        // Update post comment count
        $this->post->increment('comments_count');
        
        // Update parent reply count if this is a reply
        if ($this->parent_id) {
            $this->parent->increment('replies_count');
        }
    }

    public function reject(): void
    {
        $wasApproved = $this->status === 'approved';
        
        $this->update([
            'status' => 'rejected',
            'approved_at' => null,
            'approved_by' => null,
        ]);

        // Update counters if it was previously approved
        if ($wasApproved) {
            $this->post->decrement('comments_count');
            
            if ($this->parent_id) {
                $this->parent->decrement('replies_count');
            }
        }
    }

    public function markAsSpam(): void
    {
        $wasApproved = $this->status === 'approved';
        
        $this->update([
            'status' => 'spam',
            'approved_at' => null,
            'approved_by' => null,
        ]);

        // Update counters if it was previously approved
        if ($wasApproved) {
            $this->post->decrement('comments_count');
            
            if ($this->parent_id) {
                $this->parent->decrement('replies_count');
            }
        }
    }

    public function toggleFeatured(): void
    {
        $this->update(['is_featured' => !$this->is_featured]);
    }

    public function incrementLikes(): void
    {
        $this->increment('likes_count');
    }

    public function decrementLikes(): void
    {
        $this->decrement('likes_count');
    }

    // Boot method for automatic updates
    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function ($comment) {
            // Update post comment count if approved
            if ($comment->status === 'approved') {
                $comment->post->decrement('comments_count');
                
                if ($comment->parent_id) {
                    $comment->parent->decrement('replies_count');
                }
            }
            
            // Delete all replies
            $comment->replies()->delete();
        });
    }
}
