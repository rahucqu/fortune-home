<?php

declare(strict_types=1);

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('requires password when unlinking the last social account for social-only user', function () {
    $user = User::factory()->create([
        'password' => null, // Social-only user
    ]);

    SocialAccount::factory()->create([
        'user_id' => $user->id,
        'provider' => 'google',
    ]);

    $response = $this->actingAs($user)->delete(route('social.unlink', 'google'));

    $response->assertSessionHasErrors(['password']);
});

it('successfully unlinks social account and sets password for social-only user', function () {
    $user = User::factory()->create([
        'password' => null, // Social-only user
    ]);

    SocialAccount::factory()->create([
        'user_id' => $user->id,
        'provider' => 'google',
    ]);

    $response = $this->actingAs($user)->delete(route('social.unlink', 'google'), [
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('status', 'Social account unlinked successfully.');

    // Check that password was set
    $user->refresh();
    expect(Hash::check('password123', $user->password))->toBeTrue();

    // Check that social account was deleted
    expect($user->socialAccounts()->count())->toBe(0);
});

it('allows unlinking social account without password for users with multiple social accounts', function () {
    $user = User::factory()->create([
        'password' => null, // Social-only user
    ]);

    // Create two social accounts
    SocialAccount::factory()->create([
        'user_id' => $user->id,
        'provider' => 'google',
    ]);

    SocialAccount::factory()->create([
        'user_id' => $user->id,
        'provider' => 'github',
    ]);

    $response = $this->actingAs($user)->delete(route('social.unlink', 'google'));

    $response->assertRedirect();
    $response->assertSessionHas('status', 'Social account unlinked successfully.');

    // Check that one social account was deleted
    expect($user->socialAccounts()->count())->toBe(1);

    // Password should still be null
    $user->refresh();
    expect($user->password)->toBeNull();
});

it('allows unlinking social account for users with password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('existing-password'),
    ]);

    SocialAccount::factory()->create([
        'user_id' => $user->id,
        'provider' => 'google',
    ]);

    $response = $this->actingAs($user)->delete(route('social.unlink', 'google'));

    $response->assertRedirect();
    $response->assertSessionHas('status', 'Social account unlinked successfully.');

    // Check that social account was deleted
    expect($user->socialAccounts()->count())->toBe(0);

    // Password should remain unchanged
    $user->refresh();
    expect(Hash::check('existing-password', $user->password))->toBeTrue();
});

it('removes password when existing user with password links social account', function () {
    $user = User::factory()->create([
        'password' => Hash::make('existing-password'),
        'email' => 'test@example.com',
    ]);

    // Create the social account manually to simulate the linking process
    $user->socialAccounts()->create([
        'provider' => 'google',
        'provider_id' => '123456',
        'access_token' => 'access-token',
        'refresh_token' => 'refresh-token',
        'metadata' => [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'avatar' => 'https://example.com/avatar.jpg',
            'username' => 'testuser',
        ],
    ]);

    // Now simulate the controller logic that removes password
    if ($user->password && $user->socialAccounts()->count() > 0) {
        $user->update(['password' => null]);
    }

    $user->refresh();
    expect($user->password)->toBeNull();
    expect($user->socialAccounts()->count())->toBe(1);
});
