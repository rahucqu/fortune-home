<?php

declare(strict_types=1);

use App\Models\Amenity;
use App\Models\Feature;
use App\Models\Property;
use App\Models\PropertyFloorPlan;
use App\Models\PropertyImage;
use App\Models\PropertyType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->propertyType = PropertyType::factory()->create();
    $this->agent = User::factory()->agent()->create();

    $this->property = Property::factory()->create([
        'title' => 'Beautiful Family Home',
        'slug' => 'beautiful-family-home',
        'property_type_id' => $this->propertyType->id,
        'agent_id' => $this->agent->id,
        'status' => 'active',
        'published_at' => now(),
        'bedrooms' => 3,
        'bathrooms' => 2,
        'square_feet' => 1500,
        'year_built' => 2020,
        'garage_spaces' => 2,
        'price' => 450000,
        'featured' => true,
        'listing_type' => 'sale',
    ]);
});

it('displays property details page with correct data', function () {
    $response = $this->get("/property/{$this->property->slug}");

    $response->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('frontend/properties/property-details')
            ->where('property.title', 'Beautiful Family Home')
            ->where('property.slug', 'beautiful-family-home')
            ->where('property.bedrooms', 3)
            ->where('property.bathrooms', '2.0') // Laravel casts this as decimal
            ->where('property.square_feet', 1500)
            ->where('property.year_built', 2020)
            ->where('property.garage_spaces', 2)
            ->where('property.price', '450000.00')
            ->where('property.featured', true)
            ->where('property.listing_type', 'sale')
            ->has('similarProperties')
            ->where('isFavorited', false)
        );
});

it('displays property with images correctly', function () {
    // Create property images
    $primaryImage = PropertyImage::factory()->create([
        'property_id' => $this->property->id,
        'is_primary' => true,
        'sort_order' => 1,
    ]);

    $secondaryImages = PropertyImage::factory(3)->create([
        'property_id' => $this->property->id,
        'is_primary' => false,
    ]);

    $response = $this->get("/property/{$this->property->slug}");

    $response->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->has('property.images', 4)
            ->has('property.primary_image')
            ->where('property.primary_image.id', $primaryImage->id)
            ->where('property.primary_image.is_primary', true)
        );
});

it('displays property amenities and features', function () {
    // Create amenities
    $amenities = Amenity::factory(3)->create();
    $this->property->amenities()->attach($amenities->pluck('id'));

    // Create features
    $features = Feature::factory(2)->create();
    $this->property->features()->attach($features->first()->id, ['value' => 'Hardwood']);
    $this->property->features()->attach($features->last()->id, ['value' => 'Modern']);

    $response = $this->get("/property/{$this->property->slug}");

    $response->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->has('property.amenities', 3)
            ->has('property.features', 2)
        );
});

it('displays property floor plans', function () {
    // Create floor plans
    PropertyFloorPlan::factory(2)->create([
        'property_id' => $this->property->id,
    ]);

    $response = $this->get("/property/{$this->property->slug}");

    $response->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->has('property.floor_plans', 2)
        );
});

it('shows similar properties based on property type and location', function () {
    // Create similar properties
    Property::factory(3)->create([
        'property_type_id' => $this->property->property_type_id,
        'city' => $this->property->city,
        'state' => $this->property->state,
        'status' => 'active',
        'published_at' => now(),
    ]);

    // Create different property type (should not appear in similar)
    $differentType = PropertyType::factory()->create();
    Property::factory()->create([
        'property_type_id' => $differentType->id,
        'status' => 'active',
        'published_at' => now(),
    ]);

    $response = $this->get("/property/{$this->property->slug}");

    $response->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->has('similarProperties', 3)
        );
});

it('shows favorite status correctly for authenticated users', function () {
    $user = User::factory()->customer()->create();

    // Add property to user's favorites
    $user->favoriteProperties()->attach($this->property->id);

    $response = $this->actingAs($user)
        ->get("/property/{$this->property->slug}");

    $response->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->where('isFavorited', true)
        );
});

it('allows authenticated users to toggle favorites', function () {
    $user = User::factory()->customer()->create();

    // Initially not favorited
    expect($user->favoriteProperties()->where('property_id', $this->property->id)->exists())->toBeFalse();

    // Toggle favorite
    $response = $this->actingAs($user)
        ->post("/properties/{$this->property->slug}/favorite");

    $response->assertRedirect();

    // Verify it's now favorited
    expect($user->fresh()->favoriteProperties()->where('property_id', $this->property->id)->exists())->toBeTrue();

    // Toggle again to remove
    $response = $this->actingAs($user)
        ->post("/properties/{$this->property->slug}/favorite");

    $response->assertRedirect();

    // Verify it's no longer favorited
    expect($user->fresh()->favoriteProperties()->where('property_id', $this->property->id)->exists())->toBeFalse();
});

it('increments view count when property is viewed', function () {
    $initialViews = $this->property->views_count;

    $this->get("/property/{$this->property->slug}");

    expect($this->property->fresh()->views_count)->toBe($initialViews + 1);
});

it('returns 404 for non-existent property', function () {
    $response = $this->get('/property/non-existent-property');

    $response->assertStatus(404);
});

it('returns 404 for inactive properties', function () {
    $inactiveProperty = Property::factory()->create([
        'status' => 'inactive',
        'published_at' => now(),
    ]);

    $response = $this->get("/property/{$inactiveProperty->slug}");

    $response->assertStatus(404);
});

it('returns 404 for unpublished properties', function () {
    $unpublishedProperty = Property::factory()->create([
        'status' => 'active',
        'published_at' => null,
    ]);

    $response = $this->get("/property/{$unpublishedProperty->slug}");

    $response->assertStatus(404);
});
