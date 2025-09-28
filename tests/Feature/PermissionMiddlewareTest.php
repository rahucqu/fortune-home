<?php

declare(strict_types=1);

use App\Models\User;

test('users can access routes they have permission for', function () {
    $this->seed();

    $admin = User::where('email', 'admin@example.com')->first();

    if (! $admin) {
        $this->markTestSkipped('Admin user not found. Run database seeder first.');
    }

    $response = $this->actingAs($admin)->get(route('admin.system.users.index'));

    $response->assertStatus(200);
});

test('users cannot access routes they do not have permission for', function () {
    $this->seed();

    // Create a user without any roles
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('admin.system.users.index'));

    $response->assertStatus(403);
});

test('guest users are redirected to login', function () {
    $response = $this->get(route('admin.system.users.index'));

    $response->assertRedirect('/login');
});

test('permissions are correctly shared via inertia', function () {
    $this->seed();

    $admin = User::where('email', 'admin@example.com')->first();

    if (! $admin) {
        $this->markTestSkipped('Admin user not found. Run database seeder first.');
    }

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertInertia(fn ($page) => $page->has('auth.user.permissions')
        ->where('auth.user.permissions', fn ($permissions) => collect($permissions)->contains('system.users.view')
        )
    );
});
