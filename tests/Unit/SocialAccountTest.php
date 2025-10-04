<?php

declare(strict_types=1);

use App\Models\SocialAccount;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('can get avatar from metadata', function () {
    $socialAccount = new SocialAccount([
        'metadata' => [
            'avatar' => 'https://example.com/avatar.jpg',
        ],
    ]);

    expect($socialAccount->avatar)->toBe('https://example.com/avatar.jpg');
});

it('can get username from metadata', function () {
    $socialAccount = new SocialAccount([
        'metadata' => [
            'username' => 'johndoe',
        ],
    ]);

    expect($socialAccount->username)->toBe('johndoe');
});

it('returns null for missing metadata values', function () {
    $socialAccount = new SocialAccount([
        'metadata' => [],
    ]);

    expect($socialAccount->avatar)->toBeNull();
    expect($socialAccount->username)->toBeNull();
});

it('can check if token is expired', function () {
    // Test with expired token
    $socialAccount = new SocialAccount([
        'expires_at' => Carbon::now()->subHour(),
    ]);
    expect($socialAccount->isTokenExpired())->toBeTrue();

    // Test with future expiration
    $socialAccount = new SocialAccount([
        'expires_at' => Carbon::now()->addHour(),
    ]);
    expect($socialAccount->isTokenExpired())->toBeFalse();

    // Test with no expiration date
    $socialAccount = new SocialAccount([
        'expires_at' => null,
    ]);
    expect($socialAccount->isTokenExpired())->toBeFalse();
});
