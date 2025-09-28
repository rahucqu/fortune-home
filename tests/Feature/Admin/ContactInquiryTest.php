<?php

declare(strict_types=1);

use App\Enums\ContactInquiryStatus;
use App\Models\ContactInquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create roles for testing
    $this->superAdminRole = Role::create(['name' => 'super_admin', 'display_name' => 'Super Administrator', 'guard_name' => 'web']);
    $this->adminRole = Role::create(['name' => 'admin', 'display_name' => 'Administrator', 'guard_name' => 'web']);
    $this->userRole = Role::create(['name' => 'user', 'display_name' => 'User', 'guard_name' => 'web']);

    // Create contact inquiry permissions
    $permissions = [
        'contact-inquiries.view-all',
        'contact-inquiries.view',
        'contact-inquiries.create',
        'contact-inquiries.update',
        'contact-inquiries.delete',
    ];

    foreach ($permissions as $permission) {
        $perm = Permission::create(['name' => $permission, 'guard_name' => 'web']);
        $this->adminRole->givePermissionTo($perm);
        $this->superAdminRole->givePermissionTo($perm);
    }
});

test('admin can view list of contact inquiries', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    ContactInquiry::factory()->count(5)->create();

    $response = $this->actingAs($admin)
        ->get(route('admin.contact-inquiries.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('admin/contact-inquiry/index')
        ->has('inquiries.data', 5)
    );
});

test('admin can view a specific contact inquiry', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $inquiry = ContactInquiry::factory()->create();

    $response = $this->actingAs($admin)
        ->get(route('admin.contact-inquiries.show', $inquiry));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('admin/contact-inquiry/show')
        ->has('inquiry')
        ->where('inquiry.id', $inquiry->id)
    );
});

test('admin can update contact inquiry status', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $inquiry = ContactInquiry::factory()->create([
        'status' => ContactInquiryStatus::NEW->value,
    ]);

    $response = $this->actingAs($admin)
        ->put(route('admin.contact-inquiries.update', $inquiry), [
            'status' => ContactInquiryStatus::IN_PROGRESS->value,
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('contact_inquiries', [
        'id' => $inquiry->id,
        'status' => ContactInquiryStatus::IN_PROGRESS->value,
    ]);
});

test('admin can assign contact inquiry to a user', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $assignee = User::factory()->create();
    $inquiry = ContactInquiry::factory()->create([
        'assigned_to' => null,
    ]);

    $response = $this->actingAs($admin)
        ->put(route('admin.contact-inquiries.update', $inquiry), [
            'assigned_to' => $assignee->id,
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('contact_inquiries', [
        'id' => $inquiry->id,
        'assigned_to' => $assignee->id,
    ]);
});

test('admin can add notes to contact inquiry', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $inquiry = ContactInquiry::factory()->create([
        'notes' => null,
    ]);

    $notes = 'These are some important notes about this inquiry.';

    $response = $this->actingAs($admin)
        ->put(route('admin.contact-inquiries.update', $inquiry), [
            'notes' => $notes,
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('contact_inquiries', [
        'id' => $inquiry->id,
        'notes' => $notes,
    ]);
});

test('admin can delete a contact inquiry', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $inquiry = ContactInquiry::factory()->create();

    $response = $this->actingAs($admin)
        ->delete(route('admin.contact-inquiries.destroy', $inquiry));

    $response->assertRedirect(route('admin.contact-inquiries.index'));
    $this->assertModelMissing($inquiry);
});

test('regular user cannot access contact inquiry admin pages', function () {
    $user = User::factory()->create();
    $user->assignRole('user');

    $inquiry = ContactInquiry::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.contact-inquiries.index'))
        ->assertForbidden();

    $this->actingAs($user)
        ->get(route('admin.contact-inquiries.show', $inquiry))
        ->assertForbidden();

    $this->actingAs($user)
        ->put(route('admin.contact-inquiries.update', $inquiry), [
            'status' => ContactInquiryStatus::IN_PROGRESS->value,
        ])
        ->assertForbidden();

    $this->actingAs($user)
        ->delete(route('admin.contact-inquiries.destroy', $inquiry))
        ->assertForbidden();
});
