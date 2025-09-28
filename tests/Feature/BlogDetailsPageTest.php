<?php

declare(strict_types=1);

use App\Models\BlogCategory;
use App\Models\BlogComment;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\User;
use Illuminate\Support\Str;

test('blog details page loads successfully with dynamic data', function () {
    $user = User::factory()->create();
    $category = BlogCategory::factory()->create(['name' => 'Tech News', 'is_active' => true]);
    $tags = BlogTag::factory()->count(3)->create();

    $blogPost = BlogPost::factory()->create([
        'title' => 'Dynamic Blog Post Test',
        'slug' => 'dynamic-blog-post-test',
        'excerpt' => 'This is a test excerpt for dynamic blog post',
        'content' => '<p>This is the dynamic content for the blog post.</p>',
        'status' => 'published',
        'author_id' => $user->id,
        'category_id' => $category->id,
        'published_at' => now(),
        'is_featured' => true,
        'views_count' => 0,
    ]);

    // Attach tags to the blog post
    $blogPost->tags()->attach($tags->pluck('id'));

    $response = $this->get("/blog/{$blogPost->slug}");

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('frontend/blog/blog-details')
        ->has('blogPost')
        ->where('blogPost.title', 'Dynamic Blog Post Test')
        ->where('blogPost.slug', 'dynamic-blog-post-test')
        ->where('blogPost.excerpt', 'This is a test excerpt for dynamic blog post')
        ->where('blogPost.content', '<p>This is the dynamic content for the blog post.</p>')
        ->where('blogPost.author.name', $user->name)
        ->where('blogPost.category.name', 'Tech News')
        ->has('blogPost.tags', 3)
        ->has('relatedPosts')
        ->where('blogPost.views_count', 1) // Views count should increment after viewing
        ->hasAll(['previousPost', 'nextPost']) // Check for navigation props
    );
});

test('blog details page displays comments correctly', function () {
    $user = User::factory()->create();
    $category = BlogCategory::factory()->create(['name' => 'Tech', 'is_active' => true]);

    $blogPost = BlogPost::factory()->create([
        'title' => 'Post with Comments',
        'slug' => 'post-with-comments',
        'status' => 'published',
        'author_id' => $user->id,
        'category_id' => $category->id,
        'published_at' => now(),
    ]);

    // Create approved comments
    BlogComment::factory()->count(2)->create([
        'blog_post_id' => $blogPost->id,
        'user_id' => $user->id,
        'content' => 'This is an approved comment',
        'status' => 'approved',
    ]);

    // Create a pending comment (should not be shown)
    BlogComment::factory()->create([
        'blog_post_id' => $blogPost->id,
        'user_id' => $user->id,
        'content' => 'This is a pending comment',
        'status' => 'pending',
    ]);

    $response = $this->get("/blog/{$blogPost->slug}");

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('frontend/blog/blog-details')
        ->has('blogPost.approved_comments', 2) // Only approved comments
        ->where('blogPost.approved_comments.0.content', 'This is an approved comment')
        ->where('blogPost.approved_comments.0.user.name', $user->name)
    );
});

test('blog details page shows related posts from same category', function () {
    $user = User::factory()->create();

    // Create categories with unique names and slugs
    $techCategory = BlogCategory::factory()->create([
        'name' => 'Tech Blog '.uniqid(),
        'slug' => 'tech-blog-'.Str::random(6),
        'is_active' => true,
    ]);

    $businessCategory = BlogCategory::factory()->create([
        'name' => 'Business Blog '.uniqid(),
        'slug' => 'business-blog-'.Str::random(6),
        'is_active' => true,
    ]);

    $mainPost = BlogPost::factory()->create([
        'title' => 'Main Tech Post',
        'slug' => 'main-tech-post-'.Str::random(6),
        'status' => 'published',
        'author_id' => $user->id,
        'category_id' => $techCategory->id,
        'published_at' => now(),
    ]);

    // Create related posts in the same category
    BlogPost::factory()->count(2)->create([
        'status' => 'published',
        'author_id' => $user->id,
        'category_id' => $techCategory->id,
        'published_at' => now(),
    ]);

    // Create a post in different category (should not appear as related)
    BlogPost::factory()->create([
        'title' => 'Business Post',
        'slug' => 'business-post-'.Str::random(6),
        'status' => 'published',
        'author_id' => $user->id,
        'category_id' => $businessCategory->id,
        'published_at' => now(),
    ]);

    $response = $this->get("/blog/{$mainPost->slug}");

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('frontend/blog/blog-details')
        ->has('relatedPosts', 2) // Only posts from the same category
        ->where('blogPost.title', 'Main Tech Post')
    );
});

test('blog details page returns 404 for non-existent slug', function () {
    $response = $this->get('/blog/non-existent-slug');

    $response->assertStatus(404);
});

test('blog details page returns 404 for draft posts', function () {
    $user = User::factory()->create();
    $category = BlogCategory::factory()->create(['is_active' => true]);

    $draftPost = BlogPost::factory()->create([
        'title' => 'Draft Post',
        'slug' => 'draft-post',
        'status' => 'draft',
        'author_id' => $user->id,
        'category_id' => $category->id,
        'published_at' => null,
    ]);

    $response = $this->get("/blog/{$draftPost->slug}");

    $response->assertStatus(404);
});

test('blog details page increments views count', function () {
    $user = User::factory()->create();
    $category = BlogCategory::factory()->create(['is_active' => true]);

    $blogPost = BlogPost::factory()->create([
        'title' => 'Views Count Test',
        'slug' => 'views-count-test',
        'status' => 'published',
        'author_id' => $user->id,
        'category_id' => $category->id,
        'published_at' => now(),
        'views_count' => 5,
    ]);

    expect($blogPost->views_count)->toBe(5);

    $this->get("/blog/{$blogPost->slug}");

    $blogPost->refresh();
    expect($blogPost->views_count)->toBe(6);
});

test('blog details page provides previous and next post navigation', function () {
    $user = User::factory()->create();
    $category = BlogCategory::factory()->create(['is_active' => true]);

    // Create posts with different published dates
    $oldPost = BlogPost::factory()->create([
        'title' => 'Old Post',
        'slug' => 'old-post',
        'status' => 'published',
        'author_id' => $user->id,
        'category_id' => $category->id,
        'published_at' => now()->subDays(2),
    ]);

    $currentPost = BlogPost::factory()->create([
        'title' => 'Current Post',
        'slug' => 'current-post',
        'status' => 'published',
        'author_id' => $user->id,
        'category_id' => $category->id,
        'published_at' => now()->subDay(),
    ]);

    $newPost = BlogPost::factory()->create([
        'title' => 'New Post',
        'slug' => 'new-post',
        'status' => 'published',
        'author_id' => $user->id,
        'category_id' => $category->id,
        'published_at' => now(),
    ]);

    $response = $this->get("/blog/{$currentPost->slug}");

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('frontend/blog/blog-details')
        ->has('blogPost')
        ->has('previousPost')
        ->has('nextPost')
        ->where('previousPost.title', 'Old Post')
        ->where('previousPost.slug', 'old-post')
        ->where('nextPost.title', 'New Post')
        ->where('nextPost.slug', 'new-post')
    );
});

test('blog details page handles missing previous and next posts', function () {
    $user = User::factory()->create();
    $category = BlogCategory::factory()->create(['is_active' => true]);

    // Create only one post
    $singlePost = BlogPost::factory()->create([
        'title' => 'Single Post',
        'slug' => 'single-post',
        'status' => 'published',
        'author_id' => $user->id,
        'category_id' => $category->id,
        'published_at' => now(),
    ]);

    $response = $this->get("/blog/{$singlePost->slug}");

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('frontend/blog/blog-details')
        ->has('blogPost')
        ->whereNull('previousPost')
        ->whereNull('nextPost')
    );
});
