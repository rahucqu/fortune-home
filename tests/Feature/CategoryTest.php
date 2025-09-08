<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create roles for testing
    Role::create(['name' => 'admin']);
    Role::create(['name' => 'user']);
});

test('admin can view categories index', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Category::factory(3)->create();

    $response = $this->actingAs($admin)->get('/admin/categories');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Categories/Index')
        ->has('categories.data', 3)
    );
});

test('admin can view create category page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $response = $this->actingAs($admin)->get('/admin/categories/create');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page->component('Admin/Categories/Create'));
});

test('admin can create a category', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $categoryData = [
        'name' => 'Technology',
        'description' => 'All about technology',
        'is_active' => true,
        'sort_order' => 1,
    ];

    $response = $this->actingAs($admin)->post('/admin/categories', $categoryData);

    $response->assertRedirect('/admin/categories');
    $this->assertDatabaseHas('categories', [
        'name' => 'Technology',
        'slug' => 'technology',
        'description' => 'All about technology',
    ]);
});

test('admin can view a category', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $category = Category::factory()->create();

    $response = $this->actingAs($admin)->get("/admin/categories/{$category->slug}");

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Categories/Show')
        ->has('category')
    );
});

test('admin can edit a category', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $category = Category::factory()->create();

    $response = $this->actingAs($admin)->get("/admin/categories/{$category->slug}/edit");

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('Admin/Categories/Edit')
        ->has('category')
    );
});

test('admin can update a category', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $category = Category::factory()->create();

    $updateData = [
        'name' => 'Updated Technology',
        'description' => 'Updated description',
        'is_active' => false,
        'sort_order' => 5,
    ];

    $response = $this->actingAs($admin)->put("/admin/categories/{$category->slug}", $updateData);

    $response->assertRedirect('/admin/categories');
    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'Updated Technology',
        'slug' => 'updated-technology',
        'description' => 'Updated description',
        'is_active' => false,
    ]);
});

test('admin can delete a category without posts', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $category = Category::factory()->create();

    $response = $this->actingAs($admin)->delete("/admin/categories/{$category->slug}");

    $response->assertRedirect('/admin/categories');
    $this->assertModelMissing($category);
});

test('slug is auto-generated from name', function () {
    $category = Category::create([
        'name' => 'Hello World',
        'is_active' => true,
        'sort_order' => 0,
    ]);

    expect($category->slug)->toBe('hello-world');
});

test('custom slug is preserved when provided', function () {
    $category = Category::create([
        'name' => 'Hello World',
        'slug' => 'custom-slug',
        'is_active' => true,
        'sort_order' => 0,
    ]);

    expect($category->slug)->toBe('custom-slug');
});
