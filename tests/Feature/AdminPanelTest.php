<?php

declare(strict_types=1);

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

test('admin user can access user portal', function () {
    // Create admin role
    $adminRole = Role::firstOrCreate(['name' => 'admin']);

    // Create required permissions
    $permissions = [
        'access admin panel',
        'view dashboard',
    ];

    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission]);
    }

    $adminRole->syncPermissions($permissions);

    // Create admin user
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    // Admin trying to access user dashboard should be redirected to admin panel
    $this->actingAs($admin)
        ->get('/dashboard')
        ->assertRedirect('/admin');
});

test('admin user can access settings', function () {
    // Create admin role
    $adminRole = Role::firstOrCreate(['name' => 'admin']);

    // Create required permissions
    $permissions = [
        'access admin panel',
        'view dashboard',
    ];

    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission]);
    }

    $adminRole->syncPermissions($permissions);

    // Create admin user
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    // Admin trying to access settings should be redirected to admin panel
    $this->actingAs($admin)
        ->get('/settings/profile')
        ->assertRedirect('/admin');
});

test('regular user can access user portal', function () {
    // Create user role
    Role::firstOrCreate(['name' => 'user']);

    // Create regular user
    $user = User::factory()->create();
    $user->assignRole('user');

    // Regular user should be able to access dashboard
    $this->actingAs($user)
        ->get('/dashboard')
        ->assertStatus(200);
});

test('regular user can access settings', function () {
    // Create user role
    Role::firstOrCreate(['name' => 'user']);

    // Create regular user
    $user = User::factory()->create();
    $user->assignRole('user');

    // Regular user should be able to access settings
    $this->actingAs($user)
        ->get('/settings/profile')
        ->assertStatus(200);
});

test('admin can access admin panel', function () {
    // Create admin role
    $adminRole = Role::firstOrCreate(['name' => 'admin']);

    // Create required permissions
    $permissions = [
        'access admin panel',
        'view dashboard',
    ];

    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission]);
    }

    $adminRole->syncPermissions($permissions);

    // Create admin user
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    // Admin should be able to access admin panel
    $this->actingAs($admin)
        ->get('/admin')
        ->assertStatus(200);
});
