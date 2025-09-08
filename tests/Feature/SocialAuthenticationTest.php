<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

uses(RefreshDatabase::class);

test('redirects to social provider', function () {
    $response = $this->get('/auth/google');

    $response->assertRedirect();
});

test('handles social callback and creates new user', function () {
    $socialiteUser = Mockery::mock(SocialiteUser::class);
    $socialiteUser->shouldReceive('getId')->andReturn('123456789');
    $socialiteUser->shouldReceive('getName')->andReturn('John Doe');
    $socialiteUser->shouldReceive('getEmail')->andReturn('john@example.com');
    $socialiteUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');

    Socialite::shouldReceive('driver')->with('google')->andReturnSelf();
    Socialite::shouldReceive('user')->andReturn($socialiteUser);

    $response = $this->get('/auth/google/callback');

    $response->assertRedirect(route('dashboard'));

    $this->assertDatabaseHas('users', [
        'email' => 'john@example.com',
        'name' => 'John Doe',
        'provider' => 'google',
        'provider_id' => '123456789',
        'avatar' => 'https://example.com/avatar.jpg',
    ]);

    $this->assertAuthenticated();
});

test('links social account to existing user with same email', function () {
    $existingUser = User::factory()->create([
        'email' => 'john@example.com',
        'name' => 'John Existing',
    ]);

    $socialiteUser = Mockery::mock(SocialiteUser::class);
    $socialiteUser->shouldReceive('getId')->andReturn('123456789');
    $socialiteUser->shouldReceive('getName')->andReturn('John Doe');
    $socialiteUser->shouldReceive('getEmail')->andReturn('john@example.com');
    $socialiteUser->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');

    Socialite::shouldReceive('driver')->with('google')->andReturnSelf();
    Socialite::shouldReceive('user')->andReturn($socialiteUser);

    $response = $this->get('/auth/google/callback');

    $response->assertRedirect(route('dashboard'));

    $existingUser->refresh();

    expect($existingUser->provider)->toBe('google');
    expect($existingUser->provider_id)->toBe('123456789');
    expect($existingUser->avatar)->toBe('https://example.com/avatar.jpg');

    $this->assertAuthenticatedAs($existingUser);
});

test('logs in existing social user', function () {
    $existingUser = User::factory()->create([
        'email' => 'john@example.com',
        'provider' => 'google',
        'provider_id' => '123456789',
    ]);

    $socialiteUser = Mockery::mock(SocialiteUser::class);
    $socialiteUser->shouldReceive('getId')->andReturn('123456789');
    $socialiteUser->shouldReceive('getName')->andReturn('John Updated');
    $socialiteUser->shouldReceive('getEmail')->andReturn('john@example.com');
    $socialiteUser->shouldReceive('getAvatar')->andReturn('https://example.com/new-avatar.jpg');

    Socialite::shouldReceive('driver')->with('google')->andReturnSelf();
    Socialite::shouldReceive('user')->andReturn($socialiteUser);

    $response = $this->get('/auth/google/callback');

    $response->assertRedirect(route('dashboard'));

    $existingUser->refresh();

    expect($existingUser->name)->toBe('John Updated');
    expect($existingUser->avatar)->toBe('https://example.com/new-avatar.jpg');

    $this->assertAuthenticatedAs($existingUser);
});

test('can unlink social account', function () {
    $user = User::factory()->create([
        'provider' => 'google',
        'provider_id' => '123456789',
        'password' => bcrypt('password'),
    ]);

    $response = $this
        ->actingAs($user)
        ->delete('/auth/google/unlink');

    $response->assertRedirect();

    $user->refresh();

    expect($user->provider)->toBeNull();
    expect($user->provider_id)->toBeNull();
});

test('prevents unlinking if user has no password', function () {
    $user = User::factory()->create([
        'provider' => 'google',
        'provider_id' => '123456789',
        'password' => null,
    ]);

    $response = $this
        ->actingAs($user)
        ->delete('/auth/google/unlink');

    $response->assertRedirect();
    $response->assertSessionHasErrors('social');

    $user->refresh();

    expect($user->provider)->toBe('google');
    expect($user->provider_id)->toBe('123456789');
});

test('rejects unsupported social provider', function () {
    $response = $this->get('/auth/unsupported');

    $response->assertNotFound();
});
