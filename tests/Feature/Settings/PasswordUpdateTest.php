<?php

declare(strict_types=1);

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

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

test('password update page is displayed', function () {
    $user = User::factory()->create();

    // Assign user role to give settings.password permission
    $userRole = Role::where('name', 'user')->first();
    $user->assignRole($userRole);

    $response = $this
        ->actingAs($user)
        ->get(route('admin.settings.password.edit'));

    $response->assertStatus(200);
});

test('password can be updated', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $response = $this
        ->actingAs($user)
        ->from(route('admin.settings.password.edit'))
        ->put(route('admin.settings.password.update'), [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.settings.password.edit'));

    expect(Hash::check('new-password', $user->refresh()->password))->toBeTrue();
});

test('correct password must be provided to update password', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $response = $this
        ->actingAs($user)
        ->from(route('admin.settings.password.edit'))
        ->put(route('admin.settings.password.update'), [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $response
        ->assertSessionHasErrors('current_password')
        ->assertRedirect(route('admin.settings.password.edit'));
});
