<?php

declare(strict_types=1);

use App\Enums\LocationType;
use App\Enums\PropertyTypeCategory;
use App\Models\Location;
use App\Models\Property;
use App\Models\PropertyTour;
use App\Models\PropertyType;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\post;
use function Pest\Laravel\seed;

it('creates a tour schedule for an existing user', function () {
    // Seed the roles and permissions
    seed(RolePermissionSeeder::class);

    // Create required related models first
    $location = Location::factory()->create([
        'type' => LocationType::CITY,
        'name' => 'Test City',
        'is_active' => true,
    ]);

    $propertyType = PropertyType::factory()->create([
        'name' => 'Test Property Type',
        'category' => PropertyTypeCategory::RESIDENTIAL,
    ]);

    // Create a user with agent role
    $agent = User::factory()->create();
    $agent->assignRole('agent');

    // Create a regular user
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'johndoe@example.com',
    ]);

    // Create property with explicit values for foreign keys
    $property = Property::factory()->create([
        'property_type_id' => $propertyType->id,
        'location_id' => $location->id,
        'agent_id' => $agent->id,
    ]);

    // Submit the tour schedule request
    post(route('properties.schedule-tour'), [
        'property_id' => $property->id,
        'name' => 'John Doe',
        'email' => 'johndoe@example.com',
        'date' => '2025-10-15',
        'time' => '14:00',
        'message' => 'I would like to see this property',
    ])
        ->assertRedirect()
        ->assertSessionHas('success');

    // Check if the tour was created with the existing user
    assertDatabaseHas('property_tours', [
        'property_id' => $property->id,
        'user_id' => $user->id,
        'message' => 'I would like to see this property',
    ]);

    // Get the tour and verify it exists
    $tour = PropertyTour::where('property_id', $property->id)
        ->where('user_id', $user->id)
        ->first();
    expect($tour)->not->toBeNull();
});

it('creates a new user when scheduling a tour with new email', function () {
    // Seed the roles and permissions
    seed(RolePermissionSeeder::class);

    // Create required related models first
    $location = Location::factory()->create([
        'type' => LocationType::CITY,
        'name' => 'Test City',
        'is_active' => true,
    ]);

    $propertyType = PropertyType::factory()->create([
        'name' => 'Test Property Type',
        'category' => PropertyTypeCategory::RESIDENTIAL,
    ]);

    // Create the agent role if it doesn't exist
    if (! Role::where('name', 'agent')->exists()) {
        Role::create([
            'name' => 'agent',
            'display_name' => 'Real Estate Agent',
            'description' => 'Licensed real estate agent',
            'guard_name' => 'web',
            'is_default' => true,
        ]);
    }

    // Create user role if it doesn't exist
    if (! Role::where('name', 'user')->exists()) {
        Role::create([
            'name' => 'user',
            'display_name' => 'User',
            'description' => 'Regular user',
            'guard_name' => 'web',
            'is_default' => true,
        ]);
    }

    // Create a user with agent role
    $agent = User::factory()->create();
    $agent->assignRole('agent');

    // Create property with explicit values for foreign keys
    $property = Property::factory()->create([
        'property_type_id' => $propertyType->id,
        'location_id' => $location->id,
        'agent_id' => $agent->id,
    ]);

    // Make sure the email doesn't exist yet
    $email = 'newuser@example.com';
    expect(User::where('email', $email)->exists())->toBeFalse();

    // Submit the tour schedule request with a new email
    post(route('properties.schedule-tour'), [
        'property_id' => $property->id,
        'name' => 'New User',
        'email' => $email,
        'date' => '2025-10-15',
        'time' => '14:00',
        'message' => 'I would like to see this property',
    ])
        ->assertRedirect()
        ->assertSessionHas('success');

    // Check if a new user was created
    $newUser = User::where('email', $email)->first();
    expect($newUser)->not->toBeNull();
    expect($newUser->name)->toBe('New User');

    // Check if the user has the user role
    expect($newUser->hasRole('user'))->toBeTrue();

    // Check if the tour was created with the new user
    assertDatabaseHas('property_tours', [
        'property_id' => $property->id,
        'user_id' => $newUser->id,
        'message' => 'I would like to see this property',
    ]);

    // Get the tour and verify it exists
    $tour = PropertyTour::where('property_id', $property->id)
        ->where('user_id', $newUser->id)
        ->first();
    expect($tour)->not->toBeNull();
});

it('validates required fields when scheduling a tour', function () {
    // Seed the roles and permissions
    seed(RolePermissionSeeder::class);

    // Create required related models first
    $location = Location::factory()->create([
        'type' => LocationType::CITY,
        'name' => 'Test City',
        'is_active' => true,
    ]);

    $propertyType = PropertyType::factory()->create([
        'name' => 'Test Property Type',
        'category' => PropertyTypeCategory::RESIDENTIAL,
    ]);

    // Create the agent role if it doesn't exist
    if (! Role::where('name', 'agent')->exists()) {
        Role::create([
            'name' => 'agent',
            'display_name' => 'Real Estate Agent',
            'description' => 'Licensed real estate agent',
            'guard_name' => 'web',
            'is_default' => true,
        ]);
    }

    // Create user role if it doesn't exist
    if (! Role::where('name', 'user')->exists()) {
        Role::create([
            'name' => 'user',
            'display_name' => 'User',
            'description' => 'Regular user',
            'guard_name' => 'web',
            'is_default' => true,
        ]);
    }

    // Create a user with agent role
    $agent = User::factory()->create();
    $agent->assignRole('agent');

    // Create property with explicit values for foreign keys
    $property = Property::factory()->create([
        'property_type_id' => $propertyType->id,
        'location_id' => $location->id,
        'agent_id' => $agent->id,
    ]);

    // Submit without required fields
    post(route('properties.schedule-tour'), [
        'property_id' => $property->id,
        // Missing name, email, date, time
    ])
        ->assertRedirect()
        ->assertSessionHasErrors(['name', 'email', 'date', 'time']);
});

it('validates date is in the future', function () {
    // Seed the roles and permissions
    seed(RolePermissionSeeder::class);

    // Create required related models first
    $location = Location::factory()->create([
        'type' => LocationType::CITY,
        'name' => 'Test City',
        'is_active' => true,
    ]);

    $propertyType = PropertyType::factory()->create([
        'name' => 'Test Property Type',
        'category' => PropertyTypeCategory::RESIDENTIAL,
    ]);

    // Create the agent role if it doesn't exist
    if (! Role::where('name', 'agent')->exists()) {
        Role::create([
            'name' => 'agent',
            'display_name' => 'Real Estate Agent',
            'description' => 'Licensed real estate agent',
            'guard_name' => 'web',
            'is_default' => true,
        ]);
    }

    // Create user role if it doesn't exist
    if (! Role::where('name', 'user')->exists()) {
        Role::create([
            'name' => 'user',
            'display_name' => 'User',
            'description' => 'Regular user',
            'guard_name' => 'web',
            'is_default' => true,
        ]);
    }

    // Create a user with agent role
    $agent = User::factory()->create();
    $agent->assignRole('agent');

    // Create property with explicit values for foreign keys
    $property = Property::factory()->create([
        'property_type_id' => $propertyType->id,
        'location_id' => $location->id,
        'agent_id' => $agent->id,
    ]);

    // Submit with a past date
    post(route('properties.schedule-tour'), [
        'property_id' => $property->id,
        'name' => 'John Doe',
        'email' => 'johndoe@example.com',
        'date' => '2020-01-01', // Past date
        'time' => '14:00',
    ])
        ->assertRedirect()
        ->assertSessionHasErrors(['date']);
});
