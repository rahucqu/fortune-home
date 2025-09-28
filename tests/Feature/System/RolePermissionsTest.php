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

    // Create permissions for testing
    $this->permissions = collect([
        ['name' => 'system.roles.view', 'group' => 'System Administration'],
        ['name' => 'system.roles.create', 'group' => 'System Administration'],
        ['name' => 'system.roles.update', 'group' => 'System Administration'],
        ['name' => 'system.roles.delete', 'group' => 'System Administration'],
        ['name' => 'system.users.view', 'group' => 'System Administration'],
        ['name' => 'settings.view', 'group' => 'Settings & Profile'],
        ['name' => 'settings.profile', 'group' => 'Settings & Profile'],
    ])->map(function ($permData) {
        return Permission::create(array_merge($permData, ['guard_name' => 'web']));
    });

    // Give admin role all permissions
    $adminRole->givePermissionTo($this->permissions);

    // Create admin user
    $this->user = User::factory()->create();
    $this->user->assignRole($adminRole);

    $this->actingAs($this->user);
});

test('it can create a role with permissions', function () {
    $permissionIds = $this->permissions->take(3)->pluck('id')->toArray();

    $response = $this->post(route('admin.system.roles.store'), [
        'name' => 'test_role',
        'display_name' => 'Test Role Display',
        'description' => 'A test role for testing',
        'guard_name' => 'web',
        'permissions' => $permissionIds,
    ]);

    $response->assertRedirect(route('admin.system.roles.store'))
        ->assertSessionHas('success', 'Role created successfully.');

    $role = Role::where('name', 'test_role')->first();
    expect($role)->not->toBeNull();
    expect($role->display_name)->toBe('Test Role Display');
    expect($role->description)->toBe('A test role for testing');
    expect($role->permissions)->toHaveCount(3);
    expect($role->permissions->pluck('id')->toArray())->toEqual($permissionIds);
});

test('it can create a role without permissions', function () {
    $response = $this->post(route('admin.system.roles.store'), [
        'name' => 'empty_role',
        'display_name' => 'Empty Role Display',
        'description' => 'A role with no permissions',
        'guard_name' => 'web',
        'permissions' => [],
    ]);

    $response->assertRedirect(route('admin.system.roles.store'))
        ->assertSessionHas('success', 'Role created successfully.');

    $role = Role::where('name', 'empty_role')->first();
    expect($role)->not->toBeNull();
    expect($role->permissions)->toHaveCount(0);
});

test('it can update a role and its permissions', function () {
    $role = Role::create([
        'name' => 'update_test_role',
        'display_name' => 'Update Test Role Display',
        'description' => 'Original description',
        'guard_name' => 'web',
        'is_default' => false,
    ]);

    $originalPermissions = $this->permissions->take(2);
    $role->givePermissionTo($originalPermissions);

    $newPermissionIds = $this->permissions->take(4)->pluck('id')->toArray();

    $response = $this->patch(route('admin.system.roles.update', $role), [
        'name' => 'updated_role_name',
        'display_name' => 'Updated Role Display Name',
        'description' => 'Updated description',
        'guard_name' => 'web',
        'permissions' => $newPermissionIds,
    ]);

    $response->assertRedirect(route('admin.system.roles.store'))
        ->assertSessionHas('success', 'Role updated successfully.');

    $role->refresh();
    expect($role->name)->toBe('updated_role_name');
    expect($role->display_name)->toBe('Updated Role Display Name');
    expect($role->description)->toBe('Updated description');
    expect($role->permissions)->toHaveCount(4);
    expect($role->permissions->pluck('id')->sort()->values()->toArray())
        ->toEqual(collect($newPermissionIds)->sort()->values()->toArray());
});

test('it can remove all permissions from a role', function () {
    $role = Role::create([
        'name' => 'role_with_permissions',
        'display_name' => 'Role With Permissions Display',
        'guard_name' => 'web',
        'is_default' => false,
    ]);

    $role->givePermissionTo($this->permissions->take(3));
    expect($role->permissions)->toHaveCount(3);

    $response = $this->patch(route('admin.system.roles.update', $role), [
        'name' => 'role_with_permissions',
        'display_name' => 'Role With Permissions Display',
        'guard_name' => 'web',
        'permissions' => [],
    ]);

    $response->assertRedirect(route('admin.system.roles.store'));

    $role->refresh();
    expect($role->permissions)->toHaveCount(0);
});

test('it validates required fields when creating a role', function () {
    $response = $this->post(route('admin.system.roles.store'), [
        'name' => '',
        'display_name' => '',
        'guard_name' => 'web',
        'permissions' => [],
    ]);

    $response->assertSessionHasErrors(['name', 'display_name']);
});

test('it validates unique role name when creating', function () {
    Role::create([
        'name' => 'Existing Role',
        'display_name' => 'Existing Role Display',
        'guard_name' => 'web',
        'is_default' => false,
    ]);

    $response = $this->post(route('admin.system.roles.store'), [
        'name' => 'Existing Role',
        'display_name' => 'Different Display Name',
        'guard_name' => 'web',
        'permissions' => [],
    ]);

    $response->assertSessionHasErrors(['name']);
});

test('it validates permission IDs when creating a role', function () {
    $response = $this->post(route('admin.system.roles.store'), [
        'name' => 'Test Role',
        'display_name' => 'Test Role Display',
        'guard_name' => 'web',
        'permissions' => [999999], // Non-existent permission ID
    ]);

    $response->assertSessionHasErrors(['permissions.0']);
});

test('it prevents updating default roles', function () {
    $defaultRole = Role::create([
        'name' => 'default_role',
        'display_name' => 'Default Role Display',
        'guard_name' => 'web',
        'is_default' => true,
    ]);

    $response = $this->patch(route('admin.system.roles.update', $defaultRole), [
        'name' => 'updated_default_role',
        'display_name' => 'Updated Default Role Display',
        'guard_name' => 'web',
        'permissions' => [],
    ]);

    $response->assertRedirect(route('admin.system.roles.store'))
        ->assertSessionHas('error', 'Default roles cannot be updated.');
});

test('it can delete a non-default role', function () {
    $role = Role::create([
        'name' => 'deletable_role',
        'display_name' => 'Deletable Role Display',
        'guard_name' => 'web',
        'is_default' => false,
    ]);

    $response = $this->delete(route('admin.system.roles.update', $role));

    $response->assertRedirect(route('admin.system.roles.store'))
        ->assertSessionHas('success', 'Role deleted successfully.');

    expect(Role::find($role->id))->toBeNull();
});

test('it prevents deletion of roles assigned to users', function () {
    $role = Role::create([
        'name' => 'role_with_users',
        'display_name' => 'Role With Users',
        'guard_name' => 'web',
        'is_default' => false,
    ]);

    // Assign the role to the test user
    $this->user->assignRole($role);

    $response = $this->delete(route('admin.system.roles.update', $role));

    $response->assertRedirect(route('admin.system.roles.store'))
        ->assertSessionHas('error');

    // Verify the role still exists
    expect(Role::find($role->id))->not->toBeNull();

    // Verify the error message mentions users
    $errorMessage = session('error');
    expect($errorMessage)->toContain('assigned to');
    expect($errorMessage)->toContain('user');
});

test('it prevents deletion of default roles', function () {
    $defaultRole = Role::create([
        'name' => 'default_system_role',
        'display_name' => 'Default System Role',
        'guard_name' => 'web',
        'is_default' => true,
    ]);

    $response = $this->delete(route('admin.system.roles.update', $defaultRole));

    $response->assertRedirect(route('admin.system.roles.store'))
        ->assertSessionHas('error');

    // Verify the role still exists
    expect(Role::find($defaultRole->id))->not->toBeNull();

    // Verify the error message mentions default role
    $errorMessage = session('error');
    expect($errorMessage)->toContain('default role');
});

test('it displays roles with their permissions grouped correctly', function () {
    $role = Role::create([
        'name' => 'Test Role',
        'display_name' => 'Test Role Display',
        'description' => 'Test description',
        'guard_name' => 'web',
        'is_default' => false,
    ]);

    $role->givePermissionTo($this->permissions);

    $response = $this->get(route('admin.system.roles.edit', $role));

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('admin/system/roles/create-edit')
            ->has('permissions')
            ->has('permissions.System Administration')
            ->has('permissions.Settings & Profile')
            ->where('role.name', 'Test Role')
            ->where('role.display_name', 'Test Role Display')
            ->where('role.description', 'Test description')
        );
});

test('it shows role details correctly', function () {
    $role = Role::create([
        'name' => 'Detailed Role',
        'display_name' => 'Detailed Role Display',
        'description' => 'A role with details',
        'guard_name' => 'web',
        'is_default' => false,
    ]);

    $role->givePermissionTo($this->permissions->take(3));

    $response = $this->get(route('admin.system.roles.show', $role));

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('admin/system/roles/show')
            ->where('role.name', 'Detailed Role')
            ->where('role.display_name', 'Detailed Role Display')
            ->where('role.description', 'A role with details')
            ->has('role.permissions', 3)
        );
});

test('it validates description length when creating a role', function () {
    $longDescription = str_repeat('a', 501); // Exceeds max length of 500

    $response = $this->post(route('admin.system.roles.store'), [
        'name' => 'test_role',
        'display_name' => 'Test Role Display',
        'description' => $longDescription,
        'guard_name' => 'web',
        'permissions' => [],
    ]);

    $response->assertSessionHasErrors(['description']);
});

test('it validates role name format when creating a role', function () {
    // Test with spaces (should fail)
    $response = $this->post(route('admin.system.roles.store'), [
        'name' => 'Test Role',
        'display_name' => 'Test Role Display',
        'description' => 'Test description',
        'guard_name' => 'web',
        'permissions' => [],
    ]);

    $response->assertSessionHasErrors(['name']);

    // Test with uppercase letters (should fail)
    $response = $this->post(route('admin.system.roles.store'), [
        'name' => 'TestRole',
        'display_name' => 'Test Role Display',
        'description' => 'Test description',
        'guard_name' => 'web',
        'permissions' => [],
    ]);

    $response->assertSessionHasErrors(['name']);

    // Test with special characters (should fail)
    $response = $this->post(route('admin.system.roles.store'), [
        'name' => 'test@role',
        'display_name' => 'Test Role Display',
        'description' => 'Test description',
        'guard_name' => 'web',
        'permissions' => [],
    ]);

    $response->assertSessionHasErrors(['name']);

    // Test valid formats (should pass)
    $validNames = ['test_role_1', 'admin.user', 'role123', 'simple_role_name'];

    foreach ($validNames as $name) {
        $response = $this->post(route('admin.system.roles.store'), [
            'name' => $name,
            'display_name' => 'Valid Role Display',
            'description' => 'Valid description',
            'guard_name' => 'web',
            'permissions' => [],
        ]);

        $response->assertRedirect(route('admin.system.roles.store'));
    }
});
