<?php

declare(strict_types=1);

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create user role with settings permissions
    $userRole = Role::create([
        'name' => 'user',
        'display_name' => 'User',
        'description' => 'General users',
        'guard_name' => 'web',
        'is_default' => true,
    ]);

    // Create settings permissions
    $permissions = [
        'settings.password', 'settings.profile', 'settings.view', 'settings.appearance',
    ];

    foreach ($permissions as $permissionName) {
        $permission = Permission::create([
            'name' => $permissionName,
            'guard_name' => 'web',
            'group' => 'Settings & Profile',
        ]);
        $userRole->givePermissionTo($permission);
    }

    Storage::fake('public');
    Storage::fake('local');
});

test('can update profile photo with cropping', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    // Simulate a base64 encoded image (simple test data)
    $base64Image = 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAYEBQYFBAYGBQYHBwYIChAKCgkJChQODwwQFxQYGBcUFhYaHSUfGhsjHBYWICwgIyYnKSopGR8tMC0oMCUoKSj/2wBDAQcHBwoIChMKChMoGhYaKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCj/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCdABmX/9k=';

    $response = $this
        ->actingAs($user)
        ->post(route('admin.settings.profile.photo.crop'), [
            'cropped_image' => $base64Image,
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('status', 'Profile photo updated successfully.');

    // Check if the user's profile photo path was updated
    $user->refresh();
    expect($user->profile_photo_path)->not->toBeNull();

    // Check if the file was stored
    expect(Storage::disk('local')->exists($user->profile_photo_path))->toBeTrue();
});

test('validates that cropped_image is required', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $response = $this
        ->actingAs($user)
        ->post(route('admin.settings.profile.photo.crop'), []);

    $response->assertSessionHasErrors(['cropped_image']);
});

test('deletes old profile photo when uploading new one', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    // First, upload an image
    $oldFile = UploadedFile::fake()->image('old-photo.jpg');
    Storage::disk('local')->put('profile-photos/old-photo.jpg', $oldFile->getContent());

    $user->forceFill([
        'profile_photo_path' => 'profile-photos/old-photo.jpg',
    ])->save();

    // Now upload a cropped image
    $base64Image = 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAYEBQYFBAYGBQYHBwYIChAKCgkJChQODwwQFxQYGBcUFhYaHSUfGhsjHBYWICwgIyYnKSopGR8tMC0oMCUoKSj/2wBDAQcHBwoIChMKChMoGhYaKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCj/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCdABmX/9k=';

    $this
        ->actingAs($user)
        ->post(route('admin.settings.profile.photo.crop'), [
            'cropped_image' => $base64Image,
        ]);

    // Check that old file was deleted
    expect(Storage::disk('local')->exists('profile-photos/old-photo.jpg'))->toBeFalse();

    // Check that new file exists
    $user->refresh();
    expect(Storage::disk('local')->exists($user->profile_photo_path))->toBeTrue();
});

test('requires permission to update profile photo', function () {
    // Create a user without permission
    $userWithoutPermission = User::factory()->create();

    $base64Image = 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAYEBQYFBAYGBQYHBwYIChAKCgkJChQODwwQFxQYGBcUFhYaHSUfGhsjHBYWICwgIyYnKSopGR8tMC0oMCUoKSj/2wBDAQcHBwoIChMKChMoGhYaKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCj/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCdABmX/9k=';

    $response = $this
        ->actingAs($userWithoutPermission)
        ->post(route('admin.settings.profile.photo.crop'), [
            'cropped_image' => $base64Image,
        ]);

    $response->assertForbidden();
});
