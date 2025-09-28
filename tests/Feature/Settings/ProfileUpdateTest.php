<?php

declare(strict_types=1);

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create user role with settings permissions
    $userRole = Role::create([
        'name' => 'user',
        'display_name' => 'User',
        'description' => 'General users',
        'guard_name' => 'web',
        'is_default' => true,
    ]);

    // Create settings permissions
    $permissions = [
        'settings.password', 'settings.profile', 'settings.view', 'settings.appearance',
    ];

    foreach ($permissions as $permissionName) {
        $permission = Permission::create([
            'name' => $permissionName,
            'guard_name' => 'web',
            'group' => 'Settings & Profile',
        ]);
        $userRole->givePermissionTo($permission);
    }
});

test('profile page is displayed', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $response = $this
        ->actingAs($user)
        ->get(route('admin.settings.profile.edit'));

    $response->assertStatus(200);
});

test('profile information can be updated', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $response = $this
        ->actingAs($user)
        ->patch(route('admin.settings.profile.update'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'email_notifications' => '1',
            'sms_notifications' => '0',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.settings.profile.edit'));

    $user->refresh();

    expect($user->name)->toBe('Test User');
    expect($user->email)->toBe('test@example.com');
    expect($user->email_verified_at)->toBeNull();
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $response = $this
        ->actingAs($user)
        ->patch(route('admin.settings.profile.update'), [
            'name' => 'Test User',
            'email' => $user->email,
            'email_notifications' => '1',
            'sms_notifications' => '1',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.settings.profile.edit'));

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('user can delete their account', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $response = $this
        ->actingAs($user)
        ->delete(route('admin.settings.profile.destroy'), [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('home'));

    $this->assertGuest();
    expect($user->fresh())->toBeNull();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $response = $this
        ->actingAs($user)
        ->from(route('admin.settings.profile.edit'))
        ->delete(route('admin.settings.profile.destroy'), [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrors('password')
        ->assertRedirect(route('admin.settings.profile.edit'));

    expect($user->fresh())->not->toBeNull();
});
