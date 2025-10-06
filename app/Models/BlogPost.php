<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BlogPostStatus;
use App\Traits\HasReviewable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    /** @use HasFactory<\Database\Factories\BlogPostFactory> */
    use HasFactory, HasReviewable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image_path',
        'meta_title',
        'meta_description',
        'author_id',
        'category_id',
        'status',
        'is_featured',
        'views_count',
        'published_at',
    ];

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = [
        'featured_image_url',
        'formatted_published_date',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'status' => BlogPostStatus::class,
            'is_featured' => 'boolean',
            'views_count' => 'integer',
            'published_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }

            // Auto-publish if status is published and no published_at is set
            if ($post->status === 'published' && ! $post->published_at) {
                $post->published_at = now();
            }
        });

    /**
     * Get the author of the blog post.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get the category of the blog post.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    /**
     * Get the tags for the blog post.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(BlogTag::class, 'blog_post_tags', 'blog_post_id', 'blog_tag_id')
            ->withTimestamps();
    }

    /**
     * Get the comments for the blog post.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(BlogComment::class)->with('user', 'replies');
    }

    /**
     * Get only approved comments for the blog post.
     */
    public function approvedComments(): HasMany
    {
        return $this->comments()->approved()->topLevel()->latest();
    }

    /**
     * Get the edit requests for this blog post.
     */
    public function editRequests(): HasMany
    {
        return $this->hasMany(BlogEditRequest::class)->latest();
    }

    /**
     * Get pending edit requests for this blog post.
     */
    public function pendingEditRequests(): HasMany
    {
        return $this->editRequests()->pending();
    }

    /**
     * Scope a query to only include published posts.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope a query to only include featured posts.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope a query to only include draft posts.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeInCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope a query to order by published date.
     */
    public function scopeLatest($query)
    {
        return $query->orderByDesc('published_at');
    }

    /**
     * Scope to filter blog posts based on user permissions.
     */
    public function scopeForUser($query, $user = null)
    {
        if (! $user) {
            return $query->published();
        }

        // If user has permission to view all blog posts, return all
        if ($user->can('blog.view-all')) {
            return $query;
        }

        // If user has permission to view own blog posts (agent), filter by author_id
        if ($user->can('blog.view-own')) {
            return $query->where('author_id', $user->id);
        }

        // For regular users, only show published blog posts
        return $query->published();
    }

    /**
     * Increment the views count.
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    /**
     * Get reading time estimate.
     */
    public function getReadingTimeAttribute(): int
    {
        $wordCount = str_word_count(strip_tags($this->content));

        return max(1, round($wordCount / 200)); // Average reading speed: 200 words per minute
    }

    /**
     * Get excerpt or generate from content.
     */
    public function getExcerptAttribute($value): string
    {
        if ($value) {
            return $value;
        }

        return Str::limit(strip_tags($this->content), 200);
    }

    /**
     * Get meta title or fallback to title.
     */
    public function getMetaTitleAttribute($value): string
    {
        return $value ?: $this->title;
    }

    /**
     * Get meta description or generate from excerpt.
     */
    public function getMetaDescriptionAttribute($value): ?string
    {
        if ($value) {
            return $value;
        }

        return Str::limit($this->excerpt, 160);
    }

    /**
     * Check if the post is published.
     */
    public function isPublished(): bool
    {
        return $this->status === BlogPostStatus::PUBLISHED
               && $this->published_at
               && $this->published_at <= now();
    }

    /**
     * Get formatted published date.
     */
    public function getFormattedPublishedDateAttribute(): ?string
    {
        return $this->published_at?->format('M j, Y');
    }

    /**
     * Get the full URL for the featured image.
     */
    public function getFeaturedImageUrlAttribute(): ?string
    {
        return $this->featured_image_path
            ? Storage::url($this->featured_image_path)
            : 'https://ui-avatars.com/api/?name='.urlencode($this->title ?: 'Blog Post').'&color=7F9CF5&background=EBF4FF';
    }
}
