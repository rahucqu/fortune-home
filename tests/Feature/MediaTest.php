<?php

declare(strict_types=1);

use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create roles for testing
    Role::create(['name' => 'admin']);
    Role::create(['name' => 'user']);
    Storage::fake('public');
});

test('admin can view media index', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Media::factory()->count(3)->create(['uploaded_by' => $admin->id]);

    $response = $this->actingAs($admin)->get('/admin/media');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Media/Index')
        ->has('media.data', 3)
        ->has('stats')
    );
});

test('admin can view create media page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->get('/admin/media/create');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page->component('Admin/Media/Create'));
});

test('admin can upload a file', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $file = UploadedFile::fake()->image('test.jpg', 640, 480);

    $response = $this->actingAs($admin)->post('/admin/media', [
        'file' => $file,
        'name' => 'Test Image',
        'alt_text' => 'A test image',
        'description' => 'This is a test image',
        'is_active' => true,
    ]);

    $response->assertRedirect('/admin/media');
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('media', [
        'name' => 'Test Image',
        'original_name' => 'test.jpg',
        'type' => 'image',
        'alt_text' => 'A test image',
        'description' => 'This is a test image',
        'is_active' => true,
        'uploaded_by' => $admin->id,
    ]);
});

test('admin can view media details', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $media = Media::factory()->create(['uploaded_by' => $admin->id]);

    $response = $this->actingAs($admin)->get("/admin/media/{$media->id}");

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Media/Show')
        ->where('media.id', $media->id)
    );
});

test('admin can edit media', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $media = Media::factory()->create(['uploaded_by' => $admin->id]);

    $response = $this->actingAs($admin)->get("/admin/media/{$media->id}/edit");

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Media/Edit')
        ->where('media.id', $media->id)
    );
});

test('admin can update media', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $media = Media::factory()->create(['uploaded_by' => $admin->id]);

    $response = $this->actingAs($admin)->put("/admin/media/{$media->id}", [
        'name' => 'Updated Name',
        'alt_text' => 'Updated alt text',
        'description' => 'Updated description',
        'is_active' => false,
    ]);

    $response->assertRedirect('/admin/media');
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('media', [
        'id' => $media->id,
        'name' => 'Updated Name',
        'alt_text' => 'Updated alt text',
        'description' => 'Updated description',
        'is_active' => false,
    ]);
});

test('admin can delete media', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Storage::disk('public')->put('media/test.jpg', 'fake content');
    $media = Media::factory()->create([
        'uploaded_by' => $admin->id,
        'path' => 'media/test.jpg',
    ]);

    $response = $this->actingAs($admin)->delete("/admin/media/{$media->id}");

    $response->assertRedirect('/admin/media');
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('media', ['id' => $media->id]);
});

test('validates file upload requirements', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->post('/admin/media', []);

    $response->assertSessionHasErrors(['file']);
});

test('validates file size limit', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $file = UploadedFile::fake()->create('large.pdf', 11 * 1024); // 11MB

    $response = $this->actingAs($admin)->post('/admin/media', [
        'file' => $file,
    ]);

    $response->assertSessionHasErrors(['file']);
});

test('non-admin user cannot access media management', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $response = $this->actingAs($user)->get('/admin/media');

    $response->assertForbidden();
});

test('guest user cannot access media management', function () {
    $response = $this->get('/admin/media');

    $response->assertRedirect('/login');
});

// Model Tests
test('media has correct fillable fields', function () {
    $media = new Media();
    $fillable = $media->getFillable();

    expect($fillable)->toContain('name', 'file_name', 'original_name', 'path', 'mime_type', 'type', 'size', 'width', 'height', 'alt_text', 'description', 'metadata', 'is_active', 'uploaded_by');
});

test('media belongs to uploader', function () {
    $user = User::factory()->create();
    $media = Media::factory()->create(['uploaded_by' => $user->id]);

    expect($media->uploader)->toBeInstanceOf(User::class);
    expect($media->uploader->id)->toBe($user->id);
});

test('media detects file type from mime type', function () {
    expect(Media::getTypeFromMimeType('image/jpeg'))->toBe('image');
    expect(Media::getTypeFromMimeType('video/mp4'))->toBe('video');
    expect(Media::getTypeFromMimeType('audio/mp3'))->toBe('audio');
    expect(Media::getTypeFromMimeType('application/pdf'))->toBe('document');
    expect(Media::getTypeFromMimeType('text/plain'))->toBe('other');
});

test('media has active scope', function () {
    Media::factory()->create(['is_active' => true]);
    Media::factory()->create(['is_active' => false]);

    $activeMedia = Media::active()->get();

    expect($activeMedia)->toHaveCount(1);
    expect($activeMedia->first()->is_active)->toBeTrue();
});

test('media has images scope', function () {
    Media::factory()->image()->create();
    Media::factory()->document()->create();

    $images = Media::images()->get();

    expect($images)->toHaveCount(1);
    expect($images->first()->type)->toBe('image');
});

test('media has search scope', function () {
    Media::factory()->create(['name' => 'Test Image', 'description' => 'A test file']);
    Media::factory()->create(['name' => 'Another File', 'description' => 'Different content']);

    $searchResults = Media::search('Test')->get();

    expect($searchResults)->toHaveCount(1);
    expect($searchResults->first()->name)->toBe('Test Image');
});
