<?php

declare(strict_types=1);

use App\Models\Location;
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
        'locations.view',
        'locations.create',
        'locations.update',
        'locations.delete',
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

describe('Location Index', function () {
    test('admin can view locations index', function () {
        Location::factory(3)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.locations.index'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/locations/index')
                ->has('locations.data', 3)
                ->has('filters')
            );
    });

    test('user without permission cannot view locations index', function () {
        $response = $this->actingAs($this->user)
            ->get(route('admin.locations.index'));

        $response->assertForbidden();
    });

    test('can filter locations by search', function () {
        Location::factory()->create(['name' => 'New York']);
        Location::factory()->create(['name' => 'Los Angeles']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.locations.index', ['search' => 'York']));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/locations/index')
                ->has('locations.data', 1)
                ->where('locations.data.0.name', 'New York')
            );
    });

    test('can filter locations by type', function () {
        Location::factory()->create(['type' => 'city']);
        Location::factory()->create(['type' => 'suburb']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.locations.index', ['type' => 'city']));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/locations/index')
                ->has('locations.data', 1)
                ->where('locations.data.0.type', 'city')
            );
    });
});

describe('Location CRUD', function () {
    test('admin can create location', function () {
        $locationData = [
            'name' => 'Test City',
            'type' => 'city',
            'state' => 'California',
            'country' => 'United States',
            'latitude' => 34.0522,
            'longitude' => -118.2437,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.locations.store'), $locationData);

        $response->assertRedirect(route('admin.locations.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('locations', [
            'name' => 'Test City',
            'type' => 'city',
            'state' => 'California',
            'is_active' => true,
        ]);
    });

    test('admin can update location', function () {
        $location = Location::factory()->create(['name' => 'Old Name']);

        $updateData = [
            'name' => 'New Name',
            'type' => 'suburb',
            'country' => 'United States',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.locations.update', $location), $updateData);

        $response->assertRedirect(route('admin.locations.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'name' => 'New Name',
            'type' => 'suburb',
        ]);
    });

    test('admin can delete location', function () {
        $location = Location::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.locations.destroy', $location));

        $response->assertRedirect(route('admin.locations.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('locations', ['id' => $location->id]);
    });

    test('user without permission cannot create location', function () {
        $locationData = [
            'name' => 'Test City',
            'type' => 'city',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('admin.locations.store'), $locationData);

        $response->assertForbidden();
    });
});

describe('Location Validation', function () {
    test('name is required', function () {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.locations.store'), [
                'type' => 'city',
                'country' => 'United States',
            ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['name']);
    });

    test('type is required', function () {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.locations.store'), [
                'name' => 'Test City',
                'country' => 'United States',
            ]);

        $response->assertSessionHasErrors(['type']);
    });

    test('name must be unique', function () {
        Location::factory()->create(['name' => 'Existing City']);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.locations.store'), [
                'name' => 'Existing City',
                'type' => 'city',
                'country' => 'United States',
            ]);

        $response->assertSessionHasErrors(['name']);
    });

    test('type must be valid enum value', function () {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.locations.store'), [
                'name' => 'Test City',
                'type' => 'invalid_type',
                'country' => 'United States',
            ]);

        $response->assertSessionHasErrors(['type']);
    });

    test('coordinates must be valid decimal values', function () {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.locations.store'), [
                'name' => 'Test City',
                'type' => 'city',
                'country' => 'United States',
                'latitude' => 'invalid',
                'longitude' => 'invalid',
            ]);

        $response->assertSessionHasErrors(['latitude', 'longitude']);
    });
});

describe('Location Model', function () {
    test('automatically generates slug from name', function () {
        $location = Location::factory()->make(['name' => 'New York City']);
        $location->slug = ''; // Clear slug to test auto generation
        $location->save();

        expect($location->slug)->toBe('new-york-city');
    });

    test('generates unique slugs', function () {
        // Create first location with specific slug
        Location::factory()->create(['name' => 'Test City', 'slug' => 'test-city']);

        // Create second location with same name but no slug
        $location2 = Location::factory()->make(['name' => 'Test City']);
        $location2->slug = ''; // Clear slug to force generation
        $location2->save();

        // Should generate unique slug since 'test-city' already exists
        expect($location2->slug)->toMatch('/test-city-\d+/');
    });

    test('has coordinates accessor', function () {
        $location = Location::factory()->create([
            'latitude' => 34.0522,
            'longitude' => -118.2437,
        ]);

        expect($location->coordinates_string)->toBe('34.0522, -118.2437');
    });

    test('has full name accessor', function () {
        $location = Location::factory()->create([
            'name' => 'Los Angeles',
            'state' => 'California',
            'country' => 'United States',
        ]);

        expect($location->full_name)->toBe('Los Angeles, California, United States');
    });

    test('can filter by active status', function () {
        Location::factory()->create(['is_active' => true]);
        Location::factory()->create(['is_active' => false]);

        $activeLocations = Location::active()->get();

        expect($activeLocations)->toHaveCount(1);
        expect($activeLocations->first()->is_active)->toBeTrue();
    });

    test('can filter by type', function () {
        Location::factory()->create(['type' => 'city']);
        Location::factory()->create(['type' => 'suburb']);

        $cities = Location::ofType('city')->get();

        expect($cities)->toHaveCount(1);
        expect($cities->first()->type->value)->toBe('city');
    });
});
