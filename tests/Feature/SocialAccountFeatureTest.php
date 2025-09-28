<?php

declare(strict_types=1);

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('can create social account with metadata', function () {
    $socialAccount = SocialAccount::factory()->create([
        'user_id' => $this->user->id,
        'metadata' => [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'avatar' => 'https://example.com/avatar.jpg',
            'username' => 'johndoe',
        ],
    ]);

    expect($socialAccount->name)->toBe('John Doe');
    expect($socialAccount->email)->toBe('john@example.com');
    expect($socialAccount->avatar)->toBe('https://example.com/avatar.jpg');
    expect($socialAccount->username)->toBe('johndoe');
});

it('belongs to a user', function () {
    $socialAccount = SocialAccount::factory()->create(['user_id' => $this->user->id]);

    expect($socialAccount->user)->toBeInstanceOf(User::class);
    expect($socialAccount->user->id)->toBe($this->user->id);
});

it('user can have multiple social accounts', function () {
    SocialAccount::factory()->create([
        'user_id' => $this->user->id,
        'provider' => 'google',
    ]);

    SocialAccount::factory()->create([
        'user_id' => $this->user->id,
        'provider' => 'github',
    ]);

    expect($this->user->fresh()->socialAccounts)->toHaveCount(2);
});

it('can check if user is social only', function () {
    // User with password is not social only
    expect($this->user->isSocialOnly())->toBeFalse();

    // User without password but with social accounts is social only
    $this->user->update(['password' => null]);
    SocialAccount::factory()->create(['user_id' => $this->user->id]);

    expect($this->user->fresh()->isSocialOnly())->toBeTrue();
});

it('can check if user can unlink provider', function () {
    // Create user with password and two social accounts
    SocialAccount::factory()->create([
        'user_id' => $this->user->id,
        'provider' => 'google',
    ]);

    SocialAccount::factory()->create([
        'user_id' => $this->user->id,
        'provider' => 'github',
    ]);

    // User with password can unlink any provider
    expect($this->user->canUnlinkProvider('google'))->toBeTrue();

    // Remove password and one provider - user cannot unlink last provider
    $this->user->update(['password' => null]);
    $this->user->socialAccounts()->where('provider', 'github')->delete();

    expect($this->user->fresh()->canUnlinkProvider('google'))->toBeFalse();
});
