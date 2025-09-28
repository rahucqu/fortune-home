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
        'system.roles.view', 'system.roles.create', 'system.roles.update', 'system.roles.delete',
    ];

    foreach ($permissions as $permission) {
        $perm = Permission::create(['name' => $permission, 'guard_name' => 'web']);
        $adminRole->givePermissionTo($perm);
    }

    // Create admin user
    $this->user = User::factory()->create();
    $this->user->assignRole($adminRole);

    $this->actingAs($this->user);
});

it('can display the roles index page', function () {
    $response = $this->get(route('admin.system.roles.index'));

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('admin/system/roles/index')
            ->has('roles')
            ->has('filters')
        );
});

it('can search roles by name', function () {
    Role::factory()->create(['name' => 'Administrator']);
    Role::factory()->create(['name' => 'User']);

    $response = $this->get(route('admin.system.roles.index', ['search' => 'User']));

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('admin/system/roles/index')
            ->where('filters.search', 'User')
            ->has('roles.data', 1)
        );
});

it('can filter roles by status', function () {
    Role::factory()->create(['name' => 'Default Role', 'is_default' => true]);
    Role::factory()->create(['name' => 'Custom Role', 'is_default' => false]);

    $response = $this->get(route('admin.system.roles.index', ['is_default' => 1]));

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('admin/system/roles/index')
            ->where('filters.is_default', '1')
            ->has('roles.data', 1)
        );
});

it('can sort roles by created date', function () {
    $olderRole = Role::factory()->create(['name' => 'Older Role', 'created_at' => now()->subDay()]);
    $newerRole = Role::factory()->create(['name' => 'Newer Role', 'created_at' => now()]);

    $response = $this->get(route('admin.system.roles.index', ['sort' => 'created_at']));

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('admin/system/roles/index')
            ->where('filters.sort', 'created_at')
            ->has('roles.data')
        );
});

it('displays filter badges when filters are applied', function () {
    $response = $this->get(route('admin.system.roles.index', ['search' => 'test', 'is_default' => 0, 'sort' => 'created_at']));

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('admin/system/roles/index')
            ->where('filters.search', 'test')
            ->where('filters.is_default', '0')
            ->where('filters.sort', 'created_at')
        );
});

it('can clear all filters', function () {
    $response = $this->get(route('admin.system.roles.index'));

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('admin/system/roles/index')
            ->where('filters.search', null)
            ->where('filters.is_default', null)
            ->where('filters.sort', 'name')
        );
});
