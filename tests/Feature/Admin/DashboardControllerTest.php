<?php

declare(strict_types=1);

use App\Models\Agent;
use App\Models\Amenity;
use App\Models\Location;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $adminRole = Role::firstOrCreate(['name' => 'admin']);

    // Create required permissions
    $permissions = [
        'access admin panel',
        'view dashboard',
        'view properties',
        'create properties',
        'edit properties',
        'delete properties',
    ];

    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission]);
    }

    // Give admin all permissions
    $adminRole->syncPermissions($permissions);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

it('displays admin dashboard with property statistics', function () {
    // Create test data
    $properties = Property::factory()->count(5)->create([
        'property_type_id' => PropertyType::factory()->create()->id,
        'agent_id' => Agent::factory()->create()->id,
        'location_id' => Location::factory()->create()->id,
        'status' => 'available',
    ]);

    Property::factory()->count(2)->create([
        'property_type_id' => PropertyType::factory()->create()->id,
        'agent_id' => Agent::factory()->create()->id,
        'location_id' => Location::factory()->create()->id,
        'status' => 'sold',
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Dashboard')
            ->has('propertyStats')
            ->where('propertyStats.total_properties', 7)
            ->where('propertyStats.available_properties', 5)
            ->where('propertyStats.sold_properties', 2)
        );
});

it('calculates property statistics correctly', function () {
    // Create featured properties
    Property::factory()->count(3)->create([
        'property_type_id' => PropertyType::factory()->create()->id,
        'agent_id' => Agent::factory()->create()->id,
        'location_id' => Location::factory()->create()->id,
        'is_featured' => true,
        'price' => 500000,
    ]);

    // Create regular properties
    Property::factory()->count(2)->create([
        'property_type_id' => PropertyType::factory()->create()->id,
        'agent_id' => Agent::factory()->create()->id,
        'location_id' => Location::factory()->create()->id,
        'is_featured' => false,
        'price' => 300000,
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Dashboard')
            ->where('propertyStats.total_properties', 5)
            ->where('propertyStats.featured_properties', 3)
            ->where('propertyStats.total_value', 2100000) // (3 * 500000) + (2 * 300000)
            ->where('propertyStats.average_price', 420000) // 2100000 / 5
        );
});

it('shows recent properties on dashboard', function () {
    $recentProperty = Property::factory()->create([
        'title' => 'Recent Test Property',
        'property_type_id' => PropertyType::factory()->create()->id,
        'agent_id' => Agent::factory()->create()->id,
        'location_id' => Location::factory()->create()->id,
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Dashboard')
            ->has('recent_properties')
            ->where('recent_properties.0.title', 'Recent Test Property')
        );
});

it('counts agents, locations, and amenities correctly', function () {
    Agent::factory()->count(3)->create();
    Location::factory()->count(5)->create();
    Amenity::factory()->count(10)->create();

    $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Dashboard')
            ->where('propertyStats.total_agents', 3)
            ->where('propertyStats.total_locations', 5)
            ->where('propertyStats.total_amenities', 10)
        );
});

it('handles empty property data gracefully', function () {
    // No properties created

    $this->actingAs($this->admin)
        ->get(route('admin.dashboard'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Dashboard')
            ->where('propertyStats.total_properties', 0)
            ->where('propertyStats.available_properties', 0)
            ->where('propertyStats.sold_properties', 0)
            ->where('propertyStats.rented_properties', 0)
            ->where('propertyStats.featured_properties', 0)
            ->where('propertyStats.total_value', 0)
            ->where('propertyStats.average_price', 0)
        );
});
