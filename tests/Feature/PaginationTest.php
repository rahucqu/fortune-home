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

it('displays simple pagination on roles page', function () {
    // Create enough roles to trigger pagination (admin role already exists from beforeEach)
    Role::factory()->count(20)->create();

    $response = $this->get(route('admin.system.roles.index'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/system/roles/index')
            ->has('roles.data', 15) // Should show 15 per page (default)
            ->has('roles.next_page_url') // Should have next page URL
            ->where('roles.current_page', 1)
            ->where('roles.per_page', 15)
        );
});

it('paginates to second page correctly', function () {
    // Clear existing roles first, then create test data
    Role::factory()->count(20)->create();

    $response = $this->get(route('admin.system.roles.index', ['page' => 2]));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/system/roles/index')
            ->has('roles.data', 6) // Should show remaining 6 roles on page 2 (20 + 1 admin = 21 total, 15 on page 1, 6 on page 2)
            ->has('roles.prev_page_url') // Should have previous page URL
            ->where('roles.current_page', 2)
        );
});

it('respects per_page parameter', function () {
    // Clear existing roles first, then create test data
    Role::factory()->count(30)->create();

    $response = $this->get(route('admin.system.roles.index', ['per_page' => 25]));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('roles.data', 25) // Should show 25 per page
            ->where('roles.per_page', 25)
            ->has('roles.next_page_url') // Should have next page since 30 > 25
        );
});
