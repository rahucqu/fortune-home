<?php

<?php

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;

it('can create a comment', function () {
    $post = Post::factory()->published()->create();
    $user = User::factory()->create();

    $comment = Comment::factory()
        ->fromRegisteredUser()
        ->pending()
        ->create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'content' => 'This is a test comment.',
        ]);

    expect($comment)
        ->toBeInstanceOf(Comment::class)
        ->and($comment->content)->toBe('This is a test comment.')
        ->and($comment->status)->toBe('pending')
        ->and($comment->post_id)->toBe($post->id)
        ->and($comment->user_id)->toBe($user->id);
});

it('can approve a comment', function () {
    $user = User::factory()->create();
    $comment = Comment::factory()->pending()->create();

    $this->actingAs($user);
    $comment->approve();

    expect($comment->refresh())
        ->status->toBe('approved')
        ->and($comment->approved_at)->not->toBeNull()
        ->and($comment->approved_by)->toBe($user->id);
});

it('can reject a comment', function () {
    $comment = Comment::factory()->approved()->create();

    $comment->reject();

    expect($comment->refresh())
        ->status->toBe('rejected')
        ->and($comment->approved_at)->toBeNull()
        ->and($comment->approved_by)->toBeNull();
});

it('can mark comment as spam', function () {
    $comment = Comment::factory()->approved()->create();

    $comment->markAsSpam();

    expect($comment->refresh())
        ->status->toBe('spam')
        ->and($comment->approved_at)->toBeNull()
        ->and($comment->approved_by)->toBeNull();
});

it('has correct accessors', function () {
    $user = User::factory()->create(['name' => 'John Doe']);
    $comment = Comment::factory()->fromRegisteredUser()->create(['user_id' => $user->id]);

    expect($comment->author_display_name)->toBe('John Doe')
        ->and($comment->is_guest)->toBeFalse()
        ->and($comment->is_reply)->toBeFalse();
});

it('can create nested replies', function () {
    $parent = Comment::factory()->approved()->create();
    $reply = Comment::factory()->reply($parent)->approved()->create();

    expect($reply->parent_id)->toBe($parent->id)
        ->and($reply->is_reply)->toBeTrue()
        ->and($reply->depth)->toBe(1);
});

it('can filter comments by status', function () {
    Comment::factory()->approved()->count(3)->create();
    Comment::factory()->pending()->count(2)->create();
    Comment::factory()->spam()->count(1)->create();

    expect(Comment::approved()->count())->toBe(3)
        ->and(Comment::pending()->count())->toBe(2)
        ->and(Comment::spam()->count())->toBe(1);
});

it('can search comments', function () {
    Comment::factory()->create(['content' => 'Laravel is awesome']);
    Comment::factory()->create(['content' => 'PHP is great']);
    Comment::factory()->create(['author_name' => 'Laravel Fan']);

    $results = Comment::search('Laravel')->get();

    expect($results)->toHaveCount(2);
});
