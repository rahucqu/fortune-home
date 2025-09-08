<?php

declare(strict_types=1);

use App\Models\User;

beforeEach(function () {
    // Seed roles and permissions
    $this->artisan('db:seed', ['--class' => 'BlogRolePermissionSeeder']);
});

it('creates blog roles successfully', function () {
    expect(\Spatie\Permission\Models\Role::where('name', 'super-admin')->exists())->toBeTrue();
    expect(\Spatie\Permission\Models\Role::where('name', 'editor')->exists())->toBeTrue();
    expect(\Spatie\Permission\Models\Role::where('name', 'author')->exists())->toBeTrue();
    expect(\Spatie\Permission\Models\Role::where('name', 'contributor')->exists())->toBeTrue();
});

it('creates blog permissions successfully', function () {
    expect(\Spatie\Permission\Models\Permission::where('name', 'view posts')->exists())->toBeTrue();
    expect(\Spatie\Permission\Models\Permission::where('name', 'create posts')->exists())->toBeTrue();
    expect(\Spatie\Permission\Models\Permission::where('name', 'edit posts')->exists())->toBeTrue();
    expect(\Spatie\Permission\Models\Permission::where('name', 'delete posts')->exists())->toBeTrue();
    expect(\Spatie\Permission\Models\Permission::where('name', 'publish posts')->exists())->toBeTrue();
});

it('assigns correct permissions to super admin', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super-admin');
    
    expect($superAdmin->can('view posts'))->toBeTrue();
    expect($superAdmin->can('create posts'))->toBeTrue();
    expect($superAdmin->can('edit posts'))->toBeTrue();
    expect($superAdmin->can('delete posts'))->toBeTrue();
    expect($superAdmin->can('publish posts'))->toBeTrue();
    expect($superAdmin->can('manage settings'))->toBeTrue();
});

it('assigns correct permissions to editor', function () {
    $editor = User::factory()->create();
    $editor->assignRole('editor');
    
    expect($editor->can('view posts'))->toBeTrue();
    expect($editor->can('create posts'))->toBeTrue();
    expect($editor->can('edit posts'))->toBeTrue();
    expect($editor->can('delete posts'))->toBeTrue();
    expect($editor->can('publish posts'))->toBeTrue();
    expect($editor->can('manage settings'))->toBeFalse();
});

it('assigns correct permissions to author', function () {
    $author = User::factory()->create();
    $author->assignRole('author');
    
    expect($author->can('view posts'))->toBeTrue();
    expect($author->can('create posts'))->toBeTrue();
    expect($author->can('view own posts'))->toBeTrue();
    expect($author->can('edit own posts'))->toBeTrue();
    expect($author->can('delete own posts'))->toBeTrue();
    expect($author->can('publish posts'))->toBeFalse();
    expect($author->can('edit posts'))->toBeFalse();
});

it('assigns correct permissions to contributor', function () {
    $contributor = User::factory()->create();
    $contributor->assignRole('contributor');
    
    expect($contributor->can('view own posts'))->toBeTrue();
    expect($contributor->can('create posts'))->toBeTrue();
    expect($contributor->can('edit own posts'))->toBeTrue();
    expect($contributor->can('publish posts'))->toBeFalse();
    expect($contributor->can('delete own posts'))->toBeFalse();
    expect($contributor->can('edit posts'))->toBeFalse();
});

it('denies permissions to users without roles', function () {
    $user = User::factory()->create();
    
    expect($user->can('view posts'))->toBeFalse();
    expect($user->can('create posts'))->toBeFalse();
    expect($user->can('edit posts'))->toBeFalse();
    expect($user->can('delete posts'))->toBeFalse();
    expect($user->can('publish posts'))->toBeFalse();
});
