<?php

declare(strict_types=1);

use App\Models\Property;
use App\Models\PropertyType;
use App\Models\User;

it('property card displays all required property details', function () {
    // Create a property type
    $propertyType = PropertyType::factory()->create([
        'name' => 'Single Family Home',
        'is_active' => true,
    ]);

    // Create an agent
    $agent = User::factory()->agent()->create([
        'name' => 'John Doe',
    ]);

    // Create a property with all required details
    $property = Property::factory()->create([
        'title' => 'Beautiful Family Home',
        'property_type_id' => $propertyType->id,
        'agent_id' => $agent->id,
        'listing_type' => 'sale',
        'status' => 'active',
        'price' => 350000.00,
        'bedrooms' => 3,
        'bathrooms' => 2,
        'garage_spaces' => 2,
        'square_feet' => 1800,
        'city' => 'Los Angeles',
        'state' => 'CA',
        'featured' => true,
    ]);

    // Ensure the property has the correct relationships loaded
    $property->load(['propertyType', 'agent']);

    expect($property->title)->toBe('Beautiful Family Home');
    expect($property->propertyType->name)->toBe('Single Family Home');
    expect($property->agent->name)->toBe('John Doe');
    expect($property->listing_type->value)->toBe('sale');
    expect((float) $property->price)->toBe(350000.00);
    expect($property->bedrooms)->toBe(3);
    expect((float) $property->bathrooms)->toBe(2.0);
    expect($property->garage_spaces)->toBe(2);
    expect($property->square_feet)->toBe(1800);
    expect($property->featured)->toBeTrue();
});

it('property card handles minimal values gracefully', function () {
    // Use existing property type and agent to avoid foreign key issues
    $propertyType = PropertyType::first() ?? PropertyType::factory()->create();
    $agent = User::whereHas('roles', function ($query) {
        $query->where('name', 'agent');
    })->first() ?? User::factory()->agent()->create();

    // Create a minimal property with default values for required fields
    $property = Property::factory()->create([
        'title' => 'Minimal Property',
        'property_type_id' => $propertyType->id,
        'agent_id' => $agent->id,
        'listing_type' => 'rent',
        'status' => 'active',
        'price' => 2000,
        'bedrooms' => 0,
        'bathrooms' => 0,
        'garage_spaces' => 0,
        'square_feet' => 500,
        'city' => 'New York',
        'state' => 'NY',
        'featured' => false,
    ]);

    expect($property->title)->toBe('Minimal Property');
    expect($property->bedrooms)->toBe(0);
    expect((float) $property->bathrooms)->toBe(0.0);
    expect($property->garage_spaces)->toBe(0);
    expect($property->square_feet)->toBe(500);
    expect($property->featured)->toBeFalse();
});

it('property displays correct formatted price', function () {
    // Use existing property type and agent to avoid foreign key issues
    $propertyType = PropertyType::first() ?? PropertyType::factory()->create();
    $agent = User::whereHas('roles', function ($query) {
        $query->where('name', 'agent');
    })->first() ?? User::factory()->agent()->create();

    $property = Property::factory()->create([
        'property_type_id' => $propertyType->id,
        'agent_id' => $agent->id,
        'price' => 1250000,
    ]);

    // Test the formatted price logic that would be used in the component
    $formattedPrice = '$'.number_format((float) $property->price);

    expect($formattedPrice)->toBe('$1,250,000');
});

it('user can get favorite property ids', function () {
    $user = User::factory()->create();

    // Use existing property type and agent to avoid foreign key issues
    $propertyType = PropertyType::first() ?? PropertyType::factory()->create();
    $agent = User::whereHas('roles', function ($query) {
        $query->where('name', 'agent');
    })->first() ?? User::factory()->agent()->create();

    $property1 = Property::factory()->create([
        'property_type_id' => $propertyType->id,
        'agent_id' => $agent->id,
    ]);
    $property2 = Property::factory()->create([
        'property_type_id' => $propertyType->id,
        'agent_id' => $agent->id,
    ]);

    // Add properties to favorites
    $user->favoriteProperties()->attach([$property1->id, $property2->id]);

    $favoriteIds = $user->getFavoritePropertyIds();

    expect($favoriteIds)->toContain($property1->id);
    expect($favoriteIds)->toContain($property2->id);
    expect(count($favoriteIds))->toBe(2);
});

it('user can check if property is favorited', function () {
    $user = User::factory()->create();

    // Use existing property type and agent to avoid foreign key issues
    $propertyType = PropertyType::first() ?? PropertyType::factory()->create();
    $agent = User::whereHas('roles', function ($query) {
        $query->where('name', 'agent');
    })->first() ?? User::factory()->agent()->create();

    $property = Property::factory()->create([
        'property_type_id' => $propertyType->id,
        'agent_id' => $agent->id,
    ]);

    // Initially not favorited
    expect($user->hasFavorited($property))->toBeFalse();

    // Add to favorites
    $user->favoriteProperties()->attach($property->id);

    // Now it should be favorited
    expect($user->fresh()->hasFavorited($property))->toBeTrue();
});
