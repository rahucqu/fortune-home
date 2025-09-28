<?php

declare(strict_types=1);

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\User;

test('blog index page loads successfully', function () {
    $response = $this->get('/blog');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('frontend/blog/index')
        ->has('blogPosts')
        ->has('categories')
        ->has('popularTags')
        ->has('latestPosts')
        ->has('filters')
    );
});

test('blog posts can be searched by keyword', function () {
    $user = User::factory()->create();
    $category = BlogCategory::factory()->create(['name' => 'Tech News', 'is_active' => true]);

    $post1 = BlogPost::factory()->create([
        'title' => 'Laravel Framework Tutorial',
        'excerpt' => 'Learn Laravel framework basics',
        'status' => 'published',
        'author_id' => $user->id,
        'category_id' => $category->id,
        'published_at' => now(),
    ]);

    $post2 = BlogPost::factory()->create([
        'title' => 'React Development Guide',
        'excerpt' => 'Modern React development practices',
        'status' => 'published',
        'author_id' => $user->id,
        'category_id' => $category->id,
        'published_at' => now(),
    ]);

    $response = $this->get('/blog?search=Laravel');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('frontend/blog/index')
        ->where('filters.search', 'Laravel')
        ->has('blogPosts.data', 1)
        ->where('blogPosts.data.0.title', 'Laravel Framework Tutorial')
    );
});

test('blog posts can be filtered by category', function () {
    $user = User::factory()->create();
    $category1 = BlogCategory::factory()->create(['name' => 'Tech', 'is_active' => true]);
    $category2 = BlogCategory::factory()->create(['name' => 'Business', 'is_active' => true]);

    $techPost = BlogPost::factory()->create([
        'title' => 'Tech Post',
        'status' => 'published',
        'author_id' => $user->id,
        'category_id' => $category1->id,
        'published_at' => now(),
    ]);

    $businessPost = BlogPost::factory()->create([
        'title' => 'Business Post',
        'status' => 'published',
        'author_id' => $user->id,
        'category_id' => $category2->id,
        'published_at' => now(),
    ]);

    $response = $this->get("/blog?category={$category1->id}");

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('frontend/blog/index')
        ->where('filters.category', $category1->id)
        ->has('blogPosts.data', 1)
        ->where('blogPosts.data.0.title', 'Tech Post')
    );
});

test('blog posts can be filtered by tag', function () {
    $user = User::factory()->create();
    $category = BlogCategory::factory()->create(['name' => 'Tech', 'is_active' => true]);
    $tag1 = BlogTag::factory()->create(['name' => 'Laravel']);
    $tag2 = BlogTag::factory()->create(['name' => 'React']);

    $post1 = BlogPost::factory()->create([
        'title' => 'Laravel Post',
        'status' => 'published',
        'author_id' => $user->id,
        'category_id' => $category->id,
        'published_at' => now(),
    ]);

    $post2 = BlogPost::factory()->create([
        'title' => 'React Post',
        'status' => 'published',
        'author_id' => $user->id,
        'category_id' => $category->id,
        'published_at' => now(),
    ]);

    $post1->tags()->attach($tag1->id);
    $post2->tags()->attach($tag2->id);

    $response = $this->get("/blog?tag={$tag1->id}");

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('frontend/blog/index')
        ->where('filters.tag', $tag1->id)
        ->has('blogPosts.data', 1)
        ->where('blogPosts.data.0.title', 'Laravel Post')
    );
});

test('blog posts are paginated correctly', function () {
    $user = User::factory()->create();
    $category = BlogCategory::factory()->create(['name' => 'Tech', 'is_active' => true]);

    // Create 12 published blog posts (more than the page size of 9)
    for ($i = 1; $i <= 12; $i++) {
        BlogPost::factory()->create([
            'title' => "Blog Post {$i}",
            'status' => 'published',
            'author_id' => $user->id,
            'category_id' => $category->id,
            'published_at' => now()->subMinutes($i), // Different published times for ordering
        ]);
    }

    $response = $this->get('/blog');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('frontend/blog/index')
        ->where('blogPosts.per_page', 9)
        ->where('blogPosts.total', 12)
        ->where('blogPosts.last_page', 2)
        ->has('blogPosts.data', 9)
    );
});

test('only published blog posts are shown to visitors', function () {
    $user = User::factory()->create();
    $category = BlogCategory::factory()->create(['name' => 'Tech', 'is_active' => true]);

    $publishedPost = BlogPost::factory()->create([
        'title' => 'Published Post',
        'status' => 'published',
        'author_id' => $user->id,
        'category_id' => $category->id,
        'published_at' => now(),
    ]);

    $draftPost = BlogPost::factory()->create([
        'title' => 'Draft Post',
        'status' => 'draft',
        'author_id' => $user->id,
        'category_id' => $category->id,
        'published_at' => null,
    ]);

    $response = $this->get('/blog');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('frontend/blog/index')
        ->has('blogPosts.data', 1)
        ->where('blogPosts.data.0.title', 'Published Post')
    );
});
