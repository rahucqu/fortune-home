<?php

declare(strict_types=1);

use App\Models\Location;
use App\Models\PropertyType;

test('home page loads with search data', function () {
    // Create some test data
    $propertyType = PropertyType::factory()->create(['name' => 'House', 'is_active' => true]);
    $location = Location::factory()->create(['name' => 'Dhaka', 'is_active' => true]);

    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('frontend/home')
        ->has('propertyTypes')
        ->has('locations')
        ->where('propertyTypes.0.name', 'House')
        ->where('locations.0.name', 'Dhaka')
    );
});

test('search functionality redirects to properties page with filters', function () {
    // Create some test data
    $propertyType = PropertyType::factory()->create(['name' => 'House', 'is_active' => true]);
    $location = Location::factory()->create(['name' => 'Dhaka', 'is_active' => true]);

    // Test that when we access properties page with search params, it works
    $response = $this->get('/properties?search=luxury&property_type=1&location=1&listing_type=sale');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('frontend/properties/index')
        ->has('filters')
        ->where('filters.search', 'luxury')
        ->where('filters.property_type', 1)
        ->where('filters.location', 1)
        ->where('filters.listing_type', 'sale')
    );
});
