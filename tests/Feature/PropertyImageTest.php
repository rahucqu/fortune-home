<?php

declare(strict_types=1);

use App\Models\Location;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\PropertyType;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');

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

    $this->agent = User::factory()->create();
    $this->agent->assignRole('agent');

    // Create required related models
    $this->propertyType = PropertyType::factory()->create();
    $this->location = Location::factory()->create();
});

describe('Property Image Upload', function () {
    test('admin can create property with images', function () {
        $images = [
            UploadedFile::fake()->image('house1.jpg', 800, 600)->size(2048),
            UploadedFile::fake()->image('house2.jpg', 800, 600)->size(1536),
            UploadedFile::fake()->image('house3.jpg', 800, 600)->size(1024),
        ];

        $propertyData = [
            'title' => 'Beautiful Home with Images',
            'description' => 'A stunning property with multiple photos',
            'property_type_id' => $this->propertyType->id,
            'listing_type' => 'sale',
            'status' => 'active',
            'price' => 750000.00,
            'currency' => 'USD',
            'bedrooms' => 4,
            'bathrooms' => 3,
            'square_feet' => 2500,
            'address' => '456 Oak Street',
            'location_id' => $this->location->id,
            'city' => 'Test City',
            'state' => 'Test State',
            'zip_code' => '54321',
            'country' => 'USA',
            'agent_id' => $this->agent->id,
            'featured' => false,
            'images' => $images,
        ];

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(VerifyCsrfToken::class)
            ->post(route('admin.properties.store'), $propertyData);

        $response->assertRedirect(route('admin.properties.index'))
            ->assertSessionHas('success');

        $property = Property::where('title', 'Beautiful Home with Images')->first();
        expect($property)->not->toBeNull();
        expect($property->images)->toHaveCount(3);

        // Check that files were uploaded to storage
        $property->images->each(function ($image) {
            expect(Storage::disk('public')->exists($image->image_path))->toBeTrue();
            expect($image->file_size)->toBeGreaterThan(0);
            expect($image->file_type)->toContain('image/jpeg');
            expect($image->width)->toBeGreaterThan(0);
            expect($image->height)->toBeGreaterThan(0);
        });

        // Check that first image is marked as primary
        $primaryImage = $property->images->where('is_primary', true)->first();
        expect($primaryImage)->not->toBeNull();
        expect($property->images->where('is_primary', false))->toHaveCount(2);
    });

    test('admin can update property with new images', function () {
        $property = Property::factory()->create([
            'title' => 'Existing Property',
            'property_type_id' => $this->propertyType->id,
            'agent_id' => $this->agent->id,
            'location_id' => $this->location->id,
        ]);

        // Create existing images
        $existingImages = PropertyImage::factory(2)->create([
            'property_id' => $property->id,
        ]);

        $newImages = [
            UploadedFile::fake()->image('new1.jpg', 800, 600)->size(1024),
            UploadedFile::fake()->image('new2.jpg', 800, 600)->size(2048),
        ];

        $updateData = [
            'title' => 'Updated Property with New Images',
            'description' => 'Updated description',
            'property_type_id' => $this->propertyType->id,
            'listing_type' => 'sale',
            'status' => 'active',
            'price' => 850000.00,
            'currency' => 'USD',
            'bedrooms' => 3,
            'bathrooms' => 2,
            'square_feet' => 2000,
            'address' => '789 Pine Street',
            'location_id' => $this->location->id,
            'city' => 'Updated City',
            'state' => 'Updated State',
            'zip_code' => '98765',
            'country' => 'USA',
            'agent_id' => $this->agent->id,
            'featured' => true,
            'new_images' => $newImages,
        ];

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(VerifyCsrfToken::class)
            ->put(route('admin.properties.update', $property), $updateData);

        $response->assertRedirect(route('admin.properties.index'))
            ->assertSessionHas('success');

        $property->refresh();
        expect($property->title)->toBe('Updated Property with New Images');
        expect($property->images)->toHaveCount(4); // 2 existing + 2 new

        // Check that new files were uploaded
        $newlyAddedImages = $property->images->whereNotIn('id', $existingImages->pluck('id'));
        expect($newlyAddedImages)->toHaveCount(2);

        $newlyAddedImages->each(function ($image) {
            expect(Storage::disk('public')->exists($image->image_path))->toBeTrue();
        });
    });

    test('admin can delete existing images when updating property', function () {
        $property = Property::factory()->create([
            'property_type_id' => $this->propertyType->id,
            'agent_id' => $this->agent->id,
            'location_id' => $this->location->id,
        ]);

        // Create existing images with actual files
        $image1 = PropertyImage::factory()->create([
            'property_id' => $property->id,
            'image_path' => 'property-images/test1.jpg',
        ]);
        $image2 = PropertyImage::factory()->create([
            'property_id' => $property->id,
            'image_path' => 'property-images/test2.jpg',
        ]);

        // Put fake files in storage
        Storage::disk('public')->put($image1->image_path, 'fake content');
        Storage::disk('public')->put($image2->image_path, 'fake content');

        $updateData = [
            'title' => 'Property with Deleted Images',
            'description' => 'Updated description',
            'property_type_id' => $this->propertyType->id,
            'listing_type' => 'sale',
            'status' => 'active',
            'price' => 600000.00,
            'currency' => 'USD',
            'bedrooms' => 2,
            'bathrooms' => 1,
            'square_feet' => 1500,
            'address' => '321 Elm Street',
            'location_id' => $this->location->id,
            'city' => 'Test City',
            'state' => 'Test State',
            'zip_code' => '13579',
            'country' => 'USA',
            'agent_id' => $this->agent->id,
            'featured' => false,
            'delete_images' => [$image1->id], // Delete first image
        ];

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(VerifyCsrfToken::class)
            ->put(route('admin.properties.update', $property), $updateData);

        $response->assertRedirect(route('admin.properties.index'))
            ->assertSessionHas('success');

        $property->refresh();
        expect($property->images)->toHaveCount(1);
        expect($property->images->first()->id)->toBe($image2->id);

        // Check that deleted image file was removed from storage
        expect(Storage::disk('public')->exists($image1->image_path))->toBeFalse();
        expect(Storage::disk('public')->exists($image2->image_path))->toBeTrue();

        // Check that deleted image record was removed from database
        $this->assertDatabaseMissing('property_images', ['id' => $image1->id]);
        $this->assertDatabaseHas('property_images', ['id' => $image2->id]);
    });
});

describe('Property Image Validation', function () {
    test('validates maximum number of images', function () {
        $images = [];
        for ($i = 0; $i < 11; $i++) {
            $images[] = UploadedFile::fake()->image("house{$i}.jpg", 800, 600)->size(1024);
        }

        $propertyData = [
            'title' => 'Property with Too Many Images',
            'description' => 'Test property',
            'property_type_id' => $this->propertyType->id,
            'listing_type' => 'sale',
            'status' => 'active',
            'price' => 500000.00,
            'currency' => 'USD',
            'bedrooms' => 3,
            'bathrooms' => 2,
            'square_feet' => 2000,
            'address' => '123 Test Street',
            'location_id' => $this->location->id,
            'city' => 'Test City',
            'state' => 'Test State',
            'zip_code' => '12345',
            'country' => 'USA',
            'agent_id' => $this->agent->id,
            'featured' => false,
            'images' => $images,
        ];

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(VerifyCsrfToken::class)
            ->post(route('admin.properties.store'), $propertyData);

        $response->assertSessionHasErrors(['images']);
    });

    test('validates image file size', function () {
        $images = [
            UploadedFile::fake()->image('large.jpg', 800, 600)->size(6000), // 6MB - too large
        ];

        $propertyData = [
            'title' => 'Property with Large Image',
            'description' => 'Test property',
            'property_type_id' => $this->propertyType->id,
            'listing_type' => 'sale',
            'status' => 'active',
            'price' => 500000.00,
            'currency' => 'USD',
            'bedrooms' => 3,
            'bathrooms' => 2,
            'square_feet' => 2000,
            'address' => '123 Test Street',
            'location_id' => $this->location->id,
            'city' => 'Test City',
            'state' => 'Test State',
            'zip_code' => '12345',
            'country' => 'USA',
            'agent_id' => $this->agent->id,
            'featured' => false,
            'images' => $images,
        ];

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(VerifyCsrfToken::class)
            ->post(route('admin.properties.store'), $propertyData);

        $response->assertSessionHasErrors(['images.0']);
    });

    test('validates image file types', function () {
        $images = [
            UploadedFile::fake()->create('document.pdf', 1024, 'application/pdf'),
        ];

        $propertyData = [
            'title' => 'Property with Invalid File Type',
            'description' => 'Test property',
            'property_type_id' => $this->propertyType->id,
            'listing_type' => 'sale',
            'status' => 'active',
            'price' => 500000.00,
            'currency' => 'USD',
            'bedrooms' => 3,
            'bathrooms' => 2,
            'square_feet' => 2000,
            'address' => '123 Test Street',
            'location_id' => $this->location->id,
            'city' => 'Test City',
            'state' => 'Test State',
            'zip_code' => '12345',
            'country' => 'USA',
            'agent_id' => $this->agent->id,
            'featured' => false,
            'images' => $images,
        ];

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(VerifyCsrfToken::class)
            ->post(route('admin.properties.store'), $propertyData);

        $response->assertSessionHasErrors(['images.0']);
    });

    test('property creation succeeds without images', function () {
        $propertyData = [
            'title' => 'Property without Images',
            'description' => 'Test property with no images',
            'property_type_id' => $this->propertyType->id,
            'listing_type' => 'sale',
            'status' => 'active',
            'price' => 400000.00,
            'currency' => 'USD',
            'bedrooms' => 2,
            'bathrooms' => 1,
            'square_feet' => 1200,
            'address' => '456 No Image Street',
            'location_id' => $this->location->id,
            'city' => 'Test City',
            'state' => 'Test State',
            'zip_code' => '67890',
            'country' => 'USA',
            'agent_id' => $this->agent->id,
            'featured' => false,
        ];

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(VerifyCsrfToken::class)
            ->post(route('admin.properties.store'), $propertyData);

        $response->assertRedirect(route('admin.properties.index'))
            ->assertSessionHas('success');

        $property = Property::where('title', 'Property without Images')->first();
        expect($property)->not->toBeNull();
        expect($property->images)->toHaveCount(0);
    });
});

describe('Property Image Management', function () {
    test('can view property images in edit form', function () {
        $property = Property::factory()->create([
            'property_type_id' => $this->propertyType->id,
            'agent_id' => $this->agent->id,
            'location_id' => $this->location->id,
        ]);

        PropertyImage::factory(3)->create([
            'property_id' => $property->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.properties.edit', $property));

        $response->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('admin/properties/edit')
                ->has('property.images', 3)
                ->where('property.images.0.property_id', $property->id)
            );
    });

    test('image sort order is maintained when creating property', function () {
        $images = [
            UploadedFile::fake()->image('first.jpg', 800, 600)->size(1024),
            UploadedFile::fake()->image('second.jpg', 800, 600)->size(1024),
            UploadedFile::fake()->image('third.jpg', 800, 600)->size(1024),
        ];

        $propertyData = [
            'title' => 'Property with Ordered Images',
            'description' => 'Test property with image order',
            'property_type_id' => $this->propertyType->id,
            'listing_type' => 'sale',
            'status' => 'active',
            'price' => 500000.00,
            'currency' => 'USD',
            'bedrooms' => 3,
            'bathrooms' => 2,
            'square_feet' => 2000,
            'address' => '789 Order Street',
            'location_id' => $this->location->id,
            'city' => 'Test City',
            'state' => 'Test State',
            'zip_code' => '11111',
            'country' => 'USA',
            'agent_id' => $this->agent->id,
            'featured' => false,
            'images' => $images,
        ];

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(VerifyCsrfToken::class)
            ->post(route('admin.properties.store'), $propertyData);

        $response->assertRedirect(route('admin.properties.index'));

        $property = Property::where('title', 'Property with Ordered Images')->first();
        $sortedImages = $property->images->sortBy('sort_order');

        expect($sortedImages->first()->sort_order)->toBe(1);
        expect($sortedImages->skip(1)->first()->sort_order)->toBe(2);
        expect($sortedImages->skip(2)->first()->sort_order)->toBe(3);

        // First image should be primary
        expect($sortedImages->first()->is_primary)->toBe(true);
        expect($sortedImages->skip(1)->first()->is_primary)->toBe(false);
        expect($sortedImages->skip(2)->first()->is_primary)->toBe(false);
    });

    test('property show page includes all images with proper structure', function () {
        $property = Property::factory()->create([
            'property_type_id' => $this->propertyType->id,
            'agent_id' => $this->agent->id,
            'location_id' => $this->location->id,
        ]);

        PropertyImage::factory(3)->create([
            'property_id' => $property->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.properties.show', $property));

        $response->assertSuccessful()
            ->assertInertia(fn ($page) => $page
                ->component('admin/properties/show')
                ->has('property.images', 3)
                ->where('property.images.0.property_id', $property->id)
                ->whereType('property.images.0.image_url', 'string')
                ->whereType('property.images.0.dimensions', 'string')
                ->whereType('property.images.0.file_size', 'integer')
                ->whereType('property.images.0.is_primary', 'boolean')
            );
    });
});
