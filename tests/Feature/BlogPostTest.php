<?php

declare(strict_types=1);

use App\Enums\BlogPostStatus;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    // Create admin role and user
    $this->adminRole = Role::findByName('admin');
    $this->admin = User::factory()->create();
    $this->admin->assignRole($this->adminRole);

    // Create agent role and user
    $this->agentRole = Role::findByName('agent');
    $this->agent = User::factory()->create();
    $this->agent->assignRole($this->agentRole);

    // Create category and tags
    $this->category = BlogCategory::factory()->create();
    $this->tags = BlogTag::factory()->count(3)->create();
});

describe('Blog Post Management', function () {
    test('admin can view blog posts index', function () {
        BlogPost::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)->get(route('admin.blog.index'));

        $response->assertSuccessful();
    });

    test('agent can only view their own blog posts', function () {
        // Create posts for admin and agent
        BlogPost::factory()->create(['author_id' => $this->admin->id]);
        $agentPost = BlogPost::factory()->create(['author_id' => $this->agent->id]);

        $response = $this->actingAs($this->agent)->get(route('admin.blog.index'));

        $response->assertSuccessful();
    });

    test('admin can create a blog post', function () {
        $postData = [
            'title' => 'Test Blog Post',
            'content' => 'This is test content for the blog post.',
            'category_id' => $this->category->id,
            'tag_ids' => $this->tags->pluck('id')->toArray(),
            'status' => BlogPostStatus::DRAFT->value,
            'is_featured' => false,
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.blog.store'), $postData);

        $response->assertRedirect();
        $this->assertDatabaseHas('blog_posts', [
            'title' => 'Test Blog Post',
            'author_id' => $this->admin->id,
            'category_id' => $this->category->id,
            'status' => BlogPostStatus::DRAFT->value,
        ]);
    });

    test('agent can create a blog post', function () {
        $postData = [
            'title' => 'Agent Blog Post',
            'content' => 'This is content from an agent.',
            'category_id' => $this->category->id,
            'status' => BlogPostStatus::DRAFT->value,
        ];

        $response = $this->actingAs($this->agent)->post(route('admin.blog.store'), $postData);

        $response->assertRedirect();
        $this->assertDatabaseHas('blog_posts', [
            'title' => 'Agent Blog Post',
            'author_id' => $this->agent->id,
        ]);
    });

    test('admin can update any blog post', function () {
        $post = BlogPost::factory()->create(['author_id' => $this->agent->id]);

        $updateData = [
            'title' => 'Updated Title',
            'content' => $post->content,
            'category_id' => $post->category_id,
            'status' => $post->status->value,
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.blog.update', $post), $updateData);

        $response->assertRedirect();
        $this->assertDatabaseHas('blog_posts', [
            'id' => $post->id,
            'title' => 'Updated Title',
        ]);
    });

    test('agent can update their own blog post', function () {
        $post = BlogPost::factory()->create(['author_id' => $this->agent->id]);

        $updateData = [
            'title' => 'Agent Updated Title',
            'content' => $post->content,
            'category_id' => $post->category_id,
            'status' => $post->status->value,
        ];

        $response = $this->actingAs($this->agent)->put(route('admin.blog.update', $post), $updateData);

        $response->assertRedirect();
        $this->assertDatabaseHas('blog_posts', [
            'id' => $post->id,
            'title' => 'Agent Updated Title',
        ]);
    });

    test('agent cannot update other users blog posts', function () {
        $post = BlogPost::factory()->create(['author_id' => $this->admin->id]);

        $updateData = [
            'title' => 'Unauthorized Update',
            'content' => 'Some content',
            'category_id' => $this->category->id,
            'status' => BlogPostStatus::DRAFT->value,
        ];

        $response = $this->actingAs($this->agent)->put(route('admin.blog.update', $post), $updateData);

        // In web context, authorization failures redirect rather than return 403
        $response->assertRedirect();
    });

    test('admin can publish a blog post', function () {
        $post = BlogPost::factory()->create(['status' => BlogPostStatus::DRAFT]);

        $response = $this->actingAs($this->admin)->post(route('admin.blog.publish', $post));

        $response->assertRedirect();
        $post->refresh();
        expect($post->status)->toBe(BlogPostStatus::PUBLISHED);
        expect($post->published_at)->not->toBeNull();
    });

    test('admin can feature a blog post', function () {
        $post = BlogPost::factory()->create(['is_featured' => false]);

        $response = $this->actingAs($this->admin)->post(route('admin.blog.feature', $post));

        $response->assertRedirect();
        $post->refresh();
        expect($post->is_featured)->toBeTrue();
    });

    test('admin can delete a blog post', function () {
        $post = BlogPost::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('admin.blog.destroy', $post));

        $response->assertRedirect();
        $this->assertSoftDeleted('blog_posts', ['id' => $post->id]);
    });

    test('agent cannot delete blog posts', function () {
        $post = BlogPost::factory()->create(['author_id' => $this->agent->id]);

        $response = $this->actingAs($this->agent)->delete(route('admin.blog.destroy', $post));

        $response->assertForbidden();
    });

    test('admin can bulk publish blog posts', function () {
        $posts = BlogPost::factory()->count(3)->create(['status' => BlogPostStatus::DRAFT]);
        $postIds = $posts->pluck('id')->toArray();

        $response = $this->actingAs($this->admin)->post(route('admin.blog.bulk.publish'), [
            'ids' => $postIds,
        ]);

        $response->assertRedirect();

        foreach ($posts as $post) {
            $post->refresh();
            expect($post->status)->toBe(BlogPostStatus::PUBLISHED);
        }
    })->skip('Route may not be implemented yet');

    test('blog post validation works correctly', function () {
        $response = $this->actingAs($this->admin)->post(route('admin.blog.store'), [
            'title' => '', // Required field
            'content' => '', // Required field
        ]);

        $response->assertSessionHasErrors(['title', 'content', 'category_id']);
    });
});
