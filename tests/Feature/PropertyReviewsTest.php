<?php

declare(strict_types=1);

use App\Models\Location;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create roles and permissions
    $this->adminRole = Role::create(['name' => 'admin', 'display_name' => 'Administrator', 'guard_name' => 'web']);
    $this->userRole = Role::create(['name' => 'user', 'display_name' => 'User', 'guard_name' => 'web']);
    $this->agentRole = Role::create(['name' => 'agent', 'display_name' => 'Agent', 'guard_name' => 'web']);

    // Create permissions
    $permissions = [
        'reviews.view',
        'reviews.create',
        'reviews.update',
        'reviews.delete',
    ];

    foreach ($permissions as $permission) {
        Permission::create(['name' => $permission, 'guard_name' => 'web']);
    }

    $this->adminRole->givePermissionTo($permissions);
    $this->userRole->givePermissionTo(['reviews.create', 'reviews.view']);

    // Create users
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    $this->user = User::factory()->create();
    $this->user->assignRole('user');

    $this->agent = User::factory()->create();
    $this->agent->assignRole('agent');

    // Setup test property
    $this->location = Location::factory()->create();
    $this->propertyType = PropertyType::factory()->create();

    $this->property = Property::factory()->create([
        'property_type_id' => $this->propertyType->id,
        'location_id' => $this->location->id,
        'agent_id' => $this->agent->id,
    ]);
});

test('can create a review for a property', function () {
    $review = $this->property->addReview(
        userId: $this->user->id,
        rating: 5,
        comment: 'This property is amazing. Highly recommended!',
        autoApprove: true
    );

    expect($review)->toBeInstanceOf(Review::class);
    expect($review->user_id)->toBe($this->user->id);
    expect($review->rating)->toBe(5);
    expect($review->comment)->toBe('This property is amazing. Highly recommended!');
    expect($review->is_approved)->toBeTrue();

    // Check property updates
    $this->property->refresh();
    expect($this->property->reviews_count)->toBe(1);
    expect($this->property->average_rating)->toBe(5.0);
});
