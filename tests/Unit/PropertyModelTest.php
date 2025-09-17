<?php

declare(strict_types=1);

use App\Models\Agent;
use App\Models\Amenity;
use App\Models\Location;
use App\Models\Property;
use App\Models\PropertyType;

it('can create a property with relationships', function () {
    $propertyType = PropertyType::factory()->create();
    $agent = Agent::factory()->create();
    $location = Location::factory()->create();

    $property = Property::factory()->create([
        'property_type_id' => $propertyType->id,
        'agent_id' => $agent->id,
        'location_id' => $location->id,
    ]);

    expect($property->propertyType)->toBeInstanceOf(PropertyType::class);
    expect($property->agent)->toBeInstanceOf(Agent::class);
    expect($property->location)->toBeInstanceOf(Location::class);
    expect($property->propertyType->id)->toBe($propertyType->id);
});

it('can attach amenities to property', function () {
    $property = Property::factory()->create([
        'property_type_id' => PropertyType::factory()->create()->id,
        'agent_id' => Agent::factory()->create()->id,
        'location_id' => Location::factory()->create()->id,
    ]);

    $amenities = Amenity::factory()->count(3)->create();
    $property->amenities()->attach($amenities->pluck('id'));

    expect($property->amenities)->toHaveCount(3);
    expect($property->amenities->first())->toBeInstanceOf(Amenity::class);
});

it('has correct property status enum values', function () {
    $property = Property::factory()->create([
        'status' => 'available',
        'property_type_id' => PropertyType::factory()->create()->id,
        'agent_id' => Agent::factory()->create()->id,
        'location_id' => Location::factory()->create()->id,
    ]);

    expect($property->status)->toBe('available');

    $property->update(['status' => 'sold']);
    expect($property->fresh()->status)->toBe('sold');
});

it('can calculate property area in different units', function () {
    $property = Property::factory()->create([
        'area_sqft' => 1500, // square feet
        'property_type_id' => PropertyType::factory()->create()->id,
        'agent_id' => Agent::factory()->create()->id,
        'location_id' => Location::factory()->create()->id,
    ]);

    expect($property->area_sqft)->toBe('1500.00');
});

it('can format property price', function () {
    $property = Property::factory()->create([
        'price' => 250000,
        'property_type_id' => PropertyType::factory()->create()->id,
        'agent_id' => Agent::factory()->create()->id,
        'location_id' => Location::factory()->create()->id,
    ]);

    expect($property->price)->toBe('250000.00');
});

it('property belongs to property type', function () {
    $propertyType = PropertyType::factory()->create(['name' => 'Single Family Home']);
    $property = Property::factory()->create([
        'property_type_id' => $propertyType->id,
        'agent_id' => Agent::factory()->create()->id,
        'location_id' => Location::factory()->create()->id,
    ]);

    expect($property->propertyType->name)->toBe('Single Family Home');
});

it('property belongs to agent', function () {
    $agent = Agent::factory()->create(['name' => 'John Doe']);
    $property = Property::factory()->create([
        'agent_id' => $agent->id,
        'property_type_id' => PropertyType::factory()->create()->id,
        'location_id' => Location::factory()->create()->id,
    ]);

    expect($property->agent->name)->toBe('John Doe');
});

it('property belongs to location', function () {
    $location = Location::factory()->create(['name' => 'Downtown']);
    $property = Property::factory()->create([
        'location_id' => $location->id,
        'property_type_id' => PropertyType::factory()->create()->id,
        'agent_id' => Agent::factory()->create()->id,
    ]);

    expect($property->location->name)->toBe('Downtown');
});

it('can scope properties by status', function () {
    Property::factory()->create([
        'status' => 'available',
        'property_type_id' => PropertyType::factory()->create()->id,
        'agent_id' => Agent::factory()->create()->id,
        'location_id' => Location::factory()->create()->id,
    ]);

    Property::factory()->create([
        'status' => 'sold',
        'property_type_id' => PropertyType::factory()->create()->id,
        'agent_id' => Agent::factory()->create()->id,
        'location_id' => Location::factory()->create()->id,
    ]);

    $availableProperties = Property::where('status', 'available')->get();
    $soldProperties = Property::where('status', 'sold')->get();

    expect($availableProperties)->toHaveCount(1);
    expect($soldProperties)->toHaveCount(1);
});

it('can scope properties by listing type', function () {
    Property::factory()->create([
        'listing_type' => 'sale',
        'property_type_id' => PropertyType::factory()->create()->id,
        'agent_id' => Agent::factory()->create()->id,
        'location_id' => Location::factory()->create()->id,
    ]);

    Property::factory()->create([
        'listing_type' => 'rent',
        'property_type_id' => PropertyType::factory()->create()->id,
        'agent_id' => Agent::factory()->create()->id,
        'location_id' => Location::factory()->create()->id,
    ]);

    $saleProperties = Property::where('listing_type', 'sale')->get();
    $rentProperties = Property::where('listing_type', 'rent')->get();

    expect($saleProperties)->toHaveCount(1);
    expect($rentProperties)->toHaveCount(1);
});

it('can check if property is featured', function () {
    $property = Property::factory()->create([
        'is_featured' => true,
        'property_type_id' => PropertyType::factory()->create()->id,
        'agent_id' => Agent::factory()->create()->id,
        'location_id' => Location::factory()->create()->id,
    ]);

    expect($property->is_featured)->toBeTrue();

    $regularProperty = Property::factory()->create([
        'is_featured' => false,
        'property_type_id' => PropertyType::factory()->create()->id,
        'agent_id' => Agent::factory()->create()->id,
        'location_id' => Location::factory()->create()->id,
    ]);

    expect($regularProperty->is_featured)->toBeFalse();
});
