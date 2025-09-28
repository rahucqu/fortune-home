<?php

declare(strict_types=1);

use App\Models\BlogPost;
use App\Models\Location;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\User;

it('can fetch featured properties via v1 API', function () {
    // Create required test data
    $agent = User::factory()->create();
    $propertyType = PropertyType::factory()->create();
    $location = Location::factory()->create();

    // Create test properties
    $featuredProperty = Property::factory()->featured()->create([
        'agent_id' => $agent->id,
        'property_type_id' => $propertyType->id,
        'location_id' => $location->id,
        'published_at' => now(),
        'status' => 'active',
    ]);
    Property::factory()->create([
        'agent_id' => $agent->id,
        'property_type_id' => $propertyType->id,
        'location_id' => $location->id,
        'published_at' => now(),
        'status' => 'active',
    ]);

    $response = $this->getJson(route('api.v1.home.featured-properties'));

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'slug',
                    'price',
                ],
            ],
            'code',
            'timestamp',
            'total_count',
        ]);

    // Should only return featured properties
    $data = $response->json('data');
    expect(collect($data)->pluck('id')->toArray())->toContain($featuredProperty->id);
    expect($response->json('success'))->toBe(true);
    expect($response->json('message'))->toBe('Featured properties retrieved successfully');
});

it('can fetch recent rental properties via v1 API', function () {
    // Create required test data
    $agent = User::factory()->create();
    $propertyType = PropertyType::factory()->create();
    $location = Location::factory()->create();

    // Create test rental properties
    Property::factory()->forRent()->count(3)->create([
        'agent_id' => $agent->id,
        'property_type_id' => $propertyType->id,
        'location_id' => $location->id,
        'published_at' => now(),
        'status' => 'active',
    ]);
    Property::factory()->forSale()->create([
        'agent_id' => $agent->id,
        'property_type_id' => $propertyType->id,
        'location_id' => $location->id,
        'published_at' => now(),
        'status' => 'active',
    ]); // Should not appear in results

    $response = $this->getJson(route('api.v1.home.recent-rental-properties'));

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'slug',
                    'price',
                ],
            ],
            'code',
            'timestamp',
            'total_count',
        ]);

    // Should only return rental properties
    $data = $response->json('data');
    expect($data)->toHaveCount(3);
    expect($response->json('success'))->toBe(true);
});

it('can fetch blog posts via v1 API', function () {
    // Create test blog posts
    $user = User::factory()->create();
    BlogPost::factory()->published()->count(3)->create([
        'author_id' => $user->id,
    ]);
    BlogPost::factory()->draft()->create([
        'author_id' => $user->id,
    ]); // Should not appear in results

    $response = $this->getJson(route('api.v1.home.blog-posts'));

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'slug',
                    'excerpt',
                ],
            ],
            'code',
            'timestamp',
            'total_count',
        ]);

    // Should only return published posts
    $data = $response->json('data');
    expect($data)->toHaveCount(3);
    expect($response->json('success'))->toBe(true);
});
