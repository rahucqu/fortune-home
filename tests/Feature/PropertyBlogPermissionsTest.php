<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed roles and permissions
    $seeder = new RolePermissionSeeder();
    $seeder->run();

    // Create users with different roles
    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    $this->agent = User::factory()->create();
    $this->agent->assignRole('agent');

    $this->user = User::factory()->create();
    $this->user->assignRole('user');
});

test('admin has correct property permissions', function () {
    expect($this->admin->can('properties.view-all'))->toBeTrue();
    expect($this->admin->can('properties.view-own'))->toBeFalse();
    expect($this->admin->can('properties.create'))->toBeTrue();
    expect($this->admin->can('properties.update'))->toBeTrue();
    expect($this->admin->can('properties.delete'))->toBeTrue();
    expect($this->admin->can('properties.publish'))->toBeTrue();
    expect($this->admin->can('properties.feature'))->toBeTrue();
});

test('agent has correct property permissions', function () {
    expect($this->agent->can('properties.view-all'))->toBeFalse();
    expect($this->agent->can('properties.view-own'))->toBeTrue();
    expect($this->agent->can('properties.create'))->toBeTrue();
    expect($this->agent->can('properties.update'))->toBeFalse(); // Key requirement: agents cannot edit
    expect($this->agent->can('properties.delete'))->toBeFalse(); // Key requirement: agents cannot delete
    expect($this->agent->can('properties.publish'))->toBeFalse();
    expect($this->agent->can('properties.feature'))->toBeFalse();
});

test('regular user has correct property permissions', function () {
    expect($this->user->can('properties.view-all'))->toBeFalse();
    expect($this->user->can('properties.view-own'))->toBeFalse();
    expect($this->user->can('properties.view-published'))->toBeTrue();
    expect($this->user->can('properties.create'))->toBeFalse();
    expect($this->user->can('properties.update'))->toBeFalse();
    expect($this->user->can('properties.delete'))->toBeFalse();
});

test('admin has correct blog permissions', function () {
    expect($this->admin->can('blog.view-all'))->toBeTrue();
    expect($this->admin->can('blog.view-own'))->toBeFalse();
    expect($this->admin->can('blog.create'))->toBeTrue();
    expect($this->admin->can('blog.update'))->toBeTrue();
    expect($this->admin->can('blog.delete'))->toBeTrue();
    expect($this->admin->can('blog.publish'))->toBeTrue();
    expect($this->admin->can('blog.feature'))->toBeTrue();
});

test('agent has correct blog permissions', function () {
    expect($this->agent->can('blog.view-all'))->toBeFalse();
    expect($this->agent->can('blog.view-own'))->toBeTrue();
    expect($this->agent->can('blog.create'))->toBeTrue();
    expect($this->agent->can('blog.update'))->toBeFalse();
    expect($this->agent->can('blog.update-own'))->toBeTrue();
    expect($this->agent->can('blog.delete'))->toBeFalse();
    expect($this->agent->can('blog.publish'))->toBeFalse();
    expect($this->agent->can('blog.feature'))->toBeFalse();
});

test('agent can approve comments on own blog posts', function () {
    expect($this->agent->can('blog-comments.approve'))->toBeFalse();
    expect($this->agent->can('blog-comments.approve-own'))->toBeTrue();
    expect($this->agent->can('blog-comments.moderate'))->toBeFalse();
});

test('regular user has correct blog permissions', function () {
    expect($this->user->can('blog.view-all'))->toBeFalse();
    expect($this->user->can('blog.view-own'))->toBeFalse();
    expect($this->user->can('blog.view-published'))->toBeTrue();
    expect($this->user->can('blog.create'))->toBeFalse();
    expect($this->user->can('blog.update'))->toBeFalse();
    expect($this->user->can('blog.delete'))->toBeFalse();
});

test('permission structure matches exact requirements', function () {
    // Agent CANNOT edit or delete properties (key requirement)
    expect($this->agent->can('properties.update'))->toBeFalse();
    expect($this->agent->can('properties.delete'))->toBeFalse();

    // Agent CAN create and view their own properties
    expect($this->agent->can('properties.create'))->toBeTrue();
    expect($this->agent->can('properties.view-own'))->toBeTrue();

    // Admin CAN manage all properties
    expect($this->admin->can('properties.view-all'))->toBeTrue();
    expect($this->admin->can('properties.update'))->toBeTrue();
    expect($this->admin->can('properties.delete'))->toBeTrue();

    // Blog authors can approve their own comments
    expect($this->agent->can('blog-comments.approve-own'))->toBeTrue();

    // Frontend should show buttons based on permissions
    expect($this->admin->can('properties.feature'))->toBeTrue();
    expect($this->agent->can('properties.feature'))->toBeFalse();
});
