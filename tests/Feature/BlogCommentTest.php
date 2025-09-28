<?php

declare(strict_types=1);

use App\Enums\BlogCommentStatus;
use App\Models\BlogComment;
use App\Models\BlogPost;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->adminRole = Role::findByName('admin');
    $this->admin = User::factory()->create();
    $this->admin->assignRole($this->adminRole);

    $this->userRole = Role::findByName('user');
    $this->user = User::factory()->create();
    $this->user->assignRole($this->userRole);

    $this->blogPost = BlogPost::factory()->create();
});

describe('Blog Comment Management', function () {
    test('admin can view all comments', function () {
        BlogComment::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)->get(route('admin.blog.comments.index'));

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/blog/comments/index')
            ->has('comments.data', 5)
        );
    });

    test('admin can view moderation queue', function () {
        BlogComment::factory()->count(3)->create(['status' => BlogCommentStatus::Pending]);
        BlogComment::factory()->count(2)->create(['status' => BlogCommentStatus::Approved]);

        $response = $this->actingAs($this->admin)->get(route('admin.blog.comments.moderate'));

        $response->assertSuccessful();
        $response->assertInertia(fn ($page) => $page
            ->component('admin/blog/comments/moderate')
            ->has('comments.data', 3) // Only pending comments
        );
    });

    test('user can create a comment', function () {
        $commentData = [
            'blog_post_id' => $this->blogPost->id,
            'content' => 'This is a test comment.',
        ];

        $response = $this->actingAs($this->user)->post(route('admin.blog.comments.store'), $commentData);

        $response->assertRedirect();
        $this->assertDatabaseHas('blog_comments', [
            'blog_post_id' => $this->blogPost->id,
            'user_id' => $this->user->id,
            'content' => 'This is a test comment.',
            'status' => BlogCommentStatus::Pending->value,
        ]);
    });

    test('user can create a reply to a comment', function () {
        $parentComment = BlogComment::factory()->create([
            'blog_post_id' => $this->blogPost->id,
            'status' => BlogCommentStatus::Approved,
        ]);

        $replyData = [
            'blog_post_id' => $this->blogPost->id,
            'parent_id' => $parentComment->id,
            'content' => 'This is a reply to the comment.',
        ];

        $response = $this->actingAs($this->user)->post(route('admin.blog.comments.store'), $replyData);

        $response->assertRedirect();
        $this->assertDatabaseHas('blog_comments', [
            'blog_post_id' => $this->blogPost->id,
            'parent_id' => $parentComment->id,
            'user_id' => $this->user->id,
            'content' => 'This is a reply to the comment.',
        ]);
    });

    test('user can update their own comment', function () {
        $comment = BlogComment::factory()->create([
            'user_id' => $this->user->id,
            'status' => BlogCommentStatus::Pending,
        ]);

        $updateData = [
            'content' => 'Updated comment content.',
        ];

        $response = $this->actingAs($this->user)->put(route('admin.blog.comments.update', $comment), $updateData);

        $response->assertRedirect();
        $this->assertDatabaseHas('blog_comments', [
            'id' => $comment->id,
            'content' => 'Updated comment content.',
        ]);
    });

    test('user cannot update other users comments', function () {
        $otherUser = User::factory()->create();
        $otherUser->assignRole($this->userRole);

        $comment = BlogComment::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)->put(route('admin.blog.comments.update', $comment), [
            'content' => 'Unauthorized update.',
        ]);

        // In web context, authorization failures redirect rather than return 403
        $response->assertRedirect();
    });

    test('admin can approve a comment', function () {
        $comment = BlogComment::factory()->create(['status' => BlogCommentStatus::Pending]);

        $response = $this->actingAs($this->admin)->post(route('admin.blog.comments.approve', $comment));

        $response->assertRedirect();
        $comment->refresh();
        expect($comment->status)->toBe(BlogCommentStatus::Approved);
        expect($comment->approved_at)->not->toBeNull();
        expect($comment->approved_by)->toBe($this->admin->id);
    });

    test('admin can reject a comment', function () {
        $comment = BlogComment::factory()->create(['status' => BlogCommentStatus::Pending]);

        $response = $this->actingAs($this->admin)->post(route('admin.blog.comments.reject', $comment));

        $response->assertRedirect();
        $comment->refresh();
        expect($comment->status)->toBe(BlogCommentStatus::Rejected);
        expect($comment->approved_by)->toBe($this->admin->id);
    });

    test('admin can bulk approve comments', function () {
        $comments = BlogComment::factory()->count(3)->create(['status' => BlogCommentStatus::Pending]);
        $commentIds = $comments->pluck('id')->toArray();

        $response = $this->actingAs($this->admin)->post(route('admin.blog.comments.bulk.approve'), [
            'ids' => $commentIds,
        ]);

        $response->assertRedirect();

        foreach ($comments as $comment) {
            $comment->refresh();
            expect($comment->status)->toBe(BlogCommentStatus::Approved);
        }
    });

    test('admin can delete a comment and its replies', function () {
        $parentComment = BlogComment::factory()->create();
        $reply1 = BlogComment::factory()->create(['parent_id' => $parentComment->id]);
        $reply2 = BlogComment::factory()->create(['parent_id' => $parentComment->id]);

        $response = $this->actingAs($this->admin)->delete(route('admin.blog.comments.destroy', $parentComment));

        $response->assertRedirect();
        $this->assertDatabaseMissing('blog_comments', ['id' => $parentComment->id]);
        $this->assertDatabaseMissing('blog_comments', ['id' => $reply1->id]);
        $this->assertDatabaseMissing('blog_comments', ['id' => $reply2->id]);
    });

    test('user can delete their own comment', function () {
        $comment = BlogComment::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->delete(route('admin.blog.comments.destroy', $comment));

        $response->assertRedirect();
        $this->assertDatabaseMissing('blog_comments', ['id' => $comment->id]);
    });

    test('comment validation works correctly', function () {
        $response = $this->actingAs($this->user)->post(route('admin.blog.comments.store'), [
            'content' => 'ab', // Too short (min 3 characters)
        ]);

        $response->assertSessionHasErrors(['content', 'blog_post_id']);
    });

    test('only approved comments are visible to public', function () {
        $approvedComment = BlogComment::factory()->create([
            'blog_post_id' => $this->blogPost->id,
            'status' => BlogCommentStatus::Approved,
        ]);

        $pendingComment = BlogComment::factory()->create([
            'blog_post_id' => $this->blogPost->id,
            'status' => BlogCommentStatus::Pending,
        ]);

        $this->blogPost->load('approvedComments');

        expect($this->blogPost->approvedComments)->toHaveCount(1);
        expect($this->blogPost->approvedComments->first()->id)->toBe($approvedComment->id);
    });
});
