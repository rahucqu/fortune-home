<?php

declare(strict_types=1);

use App\Models\Agent;
use App\Models\Amenity;
use App\Models\Location;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create roles
    $adminRole = Role::firstOrCreate(['name' => 'admin']);
    Role::firstOrCreate(['name' => 'agent']);
    Role::firstOrCreate(['name' => 'user']);

    // Create permissions
    $permissions = [
        'access admin panel',
        'view properties',
        'create properties',
        'edit properties',
        'delete properties',
        'manage property images',
    ];

    foreach ($permissions as $permissionName) {
        $permission = Permission::firstOrCreate(['name' => $permissionName]);
        $adminRole->givePermissionTo($permission);
    }

    // Create admin user
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    // Create regular user
    $this->user = User::factory()->create();
    $this->user->assignRole('user');

    // Create test data
    $this->propertyType = PropertyType::factory()->create();
    $this->agent = Agent::factory()->create();
    $this->location = Location::factory()->create();
    $this->amenities = Amenity::factory()->count(3)->create();
});

it('displays properties index page for admin', function () {
    $properties = Property::factory()->count(5)->create([
        'property_type_id' => $this->propertyType->id,
        'agent_id' => $this->agent->id,
        'location_id' => $this->location->id,
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.properties.index'))
        ->assertSuccessful();
});

it('prevents unauthorized access to properties index', function () {
    $this->actingAs($this->user)
        ->get(route('admin.properties.index'))
        ->assertForbidden();
});

it('can filter properties by status', function () {
    Property::factory()->create([
        'status' => 'available',
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

    $this->actingAs($this->admin)
        ->get(route('admin.properties.index', ['status' => 'available']))
        ->assertSuccessful();
});

it('can search properties by title', function () {
    Property::factory()->create([
        'title' => 'Beautiful Beach House',
        'property_type_id' => $this->propertyType->id,
        'agent_id' => $this->agent->id,
        'location_id' => $this->location->id,
    ]);

    Property::factory()->create([
        'title' => 'Modern City Apartment',
        'property_type_id' => $this->propertyType->id,
        'agent_id' => $this->agent->id,
        'location_id' => $this->location->id,
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.properties.index', ['search' => 'Beach']))
        ->assertSuccessful();
});

it('displays create property form', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.properties.create'))
        ->assertSuccessful();
});

it('can create a new property', function () {
    Storage::fake('public');

    $propertyData = [
        'title' => 'Test Property',
        'slug' => 'test-property',
        'description' => 'A beautiful test property',
        'price' => 500000,
        'bedrooms' => 3,
        'bathrooms' => 2,
        'area_sqft' => 1500,
        'address' => '123 Test Street',
        'status' => 'available',
        'listing_type' => 'sale',
        'is_featured' => true,
        'property_type_id' => $this->propertyType->id,
        'agent_id' => $this->agent->id,
        'location_id' => $this->location->id,
        'amenities' => $this->amenities->pluck('id')->toArray(),
        'images' => [
            UploadedFile::fake()->image('property1.jpg'),
            UploadedFile::fake()->image('property2.jpg'),
        ],
    ];

    $this->actingAs($this->admin)
        ->post(route('admin.properties.store'), $propertyData)
        ->assertRedirect(route('admin.properties.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('properties', [
        'title' => 'Test Property',
        'price' => 500000,
        'status' => 'available',
        'listing_type' => 'sale',
        'is_featured' => true,
    ]);

    $property = Property::where('title', 'Test Property')->first();
    expect($property->amenities)->toHaveCount(3);
});

it('validates required fields when creating property', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.properties.store'), [])
        ->assertSessionHasErrors([
            'title',
            'description',
            'price',
            'address',
            'status',
            'listing_type',
            'property_type_id',
            'agent_id',
            'location_id',
        ]);
});

it('validates price is numeric and positive', function () {
    $propertyData = [
        'title' => 'Test Property',
        'slug' => 'test-property',
        'description' => 'A beautiful test property',
        'price' => -100,
        'bedrooms' => 3,
        'bathrooms' => 2,
        'area_sqft' => 1500,
        'address' => '123 Test Street',
        'status' => 'available',
        'listing_type' => 'sale',
        'property_type_id' => $this->propertyType->id,
        'agent_id' => $this->agent->id,
        'location_id' => $this->location->id,
    ];

    $this->actingAs($this->admin)
        ->post(route('admin.properties.store'), $propertyData)
        ->assertSessionHasErrors(['price']);
});

it('displays property details', function () {
    $property = Property::factory()->create([
        'property_type_id' => $this->propertyType->id,
        'agent_id' => $this->agent->id,
        'location_id' => $this->location->id,
    ]);

    $property->amenities()->attach($this->amenities->pluck('id'));

    $this->actingAs($this->admin)
        ->get(route('admin.properties.show', $property))
        ->assertSuccessful();
});

it('displays edit property form', function () {
    $property = Property::factory()->create([
        'property_type_id' => $this->propertyType->id,
        'agent_id' => $this->agent->id,
        'location_id' => $this->location->id,
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.properties.edit', $property))
        ->assertSuccessful();
});

it('can update a property', function () {
    $property = Property::factory()->create([
        'property_type_id' => $this->propertyType->id,
        'agent_id' => $this->agent->id,
        'location_id' => $this->location->id,
    ]);

    $updateData = [
        'title' => 'Updated Property Title',
        'slug' => 'updated-property-title',
        'description' => $property->description,
        'price' => 600000,
        'bedrooms' => $property->bedrooms,
        'bathrooms' => $property->bathrooms,
        'area_sqft' => $property->area_sqft,
        'address' => $property->address,
        'status' => 'sold',
        'listing_type' => $property->listing_type,
        'is_featured' => false,
        'property_type_id' => $property->property_type_id,
        'agent_id' => $property->agent_id,
        'location_id' => $property->location_id,
    ];

    $this->actingAs($this->admin)
        ->put(route('admin.properties.update', $property), $updateData)
        ->assertRedirect(route('admin.properties.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('properties', [
        'id' => $property->id,
        'title' => 'Updated Property Title',
        'price' => 600000,
        'status' => 'sold',
        'is_featured' => false,
    ]);
});

it('can delete a property', function () {
    $property = Property::factory()->create([
        'property_type_id' => $this->propertyType->id,
        'agent_id' => $this->agent->id,
        'location_id' => $this->location->id,
    ]);

    $this->actingAs($this->admin)
        ->delete(route('admin.properties.destroy', $property))
        ->assertRedirect(route('admin.properties.index'))
        ->assertSessionHas('success');

    expect($property->fresh()->trashed())->toBeTrue();
});

it('soft deletes property instead of hard delete', function () {
    $property = Property::factory()->create([
        'property_type_id' => $this->propertyType->id,
        'agent_id' => $this->agent->id,
        'location_id' => $this->location->id,
    ]);

    $this->actingAs($this->admin)
        ->delete(route('admin.properties.destroy', $property));

    expect($property->fresh()->trashed())->toBeTrue();
});

it('can bulk delete properties', function () {
    $properties = Property::factory()->count(3)->create([
        'property_type_id' => $this->propertyType->id,
        'agent_id' => $this->agent->id,
        'location_id' => $this->location->id,
    ]);

    $this->actingAs($this->admin)
        ->delete(route('admin.properties.bulk-destroy'), [
            'ids' => $properties->pluck('id')->toArray(),
        ])
        ->assertRedirect(route('admin.properties.index'))
        ->assertSessionHas('success');

    foreach ($properties as $property) {
        expect($property->fresh()->trashed())->toBeTrue();
    }
});
