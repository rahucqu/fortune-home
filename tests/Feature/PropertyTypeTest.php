<?php

declare(strict_types=1);

use App\Models\PropertyType;
use App\Models\User;
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
        'property-types.view',
        'property-types.create',
        'property-types.update',
        'property-types.delete',
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

describe('PropertyType Index', function () {
    test('admin can view property types index', function () {
        PropertyType::factory(3)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.property-types.index'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/property-types/index')
                ->has('propertyTypes.data', 3)
                ->has('categories')
                ->has('filters')
            );
    });

    test('user without permission cannot view property types index', function () {
        $response = $this->actingAs($this->user)
            ->get(route('admin.property-types.index'));

        $response->assertForbidden();
    });

    test('can filter property types by search', function () {
        PropertyType::factory()->create(['name' => 'House Type']);
        PropertyType::factory()->create(['name' => 'Apartment Type']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.property-types.index', ['search' => 'House']));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/property-types/index')
                ->has('propertyTypes.data', 1)
                ->where('propertyTypes.data.0.name', 'House Type')
            );
    });

    test('can filter property types by category', function () {
        PropertyType::factory()->create(['category' => 'residential']);
        PropertyType::factory()->create(['category' => 'commercial']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.property-types.index', ['category' => 'commercial']));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/property-types/index')
                ->has('propertyTypes.data', 1)
                ->where('propertyTypes.data.0.category', 'commercial')
            );
    });
});

describe('PropertyType CRUD', function () {
    test('admin can create property type', function () {
        $propertyTypeData = [
            'name' => 'Test Property Type',
            'category' => 'residential',
            'description' => 'Test description',
            'icon' => '🏠',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.property-types.store'), $propertyTypeData);

        $response->assertRedirect(route('admin.property-types.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('property_types', [
            'name' => 'Test Property Type',
            'category' => 'residential',
            'is_active' => true,
        ]);
    });

    test('admin can update property type', function () {
        $propertyType = PropertyType::factory()->create(['name' => 'Old Name']);

        $updateData = [
            'name' => 'New Name',
            'category' => 'commercial',
            'description' => 'Updated description',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.property-types.update', $propertyType), $updateData);

        $response->assertRedirect(route('admin.property-types.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('property_types', [
            'id' => $propertyType->id,
            'name' => 'New Name',
            'category' => 'commercial',
        ]);
    });

    test('admin can delete property type', function () {
        $propertyType = PropertyType::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.property-types.destroy', $propertyType));

        $response->assertRedirect(route('admin.property-types.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('property_types', ['id' => $propertyType->id]);
    });

    test('user without permission cannot create property type', function () {
        $propertyTypeData = [
            'name' => 'Test Property Type',
            'category' => 'residential',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('admin.property-types.store'), $propertyTypeData);

        $response->assertForbidden();
    });
});

describe('PropertyType Validation', function () {
    test('name is required', function () {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.property-types.store'), [
                'category' => 'residential',
            ]);

        $response->assertSessionHasErrors(['name']);
    });

    test('category is required', function () {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.property-types.store'), [
                'name' => 'Test Property Type',
            ]);

        $response->assertSessionHasErrors(['category']);
    });

    test('name must be unique', function () {
        PropertyType::factory()->create(['name' => 'Existing Type']);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.property-types.store'), [
                'name' => 'Existing Type',
                'category' => 'residential',
            ]);

        $response->assertSessionHasErrors(['name']);
    });

    test('category must be valid enum value', function () {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.property-types.store'), [
                'name' => 'Test Property Type',
                'category' => 'invalid_category',
            ]);

        $response->assertSessionHasErrors(['category']);
    });

    test('automatically generates slug from name', function () {
        $propertyTypeData = [
            'name' => 'Complex Property Type Name!',
            'category' => 'residential',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.property-types.store'), $propertyTypeData);

        $response->assertRedirect(route('admin.property-types.index'));

        $this->assertDatabaseHas('property_types', [
            'name' => 'Complex Property Type Name!',
            'slug' => 'complex-property-type-name',
        ]);
    });
});
