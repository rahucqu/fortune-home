<?php

declare(strict_types=1);

use App\Models\Location;
use App\Models\Property;
use App\Models\PropertyFavorite;
use App\Models\PropertyType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create roles
    $this->userRole = Role::create(['name' => 'user', 'display_name' => 'User', 'guard_name' => 'web']);
    $this->agentRole = Role::create(['name' => 'agent', 'display_name' => 'Agent', 'guard_name' => 'web']);

    // Create permissions
    Permission::create(['name' => 'favorites.view-own', 'guard_name' => 'web']);
    Permission::create(['name' => 'favorites.add', 'guard_name' => 'web']);
    Permission::create(['name' => 'favorites.remove', 'guard_name' => 'web']);

    // Assign permissions to roles
    $this->userRole->givePermissionTo(['favorites.view-own', 'favorites.add', 'favorites.remove']);
    $this->agentRole->givePermissionTo(['favorites.view-own', 'favorites.add', 'favorites.remove']);

    // Create required related models
    $this->propertyType = PropertyType::factory()->create();
    $this->location = Location::factory()->create();
});

test('authenticated users can view their favorites page', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $this->actingAs($user)
        ->get(route('admin.favorites.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('admin/favorites/index')
                ->has('favorites')
                ->has('favorites.data')
                ->where('favorites.total', 0)
        );
});

test('favorites page shows user favorites with property details', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    // Create agent for properties
    $agent = User::factory()->create();
    $agent->assignRole('agent');

    // Create properties with proper relationships
    $properties = Property::factory()->count(3)->create([
        'status' => 'active',
        'property_type_id' => $this->propertyType->id,
        'location_id' => $this->location->id,
        'agent_id' => $agent->id,
    ]);

    // Add some favorites for this user
    foreach ($properties as $property) {
        PropertyFavorite::create([
            'user_id' => $user->id,
            'property_id' => $property->id,
        ]);
    }

    // Add some favorites for another user (should not be shown)
    $otherUser = User::factory()->create();
    $otherUser->assignRole('user');
    $otherProperty = Property::factory()->create([
        'status' => 'active',
        'property_type_id' => $this->propertyType->id,
        'location_id' => $this->location->id,
        'agent_id' => $agent->id,
    ]);
    PropertyFavorite::create([
        'user_id' => $otherUser->id,
        'property_id' => $otherProperty->id,
    ]);

    $this->actingAs($user)
        ->get(route('admin.favorites.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('admin/favorites/index')
                ->has('favorites')
                ->has('favorites.data', 3)
                ->where('favorites.total', 3)
                ->has('favorites.data.0.property.id')
                ->has('favorites.data.0.property.title')
                ->has('favorites.data.0.property.price')
        );
});

test('favorites page only shows active properties', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    // Create agent for properties
    $agent = User::factory()->create();
    $agent->assignRole('agent');

    $activeProperty = Property::factory()->create([
        'status' => 'active',
        'property_type_id' => $this->propertyType->id,
        'location_id' => $this->location->id,
        'agent_id' => $agent->id,
    ]);
    $inactiveProperty = Property::factory()->create([
        'status' => 'inactive',
        'property_type_id' => $this->propertyType->id,
        'location_id' => $this->location->id,
        'agent_id' => $agent->id,
    ]);

    // Add favorites for both properties
    PropertyFavorite::create([
        'user_id' => $user->id,
        'property_id' => $activeProperty->id,
    ]);

    PropertyFavorite::create([
        'user_id' => $user->id,
        'property_id' => $inactiveProperty->id,
    ]);

    $this->actingAs($user)
        ->get(route('admin.favorites.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('admin/favorites/index')
                ->has('favorites')
                ->has('favorites.data', 1) // Only active property shown
                ->where('favorites.total', 1)
                ->where('favorites.data.0.property.id', $activeProperty->id)
        );
});

test('favorites page requires authentication', function () {
    $this->get(route('admin.favorites.index'))
        ->assertRedirect(route('login'));
});

test('favorites page requires permission', function () {
    $user = User::factory()->create();
    // Don't assign any role - user should not have permission

    $this->actingAs($user)
        ->get(route('admin.favorites.index'))
        ->assertForbidden();
});

test('favorites page paginates results', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    // Create agent for properties
    $agent = User::factory()->create();
    $agent->assignRole('agent');

    // Create 15 properties
    $properties = Property::factory()->count(15)->create([
        'status' => 'active',
        'property_type_id' => $this->propertyType->id,
        'location_id' => $this->location->id,
        'agent_id' => $agent->id,
    ]);

    // Add favorites for all properties
    foreach ($properties as $property) {
        PropertyFavorite::create([
            'user_id' => $user->id,
            'property_id' => $property->id,
        ]);
    }

    $this->actingAs($user)
        ->get(route('admin.favorites.index'))
        ->assertOk()
        ->assertInertia(
            fn (Assert $page) => $page
                ->component('admin/favorites/index')
                ->has('favorites')
                ->has('favorites.data', 12) // Default per page
                ->where('favorites.total', 15)
                ->where('favorites.current_page', 1)
                ->where('favorites.last_page', 2)
        );
});
