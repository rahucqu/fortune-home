<?php

declare(strict_types=1);

use App\Enums\LocationType;
use App\Enums\PropertyTypeCategory;
use App\Models\Location;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\PropertyView;
use App\Models\User;
use Carbon\Carbon;

use function Pest\Laravel\getJson;

beforeEach(function () {
    // Create required models for testing
    $this->location = Location::factory()->create([
        'type' => LocationType::CITY,
        'name' => 'Test City',
        'is_active' => true,
    ]);

    $this->propertyType = PropertyType::factory()->create([
        'name' => 'Test Property Type',
        'category' => PropertyTypeCategory::RESIDENTIAL,
    ]);

    $this->agent = User::factory()->create();

    $this->property = Property::factory()->create([
        'property_type_id' => $this->propertyType->id,
        'location_id' => $this->location->id,
        'agent_id' => $this->agent->id,
        'title' => 'Test Property',
        'price' => 500000,
    ]);
});

it('returns property views data for the last 30 days', function () {
    // Create some property views with different dates
    $today = Carbon::today();
    $tenDaysAgo = $today->copy()->subDays(10);
    $twentyDaysAgo = $today->copy()->subDays(20);
    $thirtyDaysAgo = $today->copy()->subDays(30);
    $fortyDaysAgo = $today->copy()->subDays(40); // This should not be included

    // Create views for different days
    PropertyView::factory()->create([
        'property_id' => $this->property->id,
        'created_at' => $today,
    ]);
    PropertyView::factory()->create([
        'property_id' => $this->property->id,
        'created_at' => $today,
    ]);
    PropertyView::factory()->create([
        'property_id' => $this->property->id,
        'created_at' => $tenDaysAgo,
    ]);
    PropertyView::factory()->create([
        'property_id' => $this->property->id,
        'created_at' => $twentyDaysAgo,
    ]);
    PropertyView::factory()->create([
        'property_id' => $this->property->id,
        'created_at' => $thirtyDaysAgo,
    ]);
    // This view should not be included (older than 30 days)
    PropertyView::factory()->create([
        'property_id' => $this->property->id,
        'created_at' => $fortyDaysAgo,
    ]);

    $response = getJson(route('properties.views', ['id' => $this->property->id]));

    $response->assertOk();

    $data = $response->json('data');

    // Should have 30 entries (one for each day)
    expect($data)->toHaveCount(30);

    // Check that we have the correct view counts for specific dates
    $todayEntry = collect($data)->first(fn ($item) => $item['date'] === $today->format('M d'));
    expect($todayEntry['views'])->toBe(2);

    $tenDaysAgoEntry = collect($data)->first(fn ($item) => $item['date'] === $tenDaysAgo->format('M d'));
    expect($tenDaysAgoEntry['views'])->toBe(1);

    $twentyDaysAgoEntry = collect($data)->first(fn ($item) => $item['date'] === $twentyDaysAgo->format('M d'));
    expect($twentyDaysAgoEntry['views'])->toBe(1);

    // Days with no views should return 0
    $dayWithNoViews = collect($data)->filter(fn ($item) => $item['views'] === 0);
    expect($dayWithNoViews)->not->toBeEmpty();
});

it('returns empty data for property with no views', function () {
    // Create a new property with no views
    $propertyWithNoViews = Property::factory()->create([
        'property_type_id' => $this->propertyType->id,
        'location_id' => $this->location->id,
        'agent_id' => $this->agent->id,
    ]);

    $response = getJson(route('properties.views', ['id' => $propertyWithNoViews->id]));

    $response->assertOk();

    $data = $response->json('data');

    // Should have 30 entries (one for each day), all with 0 views
    expect($data)->toHaveCount(30);

    // All entries should have 0 views
    foreach ($data as $entry) {
        expect($entry['views'])->toBe(0);
    }
});

it('returns data for non-existent property', function () {
    $nonExistentPropertyId = 99999;

    $response = getJson(route('properties.views', ['id' => $nonExistentPropertyId]));

    $response->assertOk(); // The API doesn't check if property exists, just returns empty data

    $data = $response->json('data');
    expect($data)->toHaveCount(30);

    // All entries should have 0 views
    foreach ($data as $entry) {
        expect($entry['views'])->toBe(0);
    }
});

it('fills missing dates with zero views', function () {
    // Create views only for today
    PropertyView::factory()->count(3)->create([
        'property_id' => $this->property->id,
        'created_at' => Carbon::today(),
    ]);

    $response = getJson(route('properties.views', ['id' => $this->property->id]));

    $response->assertOk();

    $data = $response->json('data');

    // Should have 30 entries
    expect($data)->toHaveCount(30);

    // Today should have 3 views
    $todayEntry = collect($data)->first(fn ($item) => $item['date'] === Carbon::today()->format('M d'));
    expect($todayEntry['views'])->toBe(3);

    // All other days should have 0 views
    $otherDays = collect($data)->filter(fn ($item) => $item['date'] !== Carbon::today()->format('M d'));
    foreach ($otherDays as $day) {
        expect($day['views'])->toBe(0);
    }
});

it('returns correctly formatted date strings', function () {
    PropertyView::factory()->create([
        'property_id' => $this->property->id,
        'created_at' => Carbon::today(),
    ]);

    $response = getJson(route('properties.views', ['id' => $this->property->id]));

    $response->assertOk();

    $data = $response->json('data');

    // Check date format (should be "M d" format like "Jan 15")
    foreach ($data as $entry) {
        expect($entry['date'])->toMatch('/^[A-Za-z]{3} \d{1,2}$/');
        expect($entry)->toHaveKey('views');
        expect($entry['views'])->toBeInt();
    }
});
