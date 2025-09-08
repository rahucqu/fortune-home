<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Comment;
use App\Models\Media;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Services\AnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->analyticsService = new AnalyticsService();

    // Create test data
    $this->user = User::factory()->create();
    $this->categories = Category::factory()->count(3)->create(['is_active' => true]);
    $this->tags = Tag::factory()->count(5)->create(['is_active' => true]);

    // Create posts with different statuses
    $this->publishedPosts = Post::factory()
        ->count(5)
        ->published()
        ->for($this->user)
        ->for($this->categories->first(), 'category')
        ->create(['views_count' => fake()->numberBetween(10, 1000)]);

    $this->draftPosts = Post::factory()
        ->count(3)
        ->for($this->user)
        ->for($this->categories->get(1), 'category')
        ->create(['status' => 'draft']);

    // Create comments with different statuses
    $this->approvedComments = Comment::factory()
        ->count(8)
        ->for($this->publishedPosts->first())
        ->create(['status' => 'approved']);

    $this->pendingComments = Comment::factory()
        ->count(4)
        ->for($this->publishedPosts->get(1))
        ->create(['status' => 'pending']);

    // Create media files
    $this->media = Media::factory()->count(6)->create();
});

it('can get dashboard statistics', function () {
    $stats = $this->analyticsService->getDashboardStats();

    expect($stats)
        ->toBeArray()
        ->toHaveKey('total_posts')
        ->toHaveKey('published_posts')
        ->toHaveKey('draft_posts')
        ->toHaveKey('total_categories')
        ->toHaveKey('active_categories')
        ->toHaveKey('total_tags')
        ->toHaveKey('active_tags')
        ->toHaveKey('total_media')
        ->toHaveKey('total_comments')
        ->toHaveKey('pending_comments')
        ->toHaveKey('approved_comments')
        ->and($stats['total_posts'])->toBe(8) // 5 published + 3 draft
        ->and($stats['published_posts'])->toBe(5)
        ->and($stats['draft_posts'])->toBe(3)
        ->and($stats['total_categories'])->toBe(3)
        ->and($stats['active_categories'])->toBe(3)
        ->and($stats['total_tags'])->toBe(5)
        ->and($stats['active_tags'])->toBe(5)
        ->and($stats['total_media'])->toBe(6)
        ->and($stats['total_comments'])->toBe(12) // 8 approved + 4 pending
        ->and($stats['pending_comments'])->toBe(4)
        ->and($stats['approved_comments'])->toBe(8);
});

it('can get monthly post statistics', function () {
    $stats = $this->analyticsService->getMonthlyPostStats();

    expect($stats)
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(12); // Last 12 months

    $currentMonth = $stats->last();
    expect($currentMonth)
        ->toHaveKey('month')
        ->toHaveKey('label')
        ->toHaveKey('total_posts')
        ->toHaveKey('published_posts')
        ->toHaveKey('draft_posts')
        ->and($currentMonth['total_posts'])->toBe(8)
        ->and($currentMonth['published_posts'])->toBe(5)
        ->and($currentMonth['draft_posts'])->toBe(3);
});

it('can get recent pending comments', function () {
    $pendingComments = $this->analyticsService->getRecentPendingComments(5);

    expect($pendingComments)
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(4); // We created 4 pending comments

    $firstComment = $pendingComments->first();
    expect($firstComment)
        ->toBeArray()
        ->toHaveKey('id')
        ->toHaveKey('content')
        ->toHaveKey('author_name')
        ->toHaveKey('post_title')
        ->toHaveKey('created_at')
        ->toHaveKey('excerpt');
});

it('can get content distribution', function () {
    $distribution = $this->analyticsService->getContentDistribution();

    expect($distribution)
        ->toBeArray()
        ->toHaveKey('posts_by_status')
        ->toHaveKey('comments_by_status')
        ->toHaveKey('posts_by_category')
        ->toHaveKey('media_by_type');

    expect($distribution['posts_by_status'])
        ->toHaveKey('published')
        ->toHaveKey('draft')
        ->and($distribution['posts_by_status']['published'])->toBe(5)
        ->and($distribution['posts_by_status']['draft'])->toBe(3);

    expect($distribution['comments_by_status'])
        ->toHaveKey('approved')
        ->toHaveKey('pending')
        ->and($distribution['comments_by_status']['approved'])->toBe(8)
        ->and($distribution['comments_by_status']['pending'])->toBe(4);
});

it('can get recent activity', function () {
    $activity = $this->analyticsService->getRecentActivity(10);

    expect($activity)
        ->toBeInstanceOf(Collection::class)
        ->not->toBeEmpty();

    $firstActivity = $activity->first();
    expect($firstActivity)
        ->toBeArray()
        ->toHaveKey('type')
        ->toHaveKey('action')
        ->toHaveKey('title')
        ->toHaveKey('created_at')
        ->toHaveKey('url');

    // Should contain both posts and comments
    $types = $activity->pluck('type')->unique();
    expect($types)->toContain('post');
    expect($types)->toContain('comment');
});

it('can get top performing content', function () {
    $topContent = $this->analyticsService->getTopPerformingContent();

    expect($topContent)
        ->toBeArray()
        ->toHaveKey('most_viewed_posts')
        ->toHaveKey('most_commented_posts');

    expect($topContent['most_viewed_posts'])
        ->toBeArray()
        ->toHaveCount(5); // We have 5 published posts

    if (! empty($topContent['most_viewed_posts'])) {
        $topPost = $topContent['most_viewed_posts'][0];
        expect($topPost)
            ->toHaveKey('title')
            ->toHaveKey('views')
            ->toHaveKey('url');
    }
});

it('caches dashboard statistics for performance', function () {
    // First call should hit the database
    $stats1 = $this->analyticsService->getDashboardStats();

    // Second call should hit cache (we can't easily test this without mocking)
    $stats2 = $this->analyticsService->getDashboardStats();

    expect($stats1)->toBe($stats2);
});

it('can clear analytics cache', function () {
    // Generate some cached data
    $this->analyticsService->getDashboardStats();
    $this->analyticsService->getMonthlyPostStats();

    // Clear cache should not throw any errors
    $this->analyticsService->clearCache();

    // Verify method executed without error
    expect(true)->toBeTrue();
});

it('handles empty data gracefully', function () {
    // Clear all test data
    Post::truncate();
    Comment::truncate();
    Category::truncate();
    Tag::truncate();
    Media::truncate();

    $stats = $this->analyticsService->getDashboardStats();

    expect($stats['total_posts'])->toBe(0);
    expect($stats['published_posts'])->toBe(0);
    expect($stats['total_comments'])->toBe(0);
    expect($stats['total_categories'])->toBe(0);
    expect($stats['total_tags'])->toBe(0);
    expect($stats['total_media'])->toBe(0);
});

it('generates correct monthly labels', function () {
    $monthlyStats = $this->analyticsService->getMonthlyPostStats();

    // Check that we have proper month labels
    $labels = $monthlyStats->pluck('label');

    expect($labels)
        ->toHaveCount(12)
        ->each->toMatch('/^[A-Z][a-z]{2} \d{4}$/'); // Format: "Jan 2024"
});

it('sorts recent activity by date descending', function () {
    $activity = $this->analyticsService->getRecentActivity();

    if ($activity->count() > 1) {
        $dates = $activity->pluck('created_at')->map->getTimestamp();
        $previous = null;

        foreach ($dates as $timestamp) {
            if ($previous !== null) {
                expect($timestamp)->toBeLessThanOrEqual($previous);
            }
            $previous = $timestamp;
        }
    }

    expect($activity)->toBeInstanceOf(Collection::class);
});
