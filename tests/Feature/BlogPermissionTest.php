<?php

declare(strict_types=1);

use App\Models\Media;
use App\Models\Post;
use App\Models\User;

beforeEach(function () {
    // Seed roles and permissions
    $this->artisan('db:seed', ['--class' => 'BlogRolePermissionSeeder']);
});

it('allows super admin to access all blog features', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super-admin');

    $post = Post::factory()->create();

    $this->actingAs($superAdmin)
        ->get('/admin/posts')
        ->assertOk();

    $this->actingAs($superAdmin)
        ->get("/admin/posts/{$post->id}")
        ->assertOk();

    $this->actingAs($superAdmin)
        ->get('/admin/posts/create')
        ->assertOk();
});

it('allows editor to manage all content but not system settings', function () {
    $editor = User::factory()->create();
    $editor->assignRole('editor');

    $post = Post::factory()->create();

    // Can access posts
    $this->actingAs($editor)
        ->get('/admin/posts')
        ->assertOk();

    // Can edit any post
    $this->actingAs($editor)
        ->get("/admin/posts/{$post->id}/edit")
        ->assertOk();

    // Can publish posts
    expect($editor->can('publish posts'))->toBeTrue();
    expect($editor->can('edit posts'))->toBeTrue();
    expect($editor->can('delete posts'))->toBeTrue();
});

it('allows author to manage own posts only', function () {
    $author = User::factory()->create();
    $author->assignRole('author');

    $ownPost = Post::factory()->create(['user_id' => $author->id]);
    $otherPost = Post::factory()->create();

    // Can view own posts
    expect($author->can('view', $ownPost))->toBeTrue();
    expect($author->can('update', $ownPost))->toBeTrue();
    expect($author->can('delete', $ownPost))->toBeTrue();

    // Cannot edit other's posts
    expect($author->can('view', $otherPost))->toBeFalse();
    expect($author->can('update', $otherPost))->toBeFalse();
    expect($author->can('delete', $otherPost))->toBeFalse();

    // Cannot publish (even own posts)
    expect($author->can('publish', $ownPost))->toBeFalse();
});

it('allows contributor to create drafts but not publish', function () {
    $contributor = User::factory()->create();
    $contributor->assignRole('contributor');

    $ownPost = Post::factory()->create(['user_id' => $contributor->id]);

    // Can create posts
    expect($contributor->can('create posts'))->toBeTrue();

    // Can edit own posts
    expect($contributor->can('view', $ownPost))->toBeTrue();
    expect($contributor->can('update', $ownPost))->toBeTrue();

    // Cannot publish or delete
    expect($contributor->can('publish', $ownPost))->toBeFalse();
    expect($contributor->can('delete', $ownPost))->toBeFalse();
});

it('denies access to unauthorized users', function () {
    $user = User::factory()->create(); // No roles assigned

    $this->actingAs($user)
        ->get('/admin/posts')
        ->assertForbidden();

    $this->actingAs($user)
        ->get('/admin/posts/create')
        ->assertForbidden();
});

it('prevents access to admin panel without proper permissions', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/admin')
        ->assertForbidden();
});

it('allows role-based category and tag management', function () {
    $editor = User::factory()->create();
    $editor->assignRole('editor');

    $author = User::factory()->create();
    $author->assignRole('author');

    // Editor can manage categories and tags
    expect($editor->can('create categories'))->toBeTrue();
    expect($editor->can('edit categories'))->toBeTrue();
    expect($editor->can('delete categories'))->toBeTrue();

    // Author can only view categories, create tags
    expect($author->can('view categories'))->toBeTrue();
    expect($author->can('create categories'))->toBeFalse();
    expect($author->can('create tags'))->toBeTrue();
    expect($author->can('edit tags'))->toBeFalse();
});

it('enforces media permissions based on ownership', function () {
    $author = User::factory()->create();
    $author->assignRole('author');

    $ownMedia = Media::factory()->create(['uploaded_by' => $author->id]);
    $otherMedia = Media::factory()->create();

    // Can manage own media
    expect($author->can('view', $ownMedia))->toBeTrue();
    expect($author->can('update', $ownMedia))->toBeTrue();
    expect($author->can('delete', $ownMedia))->toBeTrue();

    // Cannot manage other's media
    expect($author->can('view', $otherMedia))->toBeFalse();
    expect($author->can('update', $otherMedia))->toBeFalse();
    expect($author->can('delete', $otherMedia))->toBeFalse();
});
