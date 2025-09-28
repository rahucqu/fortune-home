<?php

declare(strict_types=1);

use App\Models\BlogPost;
use App\Models\User;

test('handles string rating conversion correctly', function () {
    $user = User::factory()->create();
    $blogPost = BlogPost::factory()->create();

    // Test web form submission with string rating (simulating frontend behavior)
    $response = actingAs($user)
        ->post(route('reviews.store', ['morph_type' => 'blog-post', 'morph_id' => $blogPost->id]), [
            'rating' => '4', // String rating like what comes from frontend
            'comment' => 'Test comment',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    // Verify the review was created with integer rating
    $review = $blogPost->reviews()->first();
    expect($review)->not->toBeNull();
    expect($review->rating)->toBe(4);
    expect($review->rating)->toBeInt();
});

test('validates rating as integer', function () {
    $user = User::factory()->create();
    $blogPost = BlogPost::factory()->create();

    // Test with invalid rating
    $response = actingAs($user)
        ->post(route('reviews.store', ['morph_type' => 'blog-post', 'morph_id' => $blogPost->id]), [
            'rating' => 'invalid',
            'comment' => 'Test comment',
        ]);

    $response->assertRedirect();
    $response->assertSessionHasErrors(['rating']);
});
