<?php

declare(strict_types=1);

use App\Enums\PropertyStatus;
use App\Models\Amenity;
use App\Models\Location;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create roles and permissions
    $this->adminRole = Role::create(['name' => 'admin', 'display_name' => 'Administrator', 'guard_name' => 'web']);
    $this->userRole = Role::create(['name' => 'user', 'display_name' => 'User', 'guard_name' => 'web']);
    $this->agentRole = Role::create(['name' => 'agent', 'display_name' => 'Agent', 'guard_name' => 'web']);

    // Create permissions using new permission structure
    $permissions = [
        'properties.view-all',     // Admin can view all properties
        'properties.view-own',     // Agent can view own properties
        'properties.view-published', // Users can view published properties
        'properties.create',
        'properties.update',
        'properties.delete',
        'properties.publish',
        'properties.feature',
    ];

    foreach ($permissions as $permission) {
        Permission::create(['name' => $permission, 'guard_name' => 'web']);
    }

    // Give admin permissions to view all properties
    $this->adminRole->givePermissionTo([
        'properties.view-all',
        'properties.create',
        'properties.update',
        'properties.delete',
        'properties.publish',
        'properties.feature',
    ]);

    // Give agent permissions to view own properties
    $this->agentRole->givePermissionTo([
        'properties.view-own',
        'properties.create',
    ]);

    // Create users
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    $this->user = User::factory()->create();
    $this->user->assignRole('user');

    $this->agent = User::factory()->create();
    $this->agent->assignRole('agent');

    // Create required related models
    $this->propertyType = PropertyType::factory()->create();
    $this->location = Location::factory()->create();
    $this->amenity = Amenity::factory()->create();
});

describe('Property Index', function () {
    test('admin can view properties index', function () {
        Property::factory(3)->create([
            'property_type_id' => $this->propertyType->id,
            'agent_id' => $this->agent->id,
            'location_id' => $this->location->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.properties.index'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/properties/index')
                ->has('properties.data', 3)
                ->has('filters')
                ->has('listingTypes')
                ->has('statuses')
                ->has('propertyTypes')
                ->has('agents')
                ->has('locations')
            );
    });

    test('user without permission cannot view properties index', function () {
        $response = $this->actingAs($this->user)
            ->get(route('admin.properties.index'));

        $response->assertForbidden();
    });

    test('can filter properties by search', function () {
        Property::factory()->create([
            'title' => 'Luxury Penthouse',
            'property_type_id' => $this->propertyType->id,
            'agent_id' => $this->agent->id,
            'location_id' => $this->location->id,
        ]);
        Property::factory()->create([
            'title' => 'Cozy Apartment',
            'property_type_id' => $this->propertyType->id,
            'agent_id' => $this->agent->id,
            'location_id' => $this->location->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.properties.index', ['search' => 'Luxury']));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/properties/index')
                ->has('properties.data', 1)
                ->where('properties.data.0.title', 'Luxury Penthouse')
            );
    });

    test('can filter properties by listing type', function () {
        Property::factory()->create([
            'listing_type' => 'sale',
            'property_type_id' => $this->propertyType->id,
            'agent_id' => $this->agent->id,
            'location_id' => $this->location->id,
        ]);
        Property::factory()->create([
            'listing_type' => 'rent',
            'property_type_id' => $this->propertyType->id,
            'agent_id' => $this->agent->id,
            'location_id' => $this->location->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.properties.index', ['listing_type' => 'sale']));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/properties/index')
                ->has('properties.data', 1)
                ->where('properties.data.0.listing_type', 'sale')
            );
    });

    test('can filter properties by status', function () {
        Property::factory()->create([
            'status' => 'active',
            'property_type_id' => $this->propertyType->id,
            'agent_id' => $this->agent->id,
            'location_id' => $this->location->id,
        ]);
        Property::factory()->create([
            'status' => 'sold',
            'property_type_id' => $this->propertyType->id,
            'agent_id' => $this->agent->id,
            'location_id' => $this->location->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.properties.index', ['status' => 'active']));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/properties/index')
                ->has('properties.data', 1)
                ->where('properties.data.0.status', 'active')
            );
    });
});

describe('Property CRUD', function () {
    test('admin can create property', function () {
        $propertyData = [
            'title' => 'Beautiful Home',
            'description' => 'A beautiful home in a great location',
            'property_type_id' => $this->propertyType->id,
            'listing_type' => 'sale',
            'status' => 'active',
            'price' => 500000.00,
            'currency' => 'USD',
            'bedrooms' => 3,
            'bathrooms' => 2.5,
            'square_feet' => 2000,
            'address' => '123 Main St',
            'location_id' => $this->location->id,
            'city' => 'Test City',
            'state' => 'Test State',
            'zip_code' => '12345',
            'country' => 'USA',
            'agent_id' => $this->agent->id,
            'featured' => true,
            'amenities' => [$this->amenity->id],
        ];

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(VerifyCsrfToken::class)
            ->post(route('admin.properties.store'), $propertyData);

        $response->assertRedirect(route('admin.properties.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('properties', [
            'title' => 'Beautiful Home',
            'listing_type' => 'sale',
            'status' => 'active',
            'price' => 500000.00,
            'agent_id' => $this->agent->id,
        ]);

        $property = Property::where('title', 'Beautiful Home')->first();
        expect($property->amenities)->toHaveCount(1);
        expect($property->amenities->first()->id)->toBe($this->amenity->id);
    });

    test('admin can update property', function () {
        $property = Property::factory()->create([
            'title' => 'Old Title',
            'property_type_id' => $this->propertyType->id,
            'agent_id' => $this->agent->id,
            'location_id' => $this->location->id,
        ]);

        $updateData = [
            'title' => 'New Title',
            'description' => 'Updated description',
            'property_type_id' => $this->propertyType->id,
            'listing_type' => 'rent',
            'status' => 'active',
            'price' => 2500.00,
            'currency' => 'USD',
            'address' => '456 Oak St',
            'location_id' => $this->location->id,
            'city' => 'New City',
            'state' => 'New State',
            'zip_code' => '54321',
            'country' => 'USA',
            'agent_id' => $this->agent->id,
            'featured' => false,
        ];

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(VerifyCsrfToken::class)
            ->put(route('admin.properties.update', $property), $updateData);

        $response->assertRedirect(route('admin.properties.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('properties', [
            'id' => $property->id,
            'title' => 'New Title',
            'listing_type' => 'rent',
            'price' => 2500.00,
        ]);
    });

    test('admin can view property details', function () {
        $property = Property::factory()->create([
            'property_type_id' => $this->propertyType->id,
            'agent_id' => $this->agent->id,
            'location_id' => $this->location->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.properties.show', $property));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('admin/properties/show')
                ->has('property')
                ->where('property.id', $property->id)
            );
    });

    test('admin can delete property', function () {
        $property = Property::factory()->create([
            'property_type_id' => $this->propertyType->id,
            'agent_id' => $this->agent->id,
            'location_id' => $this->location->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(VerifyCsrfToken::class)
            ->delete(route('admin.properties.destroy', $property));

        $response->assertRedirect(route('admin.properties.index'))
            ->assertSessionHas('success');

        $this->assertSoftDeleted('properties', ['id' => $property->id]);
    });

    test('cannot delete property with inquiries', function () {
        $property = Property::factory()->create([
            'property_type_id' => $this->propertyType->id,
            'agent_id' => $this->agent->id,
            'location_id' => $this->location->id,
        ]);

        // Create an inquiry for the property
        $property->inquiries()->create([
            'user_id' => $this->user->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '123-456-7890',
            'message' => 'Interested in this property',
            'inquiry_type' => 'general',
            'preferred_contact_method' => 'email',
        ]);

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(VerifyCsrfToken::class)
            ->delete(route('admin.properties.destroy', $property));

        $response->assertRedirect(route('admin.properties.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('properties', ['id' => $property->id]);
    });

    test('user without permission cannot create property', function () {
        $propertyData = [
            'title' => 'Test Property',
            'description' => 'Test description',
            'property_type_id' => $this->propertyType->id,
            'listing_type' => 'sale',
            'status' => 'active',
            'price' => 100000.00,
            'currency' => 'USD',
            'address' => '123 Test St',
            'location_id' => $this->location->id,
            'city' => 'Test City',
            'state' => 'Test State',
            'zip_code' => '12345',
            'country' => 'USA',
            'agent_id' => $this->agent->id,
        ];

        $response = $this->actingAs($this->user)
            ->withoutMiddleware(VerifyCsrfToken::class)
            ->post(route('admin.properties.store'), $propertyData);

        $response->assertForbidden();
    });
});

describe('Property Validation', function () {
    test('title is required', function () {
        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(VerifyCsrfToken::class)
            ->post(route('admin.properties.store'), [
                'description' => 'Test description',
                'property_type_id' => $this->propertyType->id,
                'listing_type' => 'sale',
                'status' => 'active',
                'price' => 100000.00,
                'currency' => 'USD',
                'address' => '123 Test St',
                'city' => 'Test City',
                'state' => 'Test State',
                'zip_code' => '12345',
                'country' => 'USA',
                'agent_id' => $this->agent->id,
            ]);

        $response->assertSessionHasErrors(['title']);
    });

    test('property type is required', function () {
        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(VerifyCsrfToken::class)
            ->post(route('admin.properties.store'), [
                'title' => 'Test Property',
                'description' => 'Test description',
                'listing_type' => 'sale',
                'status' => 'active',
                'price' => 100000.00,
                'currency' => 'USD',
                'address' => '123 Test St',
                'city' => 'Test City',
                'state' => 'Test State',
                'zip_code' => '12345',
                'country' => 'USA',
                'agent_id' => $this->agent->id,
            ]);

        $response->assertSessionHasErrors(['property_type_id']);
    });

    test('price is required', function () {
        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(VerifyCsrfToken::class)
            ->post(route('admin.properties.store'), [
                'title' => 'Test Property',
                'description' => 'Test description',
                'property_type_id' => $this->propertyType->id,
                'listing_type' => 'sale',
                'status' => 'active',
                'currency' => 'USD',
                'address' => '123 Test St',
                'city' => 'Test City',
                'state' => 'Test State',
                'zip_code' => '12345',
                'country' => 'USA',
                'agent_id' => $this->agent->id,
            ]);

        $response->assertSessionHasErrors(['price']);
    });

    test('listing type must be valid enum value', function () {
        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(VerifyCsrfToken::class)
            ->post(route('admin.properties.store'), [
                'title' => 'Test Property',
                'description' => 'Test description',
                'property_type_id' => $this->propertyType->id,
                'listing_type' => 'invalid_type',
                'status' => 'active',
                'price' => 100000.00,
                'currency' => 'USD',
                'address' => '123 Test St',
                'city' => 'Test City',
                'state' => 'Test State',
                'zip_code' => '12345',
                'country' => 'USA',
                'agent_id' => $this->agent->id,
            ]);

        $response->assertSessionHasErrors(['listing_type']);
    });

    test('status must be valid enum value', function () {
        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(VerifyCsrfToken::class)
            ->post(route('admin.properties.store'), [
                'title' => 'Test Property',
                'description' => 'Test description',
                'property_type_id' => $this->propertyType->id,
                'listing_type' => 'sale',
                'status' => 'invalid_status',
                'price' => 100000.00,
                'currency' => 'USD',
                'address' => '123 Test St',
                'city' => 'Test City',
                'state' => 'Test State',
                'zip_code' => '12345',
                'country' => 'USA',
                'agent_id' => $this->agent->id,
            ]);

        $response->assertSessionHasErrors(['status']);
    });

    test('agent must exist', function () {
        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(VerifyCsrfToken::class)
            ->post(route('admin.properties.store'), [
                'title' => 'Test Property',
                'description' => 'Test description',
                'property_type_id' => $this->propertyType->id,
                'listing_type' => 'sale',
                'status' => 'active',
                'price' => 100000.00,
                'currency' => 'USD',
                'address' => '123 Test St',
                'city' => 'Test City',
                'state' => 'Test State',
                'zip_code' => '12345',
                'country' => 'USA',
                'agent_id' => 99999,
            ]);

        $response->assertSessionHasErrors(['agent_id']);
    });
});

describe('Property Model', function () {
    test('automatically generates slug from title', function () {
        $property = Property::factory()->make([
            'title' => 'Beautiful Luxury Home',
            'property_type_id' => $this->propertyType->id,
            'agent_id' => $this->agent->id,
            'location_id' => $this->location->id,
        ]);
        $property->slug = ''; // Clear slug to test auto generation
        $property->save();

        expect($property->slug)->toBe('beautiful-luxury-home');
    });

    test('generates unique slugs', function () {
        // Create first property with specific slug
        Property::factory()->create([
            'title' => 'Test Property',
            'slug' => 'test-property',
            'property_type_id' => $this->propertyType->id,
            'agent_id' => $this->agent->id,
            'location_id' => $this->location->id,
        ]);

        // Create second property with same title but no slug
        $property2 = Property::factory()->make([
            'title' => 'Test Property',
            'property_type_id' => $this->propertyType->id,
            'agent_id' => $this->agent->id,
            'location_id' => $this->location->id,
        ]);
        $property2->slug = ''; // Clear slug to force generation
        $property2->save();

        // Should generate unique slug since 'test-property' already exists
        expect($property2->slug)->toMatch('/test-property-\d+/');
    });

    test('has formatted price accessor', function () {
        $property = Property::factory()->create([
            'price' => 250000.00,
            'property_type_id' => $this->propertyType->id,
            'agent_id' => $this->agent->id,
            'location_id' => $this->location->id,
        ]);

        expect($property->formatted_price)->toBe('$250,000');
    });

    test('has full address accessor', function () {
        $property = Property::factory()->create([
            'address' => '123 Main St',
            'city' => 'Test City',
            'state' => 'Test State',
            'zip_code' => '12345',
            'property_type_id' => $this->propertyType->id,
            'agent_id' => $this->agent->id,
            'location_id' => $this->location->id,
        ]);

        expect($property->full_address)->toBe('123 Main St, Test City, Test State 12345');
    });

    test('can check if property is available', function () {
        $activeProperty = Property::factory()->create([
            'status' => PropertyStatus::ACTIVE,
            'published_at' => now()->subDay(),
            'property_type_id' => $this->propertyType->id,
            'agent_id' => $this->agent->id,
            'location_id' => $this->location->id,
        ]);

        $inactiveProperty = Property::factory()->create([
            'status' => PropertyStatus::INACTIVE,
            'published_at' => now()->subDay(),
            'property_type_id' => $this->propertyType->id,
            'agent_id' => $this->agent->id,
            'location_id' => $this->location->id,
        ]);

        $unpublishedProperty = Property::factory()->create([
            'status' => PropertyStatus::ACTIVE,
            'published_at' => null,
            'property_type_id' => $this->propertyType->id,
            'agent_id' => $this->agent->id,
            'location_id' => $this->location->id,
        ]);

        expect($activeProperty->isAvailable())->toBeTrue();
        expect($inactiveProperty->isAvailable())->toBeFalse();
        expect($unpublishedProperty->isAvailable())->toBeFalse();
    });

    test('can scope by listing type', function () {
        Property::factory()->create([
            'listing_type' => 'sale',
            'property_type_id' => $this->propertyType->id,
            'agent_id' => $this->agent->id,
            'location_id' => $this->location->id,
        ]);
        Property::factory()->create([
            'listing_type' => 'rent',
            'property_type_id' => $this->propertyType->id,
            'agent_id' => $this->agent->id,
            'location_id' => $this->location->id,
        ]);

        $forSaleProperties = Property::forSale()->get();
        $forRentProperties = Property::forRent()->get();

        expect($forSaleProperties)->toHaveCount(1);
        expect($forRentProperties)->toHaveCount(1);
        expect($forSaleProperties->first()->listing_type->value)->toBe('sale');
        expect($forRentProperties->first()->listing_type->value)->toBe('rent');
    });

    test('belongs to property type', function () {
        $property = Property::factory()->create([
            'property_type_id' => $this->propertyType->id,
            'agent_id' => $this->agent->id,
            'location_id' => $this->location->id,
        ]);

        expect($property->propertyType)->toBeInstanceOf(PropertyType::class);
        expect($property->propertyType->id)->toBe($this->propertyType->id);
    });

    test('belongs to agent', function () {
        $property = Property::factory()->create([
            'property_type_id' => $this->propertyType->id,
            'agent_id' => $this->agent->id,
            'location_id' => $this->location->id,
        ]);

        expect($property->agent)->toBeInstanceOf(User::class);
        expect($property->agent->id)->toBe($this->agent->id);
    });

    test('belongs to location', function () {
        $property = Property::factory()->create([
            'property_type_id' => $this->propertyType->id,
            'agent_id' => $this->agent->id,
            'location_id' => $this->location->id,
        ]);

        expect($property->location)->toBeInstanceOf(Location::class);
        expect($property->location->id)->toBe($this->location->id);
    });

    test('can have amenities', function () {
        $property = Property::factory()->create([
            'property_type_id' => $this->propertyType->id,
            'agent_id' => $this->agent->id,
            'location_id' => $this->location->id,
        ]);

        $amenity2 = Amenity::factory()->create();
        $property->amenities()->sync([$this->amenity->id, $amenity2->id]);

        expect($property->amenities)->toHaveCount(2);
        expect($property->amenities->pluck('id')->toArray())->toContain($this->amenity->id, $amenity2->id);
    });
});
