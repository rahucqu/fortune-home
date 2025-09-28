<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\patchJson;
use function Pest\Laravel\seed;

it('can update profile with checkbox notification settings', function () {
    seed(RolePermissionSeeder::class);

    /** @var User $user */
    $user = User::factory()->create([
        'email_notifications' => false,
        'sms_notifications' => false,
    ]);
    $user->assignRole('user');

    actingAs($user);

    $response = patchJson('/panel/settings/profile', [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
        'phone' => '1234567890',
        'email_notifications' => '1', // Checked checkbox
        'sms_notifications' => '0',   // Unchecked checkbox
    ]);

    $response->assertRedirect('/panel/settings/profile');

    $user->refresh();

    expect($user->name)->toBe('Updated Name');
    expect($user->email)->toBe('updated@example.com');
    expect($user->phone)->toBe('1234567890');
    expect($user->email_notifications)->toBe(true);
    expect($user->sms_notifications)->toBe(false);
});

it('can update profile with unchecked notification settings', function () {
    seed(RolePermissionSeeder::class);

    /** @var User $user */
    $user = User::factory()->create([
        'email_notifications' => true,
        'sms_notifications' => true,
    ]);
    $user->assignRole('user');

    actingAs($user);

    $response = patchJson('/panel/settings/profile', [
        'name' => $user->name,
        'email' => $user->email,
        'email_notifications' => '0', // Unchecked checkbox
        'sms_notifications' => '0',   // Unchecked checkbox
    ]);

    $response->assertRedirect('/panel/settings/profile');

    $user->refresh();

    expect($user->email_notifications)->toBe(false);
    expect($user->sms_notifications)->toBe(false);
});

it('validates checkbox notification settings correctly', function () {
    seed(RolePermissionSeeder::class);

    /** @var User $user */
    $user = User::factory()->create();
    $user->assignRole('user');

    actingAs($user);

    $response = patchJson('/panel/settings/profile', [
        'name' => $user->name,
        'email' => $user->email,
        'email_notifications' => 'invalid', // Invalid value
        'sms_notifications' => '',          // Missing value
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors([
            'email_notifications',
            'sms_notifications',
        ]);
});

it('resets email verification when email is changed', function () {
    seed(RolePermissionSeeder::class);

    /** @var User $user */
    $user = User::factory()->create([
        'email' => 'original@example.com',
        'email_verified_at' => now(),
    ]);
    $user->assignRole('user');

    actingAs($user);

    $response = patchJson('/panel/settings/profile', [
        'name' => $user->name,
        'email' => 'new@example.com',
        'email_notifications' => '1',
        'sms_notifications' => '1',
    ]);

    $response->assertRedirect('/panel/settings/profile');

    $user->refresh();

    expect($user->email)->toBe('new@example.com');
    expect($user->email_verified_at)->toBeNull();
});
