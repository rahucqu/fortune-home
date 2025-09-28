<?php

declare(strict_types=1);

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create user role
    $userRole = Role::create([
        'name' => 'user',
        'display_name' => 'User',
        'description' => 'General users',
        'guard_name' => 'web',
        'is_default' => true,
    ]);

    // Create basic permissions needed for dashboard access (can be empty for this test)
    $permission = Permission::create([
        'name' => 'settings.view',
        'guard_name' => 'web',
    ]);

    $userRole->givePermissionTo($permission);
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('admin.dashboard', absolute: false));
});
