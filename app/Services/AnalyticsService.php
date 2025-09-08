<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Media;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Get dashboard statistics
     */
    public function getDashboardStats(): array
    {
        return Cache::remember('dashboard_stats', 300, function () {
            return [
                // Content counts
                'total_posts' => Post::count(),
                'published_posts' => Post::published()->count(),
                'draft_posts' => Post::where('status', 'draft')->count(),
                'total_categories' => Category::count(),
                'active_categories' => Category::where('is_active', true)->count(),
                'total_tags' => Tag::count(),
                'active_tags' => Tag::where('is_active', true)->count(),
                'total_media' => Media::count(),
                'total_comments' => Comment::count(),
                'pending_comments' => Comment::where('status', 'pending')->count(),
                'approved_comments' => Comment::where('status', 'approved')->count(),

                // Monthly stats
                'posts_this_month' => Post::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
                'comments_this_month' => Comment::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),

                // User engagement
                'total_users' => User::count(),
                'users_this_month' => User::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
            ];
        });
    }

    /**
     * Get monthly post publishing statistics for the last 12 months
     */
    public function getMonthlyPostStats(): Collection
    {
        return Cache::remember('monthly_post_stats', 3600, function () {
            $months = collect();

            for ($i = 11; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $monthKey = $date->format('Y-m');
                $monthLabel = $date->format('M Y');

                $postCount = Post::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count();

                $publishedCount = Post::published()
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count();

                $months->push([
                    'month' => $monthKey,
                    'label' => $monthLabel,
                    'total_posts' => $postCount,
                    'published_posts' => $publishedCount,
                    'draft_posts' => $postCount - $publishedCount,
                ]);
            }

            return $months;
        });
    }

    /**
     * Get recent comments pending approval
     */
    public function getRecentPendingComments(int $limit = 10): Collection
    {
        return Comment::with(['post:id,title,slug', 'user:id,name,email'])
            ->where('status', 'pending')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'author_name' => $comment->author_name ?: $comment->user?->name,
                    'author_email' => $comment->author_email ?: $comment->user?->email,
                    'post_title' => $comment->post?->title,
                    'post_slug' => $comment->post?->slug,
                    'created_at' => $comment->created_at,
                    'excerpt' => str($comment->content)->limit(100),
                ];
            });
    }

    /**
     * Get content distribution statistics
     */
    public function getContentDistribution(): array
    {
        return Cache::remember('content_distribution', 1800, function () {
            // Posts by status
            $postsByStatus = Post::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            // Comments by status
            $commentsByStatus = Comment::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            // Posts by category
            $postsByCategory = Category::select('categories.*')
                ->selectRaw('(SELECT COUNT(*) FROM posts WHERE categories.id = posts.category_id) as posts_count')
                ->orderByDesc('posts_count')
                ->limit(10)
                ->get()
                ->filter(function ($category) {
                    return $category->posts_count > 0;
                })
                ->map(function ($category) {
                    return [
                        'name' => $category->name,
                        'count' => $category->posts_count,
                    ];
                })
                ->toArray();

            // Media by type
            $mediaTypes = [];
            $mediaData = Media::select('mime_type', DB::raw('count(*) as count'))
                ->groupBy('mime_type')
                ->orderByDesc('count')
                ->get();

            foreach ($mediaData as $media) {
                $type = explode('/', $media->mime_type)[0] ?? 'other';
                if (! isset($mediaTypes[$type])) {
                    $mediaTypes[$type] = 0;
                }
                $mediaTypes[$type] += (int) $media->count;
            }

            return [
                'posts_by_status' => $postsByStatus,
                'comments_by_status' => $commentsByStatus,
                'posts_by_category' => $postsByCategory,
                'media_by_type' => $mediaTypes,
            ];
        });
    }

    /**
     * Get recent activity feed
     */
    public function getRecentActivity(int $limit = 20): Collection
    {
        $activities = collect();

        // Recent posts
        $recentPosts = Post::with('user:id,name')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($post) {
                return [
                    'type' => 'post',
                    'action' => 'created',
                    'title' => $post->title,
                    'user' => $post->user?->name,
                    'status' => $post->status,
                    'created_at' => $post->created_at,
                    'url' => route('admin.posts.show', $post),
                ];
            });

        // Recent comments
        $recentComments = Comment::with(['post:id,title', 'user:id,name'])
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($comment) {
                try {
                    $url = route('admin.comments.show', $comment);
                } catch (Exception $e) {
                    $url = '#'; // Fallback URL
                }

                return [
                    'type' => 'comment',
                    'action' => 'posted',
                    'title' => "Comment on \"{$comment->post?->title}\"",
                    'user' => $comment->author_name ?: $comment->user?->name,
                    'status' => $comment->status,
                    'created_at' => $comment->created_at,
                    'url' => $url,
                ];
            });

        // Merge and sort by created_at
        $activities = $recentPosts->merge($recentComments)
            ->sortByDesc('created_at')
            ->take($limit)
            ->values();

        return $activities;
    }

    /**
     * Get top performing content
     */
    public function getTopPerformingContent(): array
    {
        return Cache::remember('top_performing_content', 3600, function () {
            // Most viewed posts
            $mostViewedPosts = Post::published()
                ->orderByDesc('views_count')
                ->limit(10)
                ->get(['id', 'title', 'slug', 'views_count'])
                ->map(function ($post) {
                    return [
                        'title' => $post->title,
                        'views' => $post->views_count,
                        'url' => route('admin.posts.show', $post),
                    ];
                })
                ->toArray();

            // Most commented posts
            $mostCommentedPosts = Post::withCount(['comments' => function ($query) {
                $query->where('status', 'approved');
            }])
                ->orderByDesc('comments_count')
                ->limit(10)
                ->get(['id', 'title', 'slug'])
                ->map(function ($post) {
                    return [
                        'title' => $post->title,
                        'comments' => $post->comments_count,
                        'url' => route('admin.posts.show', $post),
                    ];
                })
                ->toArray();

            return [
                'most_viewed_posts' => $mostViewedPosts,
                'most_commented_posts' => $mostCommentedPosts,
            ];
        });
    }

    /**
     * Clear all analytics cache
     */
    public function clearCache(): void
    {
        $cacheKeys = [
            'dashboard_stats',
            'monthly_post_stats',
            'content_distribution',
            'top_performing_content',
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }
}
