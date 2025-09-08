<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create roles and permissions for testing
    Role::create(['name' => 'Super Admin']);
    Permission::create(['name' => 'view posts']);
    Permission::create(['name' => 'create posts']);
    Permission::create(['name' => 'edit posts']);
    Permission::create(['name' => 'delete posts']);
});

test('admin can view posts index page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    $response = $this->actingAs($admin)->get('/admin/posts');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Posts/Index')
        ->has('posts')
        ->has('stats')
        ->has('filters')
        ->has('categories')
        ->has('authors')
    );
});

test('admin can view create post page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    $response = $this->actingAs($admin)->get('/admin/posts/create');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Posts/Create')
        ->has('categories')
        ->has('tags')
        ->has('media')
    );
});

test('admin can view edit post page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    $post = Post::factory()->create();

    $response = $this->actingAs($admin)->get("/admin/posts/{$post->id}/edit");

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Posts/Edit')
        ->has('post')
        ->has('categories')
        ->has('tags')
        ->has('media')
        ->has('selectedTagIds')
    );
});

test('admin can view show post page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    $post = Post::factory()->create();

    $response = $this->actingAs($admin)->get("/admin/posts/{$post->id}");

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Posts/Show')
        ->has('post')
    );
});

test('admin can create a new post', function () {
    $admin = User::factory()->create();
    $admin->assignRole('Super Admin');

    $category = Category::factory()->create();
    $tag = Tag::factory()->create();

    $postData = [
        'title' => 'Test Post',
        'slug' => 'test-post',
        'content' => 'This is test content',
        'excerpt' => 'Test excerpt',
        'status' => 'draft',
        'category_id' => $category->id,
        'tag_ids' => [$tag->id],
        'is_featured' => false,
        'allow_comments' => true,
        'is_sticky' => false,
        'sort_order' => 0,
    ];

    $response = $this->actingAs($admin)->post('/admin/posts', $postData);

    $response->assertRedirect('/admin/posts');

    $this->assertDatabaseHas('posts', [
        'title' => 'Test Post',
        'slug' => 'test-post',
        'content' => 'This is test content',
        'status' => 'draft',
        'user_id' => $admin->id,
        'category_id' => $category->id,
    ]);

    $post = Post::where('title', 'Test Post')->first();
    expect($post->tags)->toHaveCount(1);
    expect($post->tags->first()->id)->toBe($tag->id);
});
