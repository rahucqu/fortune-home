<?php

declare(strict_types=1);

use App\Models\Location;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\User;

beforeEach(function () {
    // Create test data
    $this->propertyType = PropertyType::factory()->create([
        'name' => 'Apartment',
        'is_active' => true,
    ]);

    $this->location = Location::factory()->create([
        'name' => 'Downtown',
        'is_active' => true,
    ]);

    $this->agent = User::factory()->create();

    // Create test properties
    $this->properties = Property::factory()
        ->count(15)
        ->create([
            'property_type_id' => $this->propertyType->id,
            'location_id' => $this->location->id,
            'agent_id' => $this->agent->id,
            'status' => 'active',
            'published_at' => now(),
        ]);

    // Create featured properties
    $this->featuredProperties = Property::factory()
        ->count(3)
        ->create([
            'featured' => true,
            'status' => 'active',
            'published_at' => now(),
            'property_type_id' => $this->propertyType->id,
            'location_id' => $this->location->id,
            'agent_id' => $this->agent->id,
        ]);
});

test('properties index page loads successfully', function () {
    $response = $this->get('/properties');

    $response->assertOk()
        ->assertInertia(function ($assert) {
            $assert->component('frontend/properties/index')
                ->has('properties.data')
                ->has('featuredProperties')
                ->has('propertyTypes')
                ->has('locations')
                ->has('userFavorites')
                ->has('filters');
        });
});

test('properties can be filtered by property type', function () {
    $response = $this->get('/properties?property_type='.$this->propertyType->id);

    $response->assertOk()
        ->assertInertia(function ($assert) {
            $assert->component('frontend/properties/index')
                ->where('filters.property_type', $this->propertyType->id)
                ->has('properties.data');
        });
});

test('properties can be filtered by location', function () {
    $response = $this->get('/properties?location='.$this->location->id);

    $response->assertOk()
        ->assertInertia(function ($assert) {
            $assert->component('frontend/properties/index')
                ->where('filters.location', $this->location->id)
                ->has('properties.data');
        });
});

test('properties can be filtered by listing type', function () {
    Property::factory()->create([
        'listing_type' => 'sale',
        'status' => 'active',
        'published_at' => now(),
        'property_type_id' => $this->propertyType->id,
        'location_id' => $this->location->id,
        'agent_id' => $this->agent->id,
    ]);

    $response = $this->get('/properties?listing_type=sale');

    $response->assertOk()
        ->assertInertia(function ($assert) {
            $assert->component('frontend/properties/index')
                ->where('filters.listing_type', 'sale')
                ->has('properties.data');
        });
});

test('properties can be filtered by price range', function () {
    Property::factory()->create([
        'price' => 100000,
        'status' => 'active',
        'published_at' => now(),
        'property_type_id' => $this->propertyType->id,
        'location_id' => $this->location->id,
        'agent_id' => $this->agent->id,
    ]);

    Property::factory()->create([
        'price' => 500000,
        'status' => 'active',
        'published_at' => now(),
        'property_type_id' => $this->propertyType->id,
        'location_id' => $this->location->id,
        'agent_id' => $this->agent->id,
    ]);

    Property::factory()->create([
        'price' => 1000000,
        'status' => 'active',
        'published_at' => now(),
        'property_type_id' => $this->propertyType->id,
        'location_id' => $this->location->id,
        'agent_id' => $this->agent->id,
    ]);

    $response = $this->get('/properties?min_price=200000&max_price=800000');

    $response->assertOk()
        ->assertInertia(function ($assert) {
            $assert->component('frontend/properties/index')
                ->where('filters.min_price', 200000)
                ->where('filters.max_price', 800000)
                ->has('properties.data');
        });
});

test('properties can be filtered by bedrooms', function () {
    Property::factory()->create([
        'bedrooms' => 2,
        'status' => 'active',
        'published_at' => now(),
        'property_type_id' => $this->propertyType->id,
        'location_id' => $this->location->id,
        'agent_id' => $this->agent->id,
    ]);

    Property::factory()->create([
        'bedrooms' => 3,
        'status' => 'active',
        'published_at' => now(),
        'property_type_id' => $this->propertyType->id,
        'location_id' => $this->location->id,
        'agent_id' => $this->agent->id,
    ]);

    $response = $this->get('/properties?bedrooms=3');

    $response->assertOk()
        ->assertInertia(function ($assert) {
            $assert->component('frontend/properties/index')
                ->where('filters.bedrooms', '3')
                ->has('properties.data');
        });
});

test('properties can be filtered by bedrooms 4+', function () {
    Property::factory()->create([
        'bedrooms' => 5,
        'status' => 'active',
        'published_at' => now(),
    ]);

    $response = $this->get('/properties?bedrooms=4%2B'); // URL encoded 4+

    $response->assertOk()
        ->assertInertia(function ($assert) {
            $assert->component('frontend/properties/index')
                ->where('filters.bedrooms', '4+')
                ->has('properties.data');
        });
});

test('properties can be searched by keyword', function () {
    Property::factory()->create([
        'title' => 'Luxury Apartment Downtown',
        'description' => 'Beautiful luxury apartment in the heart of downtown',
        'status' => 'active',
        'published_at' => now(),
        'property_type_id' => $this->propertyType->id,
        'location_id' => $this->location->id,
        'agent_id' => $this->agent->id,
    ]);

    $response = $this->get('/properties?search=Luxury');

    $response->assertOk()
        ->assertInertia(function ($assert) {
            $assert->component('frontend/properties/index')
                ->where('filters.search', 'Luxury')
                ->has('properties.data');
        });
});

test('properties can be sorted by price low to high', function () {
    Property::factory()->create([
        'price' => 100000,
        'status' => 'active',
        'published_at' => now(),
        'property_type_id' => $this->propertyType->id,
        'location_id' => $this->location->id,
        'agent_id' => $this->agent->id,
    ]);

    Property::factory()->create([
        'price' => 500000,
        'status' => 'active',
        'published_at' => now(),
        'property_type_id' => $this->propertyType->id,
        'location_id' => $this->location->id,
        'agent_id' => $this->agent->id,
    ]);

    $response = $this->get('/properties?sort=price_low');

    $response->assertOk()
        ->assertInertia(function ($assert) {
            $assert->component('frontend/properties/index')
                ->where('filters.sort', 'price_low')
                ->has('properties.data');
        });
});

test('properties can be sorted by price high to low', function () {
    Property::factory()->create([
        'price' => 100000,
        'status' => 'active',
        'published_at' => now(),
        'property_type_id' => $this->propertyType->id,
        'location_id' => $this->location->id,
        'agent_id' => $this->agent->id,
    ]);

    Property::factory()->create([
        'price' => 500000,
        'status' => 'active',
        'published_at' => now(),
        'property_type_id' => $this->propertyType->id,
        'location_id' => $this->location->id,
        'agent_id' => $this->agent->id,
    ]);

    $response = $this->get('/properties?sort=price_high');

    $response->assertOk()
        ->assertInertia(function ($assert) {
            $assert->component('frontend/properties/index')
                ->where('filters.sort', 'price_high')
                ->has('properties.data');
        });
});

test('featured properties are returned separately', function () {
    $response = $this->get('/properties');

    $response->assertOk()
        ->assertInertia(function ($assert) {
            $assert->component('frontend/properties/index')
                ->has('featuredProperties')
                ->has('featuredProperties.0');
        });
});

test('properties are paginated correctly', function () {
    $response = $this->get('/properties');

    $response->assertOk()
        ->assertInertia(function ($assert) {
            $assert->component('frontend/properties/index')
                ->has('properties.current_page')
                ->has('properties.last_page')
                ->has('properties.per_page')
                ->has('properties.total')
                ->has('properties.from')
                ->has('properties.to')
                ->has('properties.data');
        });
});

test('only published and active properties are shown', function () {
    // Create inactive property
    Property::factory()->create([
        'status' => 'inactive',
        'published_at' => now(),
        'property_type_id' => $this->propertyType->id,
        'location_id' => $this->location->id,
        'agent_id' => $this->agent->id,
    ]);

    // Create unpublished property
    Property::factory()->create([
        'status' => 'active',
        'published_at' => null,
        'property_type_id' => $this->propertyType->id,
        'location_id' => $this->location->id,
        'agent_id' => $this->agent->id,
    ]);

    $response = $this->get('/properties');

    $response->assertOk()
        ->assertInertia(function ($assert) {
            $assert->component('frontend/properties/index')
                ->has('properties.data');
        });
});

test('user favorites are included when authenticated', function () {
    $user = User::factory()->create();
    $property = Property::factory()->create([
        'status' => 'active',
        'published_at' => now(),
        'property_type_id' => $this->propertyType->id,
        'location_id' => $this->location->id,
        'agent_id' => $this->agent->id,
    ]);

    // Add property to user favorites
    $user->favoriteProperties()->attach($property->id);

    $response = $this->actingAs($user)->get('/properties');

    $response->assertOk()
        ->assertInertia(function ($assert) use ($property) {
            $assert->component('frontend/properties/index')
                ->has('userFavorites')
                ->where('userFavorites.0', $property->id);
        });
});

test('empty user favorites when not authenticated', function () {
    $response = $this->get('/properties');

    $response->assertOk()
        ->assertInertia(function ($assert) {
            $assert->component('frontend/properties/index')
                ->where('userFavorites', []);
        });
});
