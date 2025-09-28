<?php

declare(strict_types=1);

use App\Models\Amenity;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create roles and permissions
    $this->adminRole = Role::create(['name' => 'admin', 'display_name' => 'Administrator', 'guard_name' => 'web']);
    $this->userRole = Role::create(['name' => 'user', 'display_name' => 'User', 'guard_name' => 'web']);

    // Create permissions
    $permissions = [
        'amenities.view',
        'amenities.create',
        'amenities.update',
        'amenities.delete',
    ];

    foreach ($permissions as $permission) {
        Permission::create(['name' => $permission, 'guard_name' => 'web']);
    }

    $this->adminRole->givePermissionTo($permissions);

    // Create users
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    $this->user = User::factory()->create();
    $this->user->assignRole('user');
});

describe('Amenity Index', function () {
    test('admin can view amenities index', function () {
        Amenity::factory(3)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.amenities.index'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/amenities/index')
                ->has('amenities.data', 3)
                ->has('categories')
                ->has('filters')
            );
    });

    test('user without permission cannot view amenities index', function () {
        $response = $this->actingAs($this->user)
            ->get(route('admin.amenities.index'));

        $response->assertForbidden();
    });

    test('can filter amenities by search', function () {
        Amenity::factory()->create(['name' => 'Swimming Pool']);
        Amenity::factory()->create(['name' => 'Gym']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.amenities.index', ['search' => 'Pool']));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/amenities/index')
                ->has('amenities.data', 1)
                ->where('amenities.data.0.name', 'Swimming Pool')
            );
    });

    test('can filter amenities by category', function () {
        Amenity::factory()->create(['category' => 'general']);
        Amenity::factory()->create(['category' => 'outdoor']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.amenities.index', ['category' => 'outdoor']));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/amenities/index')
                ->has('amenities.data', 1)
                ->where('amenities.data.0.category', 'outdoor')
            );
    });
});

describe('Amenity CRUD', function () {
    test('admin can create amenity', function () {
        $amenityData = [
            'name' => 'Test Amenity',
            'category' => 'general',
            'description' => 'Test description',
            'icon' => '🏊',
            'is_active' => true,
            'sort_order' => 10,
        ];

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(VerifyCsrfToken::class)
            ->post(route('admin.amenities.store'), $amenityData);

        $response->assertRedirect(route('admin.amenities.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('amenities', [
            'name' => 'Test Amenity',
            'category' => 'general',
            'is_active' => true,
        ]);
    });

    test('admin can update amenity', function () {
        $amenity = Amenity::factory()->create(['name' => 'Old Name']);

        $updateData = [
            'name' => 'New Name',
            'category' => 'outdoor',
            'description' => 'Updated description',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(VerifyCsrfToken::class)
            ->put(route('admin.amenities.update', $amenity), $updateData);

        $response->assertRedirect(route('admin.amenities.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('amenities', [
            'id' => $amenity->id,
            'name' => 'New Name',
            'category' => 'outdoor',
        ]);
    });

    test('admin can delete amenity', function () {
        $amenity = Amenity::factory()->create();

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(VerifyCsrfToken::class)
            ->delete(route('admin.amenities.destroy', $amenity));

        $response->assertRedirect(route('admin.amenities.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('amenities', ['id' => $amenity->id]);
    });

    test('user without permission cannot create amenity', function () {
        $amenityData = [
            'name' => 'Test Amenity',
            'category' => 'general',
        ];

        $response = $this->actingAs($this->user)
            ->withoutMiddleware(VerifyCsrfToken::class)
            ->post(route('admin.amenities.store'), $amenityData);

        $response->assertForbidden();
    });
});

describe('Amenity Validation', function () {
    test('name is required', function () {
        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(VerifyCsrfToken::class)
            ->post(route('admin.amenities.store'), [
                'category' => 'general',
            ]);

        $response->assertSessionHasErrors(['name']);
    });

    test('category is required', function () {
        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(VerifyCsrfToken::class)
            ->post(route('admin.amenities.store'), [
                'name' => 'Test Amenity',
            ]);

        $response->assertSessionHasErrors(['category']);
    });

    test('name must be unique', function () {
        Amenity::factory()->create(['name' => 'Existing Amenity']);

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(VerifyCsrfToken::class)
            ->post(route('admin.amenities.store'), [
                'name' => 'Existing Amenity',
                'category' => 'general',
            ]);

        $response->assertSessionHasErrors(['name']);
    });

    test('category must be valid enum value', function () {
        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(VerifyCsrfToken::class)
            ->post(route('admin.amenities.store'), [
                'name' => 'Test Amenity',
                'category' => 'invalid_category',
            ]);

        $response->assertSessionHasErrors(['category']);
    });
});
