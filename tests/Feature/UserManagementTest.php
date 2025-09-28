<?php

declare(strict_types=1);

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create admin role with permissions
    $adminRole = Role::create([
        'name' => 'admin',
        'display_name' => 'Administrator',
        'description' => 'System administrator with full operational access',
        'guard_name' => 'web',
        'is_default' => false,
    ]);

    // Create permissions
    $permissions = [
        'system.users.view', 'system.users.create', 'system.users.update', 'system.users.delete',
    ];

    foreach ($permissions as $permission) {
        $perm = Permission::create(['name' => $permission, 'guard_name' => 'web']);
        $adminRole->givePermissionTo($perm);
    }

    // Create admin user
    $adminUser = User::factory()->create();
    $adminUser->assignRole($adminRole);

    $this->actingAs($adminUser);
});

it('can display users index page', function () {
    $this->get(route('admin.system.users.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/system/users/index')
            ->has('users')
            ->has('roles')
            ->has('filters')
        );
});

it('can create a new user', function () {
    $role = Role::factory()->create();

    $userData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'roles' => [$role->id],
    ];

    $this->post(route('admin.system.users.store'), $userData)
        ->assertRedirect(route('admin.system.users.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('users', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);

    $user = User::where('email', 'john@example.com')->first();
    expect($user->hasRole($role))->toBeTrue();
});

it('can show user details', function () {
    $user = User::factory()->create();
    $role = Role::factory()->create();
    $user->assignRole($role);

    $this->getJson(route('admin.system.users.show', $user), [
        'X-Requested-With' => 'XMLHttpRequest',
    ])
        ->assertOk()
        ->assertJson(['user' => [
            'id' => $user->id,
            'name' => $user->name,
        ]]);
});

it('can display user creation page', function () {
    // Since we're using a sheet now, we just check that the index page loads with roles
    $this->get(route('admin.system.users.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/system/users/index')
            ->has('roles')
        );
});

it('can update a user', function () {
    $user = User::factory()->create();
    $role = Role::factory()->create();
    $user->assignRole($role);

    $newRole = Role::factory()->create(['name' => 'New Role']);

    $updateData = [
        'name' => 'Updated Name',
        'email' => $user->email,
        'roles' => [$newRole->id],
    ];

    $this->patch(route('admin.system.users.update', $user), $updateData)
        ->assertRedirect(route('admin.system.users.index'))
        ->assertSessionHas('success');

    $user->refresh();
    expect($user->name)->toBe('Updated Name');
    expect($user->hasRole($newRole))->toBeTrue();
    expect($user->hasRole($role))->toBeFalse();
});

it('can delete a user', function () {
    $user = User::factory()->create();

    $this->delete(route('admin.system.users.destroy', $user))
        ->assertRedirect(route('admin.system.users.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

it('cannot delete current user', function () {
    $currentUser = $this->app['auth']->user();

    $this->delete(route('admin.system.users.destroy', $currentUser))
        ->assertRedirect(route('admin.system.users.index'))
        ->assertSessionHas('error');

    $this->assertDatabaseHas('users', ['id' => $currentUser->id]);
});

it('validates required fields when creating user', function () {
    $this->post(route('admin.system.users.store'), [])
        ->assertSessionHasErrors(['name', 'email', 'password']);
});

it('validates unique email when creating user', function () {
    $existingUser = User::factory()->create();

    $this->post(route('admin.system.users.store'), [
        'name' => 'Test User',
        'email' => $existingUser->email,
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])
        ->assertSessionHasErrors(['email']);
});

it('can filter users by search term', function () {
    User::factory()->create(['name' => 'John Doe', 'email' => 'john@test.com']);
    User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@test.com']);

    $this->get(route('admin.system.users.index', ['search' => 'John']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('users.data', 1)
            ->where('users.data.0.name', 'John Doe')
        );
});

it('can filter users by role', function () {
    $testRole = Role::factory()->create(['name' => 'TestRole']);
    $userRole = Role::factory()->create(['name' => 'UserRole']);

    $testUser = User::factory()->create();
    $testUser->assignRole($testRole);

    $regularUser = User::factory()->create();
    $regularUser->assignRole($userRole);

    $this->get(route('admin.system.users.index', ['role' => $testRole->name]))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('users.data', 1)
        );
});
