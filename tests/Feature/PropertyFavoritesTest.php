<?php

declare(strict_types=1);

use App\Models\Location;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create required models first
    $this->propertyType = PropertyType::factory()->create();
    $this->location = Location::factory()->create();
    $this->agent = User::factory()->create();

    $this->user = User::factory()->create();
    $this->property = Property::factory()->create([
        'status' => 'active',
        'published_at' => now(),
        'property_type_id' => $this->propertyType->id,
        'location_id' => $this->location->id,
        'agent_id' => $this->agent->id,
    ]);
});

test('user can favorite a property model', function () {
    // Test the model relationship directly
    $this->user->favoriteProperties()->attach($this->property->id);

    // Check that the property is now favorited
    expect($this->user->favoriteProperties()->where('property_id', $this->property->id)->exists())->toBeTrue();
    expect($this->user->favoriteProperties()->count())->toBe(1);
});

test('user can unfavorite a property model', function () {
    // First, favorite the property
    $this->user->favoriteProperties()->attach($this->property->id);

    // Then unfavorite it
    $this->user->favoriteProperties()->detach($this->property->id);

    // Check that the property is no longer favorited
    expect($this->user->favoriteProperties()->where('property_id', $this->property->id)->exists())->toBeFalse();
    expect($this->user->favoriteProperties()->count())->toBe(0);
});

test('user can favorite multiple properties via model', function () {
    $property1 = Property::factory()->create([
        'status' => 'active',
        'published_at' => now(),
        'property_type_id' => $this->propertyType->id,
        'location_id' => $this->location->id,
        'agent_id' => $this->agent->id,
    ]);

    $property2 = Property::factory()->create([
        'status' => 'active',
        'published_at' => now(),
        'property_type_id' => $this->propertyType->id,
        'location_id' => $this->location->id,
        'agent_id' => $this->agent->id,
    ]);

    // Favorite both properties
    $this->user->favoriteProperties()->attach([$property1->id, $property2->id]);

    // Check that both properties are favorited
    expect($this->user->favoriteProperties()->count())->toBe(2);
    expect($this->user->favoriteProperties()->where('property_id', $property1->id)->exists())->toBeTrue();
    expect($this->user->favoriteProperties()->where('property_id', $property2->id)->exists())->toBeTrue();
});

test('property favorites count can be updated', function () {
    // Get initial count
    $initialCount = $this->property->favorites_count;

    // Add some favorites
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $user1->favoriteProperties()->attach($this->property->id);
    $user2->favoriteProperties()->attach($this->property->id);

    // Manually increment since we're not using the controller
    $this->property->increment('favorites_count', 2);

    $this->property->refresh();
    expect($this->property->favorites_count)->toBe($initialCount + 2);
});

test('favorite relationship methods work correctly', function () {
    // Test that we can check if a property is favorited
    expect($this->user->favoriteProperties()->where('property_id', $this->property->id)->exists())->toBeFalse();

    // Add favorite
    $this->user->favoriteProperties()->attach($this->property->id);

    // Test that it's now favorited
    expect($this->user->favoriteProperties()->where('property_id', $this->property->id)->exists())->toBeTrue();

    // Test that we can get the favorited property
    $favoritedProperty = $this->user->favoriteProperties()->first();
    expect($favoritedProperty->id)->toBe($this->property->id);
});
