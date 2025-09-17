<?php

declare(strict_types=1);

use App\Models\Tag;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Create admin role and user for authentication
    $adminRole = Role::firstOrCreate(['name' => 'admin']);

    // Create required permissions
    $permissions = [
        'access admin panel',
        'view tags',
        'create tags',
        'edit tags',
        'delete tags',
    ];

    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission]);
    }

    // Give admin all permissions
    $adminRole->syncPermissions($permissions);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');
});

it('can display tag index page', function () {
    $tags = Tag::factory()->count(3)->create();

    $response = $this->actingAs($this->admin)
        ->get(route('admin.tags.index'));

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Tags/Index')
            ->has('tags.data', 3)
        );
});

it('can search tags', function () {
    Tag::factory()->create(['name' => 'Laravel Framework']);
    Tag::factory()->create(['name' => 'Vue.js']);
    Tag::factory()->create(['description' => 'JavaScript framework']);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.tags.index', ['search' => 'Laravel']));

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->has('tags.data', 1)
            ->where('tags.data.0.name', 'Laravel Framework')
        );
});

it('can display create tag page', function () {
    $response = $this->actingAs($this->admin)
        ->get(route('admin.tags.create'));

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Tags/Create')
        );
});

it('can create a new tag', function () {
    $tagData = [
        'name' => 'New Technology',
        'description' => 'A tag for new technology posts',
        'color' => '#FF5733',
        'seo_title' => 'New Technology - Blog Tag',
        'seo_description' => 'Explore posts about new and emerging technologies',
        'seo_keywords' => 'technology, innovation, new tech',
        'is_active' => true,
        'sort_order' => 10,
    ];

    $response = $this->actingAs($this->admin)
        ->post(route('admin.tags.store'), $tagData);

    $response->assertRedirect(route('admin.tags.index'))
        ->assertSessionHas('success', 'Tag created successfully.');

    $this->assertDatabaseHas('tags', [
        'name' => 'New Technology',
        'slug' => 'new-technology',
        'description' => 'A tag for new technology posts',
        'color' => '#FF5733',
        'is_active' => true,
    ]);
});

it('auto-generates slug when creating tag', function () {
    $response = $this->actingAs($this->admin)
        ->post(route('admin.tags.store'), [
            'name' => 'Artificial Intelligence',
            'is_active' => true,
            'sort_order' => 0,
        ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('tags', [
        'name' => 'Artificial Intelligence',
        'slug' => 'artificial-intelligence',
    ]);
});

it('can display tag details', function () {
    $tag = Tag::factory()->create([
        'name' => 'Web Development',
        'description' => 'All about web development',
    ]);

    $response = $this->actingAs($this->admin)
        ->get(route('admin.tags.show', $tag));

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Tags/Show')
            ->where('tag.name', 'Web Development')
        );
});

it('can display edit tag page', function () {
    $tag = Tag::factory()->create();

    $response = $this->actingAs($this->admin)
        ->get(route('admin.tags.edit', $tag));

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Tags/Edit')
            ->where('tag.id', $tag->id)
        );
});

it('can update an existing tag', function () {
    $tag = Tag::factory()->create([
        'name' => 'Old Name',
        'slug' => 'old-name',
    ]);

    $updateData = [
        'name' => 'Updated Technology',
        'slug' => 'updated-technology',
        'description' => 'Updated description',
        'color' => '#00FF00',
        'is_active' => false,
        'sort_order' => 20,
    ];

    $response = $this->actingAs($this->admin)
        ->put(route('admin.tags.update', $tag), $updateData);

    $response->assertRedirect(route('admin.tags.index'))
        ->assertSessionHas('success', 'Tag updated successfully.');

    $tag->refresh();
    expect($tag->name)->toBe('Updated Technology');
    expect($tag->slug)->toBe('updated-technology');
    expect($tag->is_active)->toBeFalse();
});

it('can delete a tag', function () {
    $tag = Tag::factory()->create();

    $response = $this->actingAs($this->admin)
        ->delete(route('admin.tags.destroy', $tag));

    $response->assertRedirect(route('admin.tags.index'))
        ->assertSessionHas('success', 'Tag deleted successfully.');

    $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
});

it('requires name when creating tag', function () {
    $response = $this->actingAs($this->admin)
        ->post(route('admin.tags.store'), [
            'name' => '',
            'is_active' => true,
            'sort_order' => 0,
        ]);

    $response->assertSessionHasErrors(['name']);
});

it('validates unique slug', function () {
    Tag::factory()->create(['slug' => 'existing-tag']);

    $response = $this->actingAs($this->admin)
        ->post(route('admin.tags.store'), [
            'name' => 'Another Tag',
            'slug' => 'existing-tag',
            'is_active' => true,
            'sort_order' => 0,
        ]);

    $response->assertSessionHasErrors(['slug']);
});

it('validates color format', function () {
    $response = $this->actingAs($this->admin)
        ->post(route('admin.tags.store'), [
            'name' => 'Test Tag',
            'color' => 'invalid-color',
            'is_active' => true,
            'sort_order' => 0,
        ]);

    $response->assertSessionHasErrors(['color']);
});

it('validates seo title length', function () {
    $longTitle = str_repeat('a', 61); // Exceeds 60 character limit

    $response = $this->actingAs($this->admin)
        ->post(route('admin.tags.store'), [
            'name' => 'Test Tag',
            'seo_title' => $longTitle,
            'is_active' => true,
            'sort_order' => 0,
        ]);

    $response->assertSessionHasErrors(['seo_title']);
});

it('validates seo description length', function () {
    $longDescription = str_repeat('a', 161); // Exceeds 160 character limit

    $response = $this->actingAs($this->admin)
        ->post(route('admin.tags.store'), [
            'name' => 'Test Tag',
            'seo_description' => $longDescription,
            'is_active' => true,
            'sort_order' => 0,
        ]);

    $response->assertSessionHasErrors(['seo_description']);
});

it('can filter active tags', function () {
    Tag::factory()->active()->count(2)->create();
    Tag::factory()->inactive()->count(3)->create();

    $activeTags = Tag::active()->get();
    expect($activeTags)->toHaveCount(2);
    expect($activeTags->every(fn ($tag) => $tag->is_active))->toBeTrue();
});

it('can order tags by sort order and name', function () {
    Tag::factory()->create(['name' => 'Z Tag', 'sort_order' => 1]);
    Tag::factory()->create(['name' => 'A Tag', 'sort_order' => 1]);
    Tag::factory()->create(['name' => 'B Tag', 'sort_order' => 0]);

    $orderedTags = Tag::ordered()->get();

    expect($orderedTags->first()->name)->toBe('B Tag'); // Lower sort_order first
    expect($orderedTags->get(1)->name)->toBe('A Tag'); // Then alphabetical within same sort_order
    expect($orderedTags->last()->name)->toBe('Z Tag');
});

it('can search tags by name, description, and slug', function () {
    Tag::factory()->create(['name' => 'Laravel Framework', 'description' => 'PHP framework']);
    Tag::factory()->create(['name' => 'Vue.js', 'description' => 'JavaScript framework', 'slug' => 'vuejs']);
    Tag::factory()->create(['name' => 'React', 'description' => 'Library']);

    $searchResults = Tag::search('framework')->get();
    expect($searchResults)->toHaveCount(2);

    $searchResults = Tag::search('vue')->get();
    expect($searchResults)->toHaveCount(1);
    expect($searchResults->first()->name)->toBe('Vue.js');
});

it('prevents admin access for non-admin users', function () {
    $user = User::factory()->create(); // Regular user without admin role

    $response = $this->actingAs($user)
        ->get(route('admin.tags.index'));

    $response->assertForbidden();
});
