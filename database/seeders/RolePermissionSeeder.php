<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $groupPermissions = $this->getGroupPermissions();

        // Update or create all roles
        $this->updateOrCreateAllRoles($this->getRoles());

        // Update or create all permissions and assign to roles
        $this->updateOrCreatePermissionsAndAssignToAppropriateRoles($groupPermissions);

        // Clean up orphaned permissions that are no longer defined
        $this->cleanupOrphanedPermissions($groupPermissions);
    }

    private function updateOrCreateAllRoles(array $roles): void
    {
        $roleNames = collect($roles)->pluck('name')->toArray();

        foreach ($roles as $data) {
            Role::updateOrCreate(
                ['name' => $data['name']],
                $data
            );
        }

        // Note: Keeping all existing roles since we can't distinguish by is_default column
        // Delete roles that are no longer defined is disabled for safety
        // Role::whereNotIn('name', $roleNames)->delete();
    }

    private function updateOrCreatePermissionsAndAssignToAppropriateRoles(array $groupPermissions): void
    {
        foreach ($groupPermissions as $group => $permissions) {
            foreach ($permissions as $permissionName => $roles) {
                $permission = Permission::updateOrCreate(
                    [
                        'name' => $permissionName,
                        'guard_name' => 'web', // Ensure guard_name is set
                    ],
                    [
                        'name' => $permissionName,
                        'guard_name' => 'web',
                    ]
                );

                // Sync roles (this will remove old assignments and add new ones)
                $permission->syncRoles($roles);
            }
        }
    }

    private function cleanupOrphanedPermissions(array $groupPermissions): void
    {
        // Get all permission names that should exist
        $expectedPermissions = [];
        foreach ($groupPermissions as $group => $permissions) {
            foreach ($permissions as $permissionName => $roles) {
                $expectedPermissions[] = $permissionName;
            }
        }

        // Delete permissions that are no longer defined
        Permission::whereNotIn('name', $expectedPermissions)->delete();
    }

    public function getGroupPermissions(): array
    {
        return [
            // System Administration
            'System Administration' => [
                'system.users.view' => [
                    'admin',
                ],
                'system.users.create' => [
                    'admin',
                ],
                'system.users.update' => [
                    'admin',
                ],
                'system.users.delete' => [
                    'admin',
                ],
                'system.roles.view' => [
                    'admin',
                ],
                'system.roles.create' => [
                    'admin',
                ],
                'system.roles.update' => [
                    'admin',
                ],
                'system.roles.delete' => [
                    'admin',
                ],
            ],

            // Location Management
            'Location Management' => [
                'locations.view' => [
                    'super_admin', 'admin',
                ],
                'locations.create' => [
                    'super_admin', 'admin',
                ],
                'locations.update' => [
                    'super_admin', 'admin',
                ],
                'locations.delete' => [
                    'super_admin', 'admin',
                ],
                'locations.publish' => [
                    'super_admin', 'admin',
                ],
            ],

            // Settings & Profile
            'Settings & Profile' => [
                'settings.view' => [
                    'admin', 'user',
                ],
                'settings.appearance' => [
                    'admin', 'user',
                ],
                'settings.profile' => [
                    'admin', 'user',
                ],
                'settings.password' => [
                    'admin', 'user',
                ],
            ],

            // Property Management
            'Property Management' => [
                'properties.view-all' => ['super_admin', 'admin'],
                'properties.view-own' => ['agent'],
                'properties.view-published' => ['super_admin', 'admin', 'agent', 'user'],
                'properties.create' => ['super_admin', 'admin', 'agent'],
                'properties.update' => ['super_admin', 'admin'],
                'properties.update-status' => ['super_admin', 'admin'],
                'properties.delete' => ['super_admin', 'admin'],
                'properties.publish' => ['super_admin', 'admin'],
                'properties.feature' => ['super_admin', 'admin'],
            ],

            // Property Types & Features
            'Property Configuration' => [
                'property-types.view' => ['super_admin', 'admin', 'agent'],
                'property-types.create' => ['super_admin', 'admin'],
                'property-types.update' => ['super_admin', 'admin'],
                'property-types.delete' => ['super_admin', 'admin'],
                'features.view' => ['super_admin', 'admin', 'agent'],
                'features.create' => ['super_admin', 'admin'],
                'features.update' => ['super_admin', 'admin'],
                'features.delete' => ['super_admin', 'admin'],
                'amenities.view' => ['super_admin', 'admin', 'agent'],
                'amenities.create' => ['super_admin', 'admin'],
                'amenities.update' => ['super_admin', 'admin'],
                'amenities.delete' => ['super_admin', 'admin'],
            ],

            // Amenity Management
            'Amenity Management' => [
                'amenities.view' => ['super_admin', 'admin', 'agent'],
                'amenities.create' => ['super_admin', 'admin'],
                'amenities.update' => ['super_admin', 'admin'],
                'amenities.delete' => ['super_admin', 'admin'],
                'amenities.publish' => ['super_admin', 'admin'],
            ],

            // User Management
            'User Management' => [
                'users.view' => ['super_admin', 'admin'],
                'users.create' => ['super_admin', 'admin'],
                'users.update' => ['super_admin', 'admin'],
                'users.delete' => ['super_admin'],
                'users.assign-roles' => ['super_admin'],
            ],

            // Agent Management
            'Agent Management' => [
                'agents.view' => ['super_admin', 'admin'],
                'agents.create' => ['super_admin', 'admin'],
                'agents.update' => ['super_admin', 'admin'],
                'agents.delete' => ['super_admin'],
                'agents.assign-properties' => ['super_admin', 'admin'],
            ],

            // Property Inquiries
            'Inquiry Management' => [
                'inquiries.view-all' => ['super_admin', 'admin'],
                'inquiries.view-assigned' => ['agent'],
                'inquiries.create' => ['super_admin', 'admin', 'agent', 'user'],
                'inquiries.respond' => ['super_admin', 'admin', 'agent'],
                'inquiries.assign' => ['super_admin', 'admin'],
                'inquiries.update-status' => ['super_admin', 'admin', 'agent'],
                'inquiries.delete' => ['super_admin', 'admin'],
                'inquiries.export' => ['super_admin', 'admin'],
            ],

            // Property Favorites
            'Favorites Management' => [
                'favorites.view-own' => ['user', 'agent', 'admin', 'super_admin'],
                'favorites.add' => ['user', 'agent', 'admin', 'super_admin'],
                'favorites.remove' => ['user', 'agent', 'admin', 'super_admin'],
            ],

            // Property Tours
            'Property Tours Management' => [
                'property-tours.view-all' => ['super_admin', 'admin'],
                'property-tours.view-own' => ['agent', 'user'],
                'property-tours.create' => ['super_admin', 'admin', 'agent', 'user'],
                'property-tours.schedule' => ['super_admin', 'admin', 'agent', 'user'],
                'property-tours.update' => ['super_admin', 'admin', 'agent'],
                'property-tours.update-own' => ['user'],
                'property-tours.cancel' => ['super_admin', 'admin', 'agent', 'user'],
                'property-tours.delete' => ['super_admin', 'admin'],
            ],

            // Saved Searches
            'Saved Searches' => [
                'saved-searches.view-own' => ['user', 'agent'],
                'saved-searches.create' => ['user', 'agent'],
                'saved-searches.update' => ['user', 'agent'],
                'saved-searches.delete' => ['user', 'agent'],
                'saved-searches.view-all' => ['super_admin', 'admin'],
                'saved-searches.notifications' => ['user', 'agent'],
            ],

            // Blog Management
            'Blog Management' => [
                'blog.view-all' => ['super_admin', 'admin'],
                'blog.view-own' => ['agent'],
                'blog.view-published' => ['super_admin', 'admin', 'agent', 'user'],
                'blog.create' => ['super_admin', 'admin', 'agent'],
                'blog.update' => ['super_admin', 'admin'],
                'blog.update-own' => ['agent'],
                'blog.delete' => ['super_admin', 'admin'],
                'blog.publish' => ['super_admin', 'admin'],
                'blog.unpublish' => ['super_admin', 'admin'],
                'blog.feature' => ['super_admin', 'admin'],
                'blog.manage-categories' => ['super_admin', 'admin'],
                'blog.manage-tags' => ['super_admin', 'admin'],
            ],

            // Blog Comments Management
            'Blog Comments Management' => [
                'blog-comments.view-all' => ['super_admin', 'admin'],
                'blog-comments.view-own' => ['agent', 'user'],
                'blog-comments.create' => ['super_admin', 'admin', 'agent', 'user'],
                'blog-comments.update-own' => ['agent', 'user'],
                'blog-comments.delete-own' => ['agent', 'user'],
                'blog-comments.delete' => ['super_admin', 'admin'],
                'blog-comments.approve' => ['super_admin', 'admin'],
                'blog-comments.approve-own' => ['agent'],
                'blog-comments.reject' => ['super_admin', 'admin'],
                'blog-comments.moderate' => ['super_admin', 'admin'],
            ],

            // Blog Edit Requests Management
            'Blog Edit Requests Management' => [
                'blog-edit-requests.view-all' => ['super_admin', 'admin'],
                'blog-edit-requests.view-own' => ['agent'],
                'blog-edit-requests.create' => ['agent'],
                'blog-edit-requests.update-own' => ['agent'],
                'blog-edit-requests.delete-own' => ['agent'],
                'blog-edit-requests.approve' => ['super_admin', 'admin'],
                'blog-edit-requests.reject' => ['super_admin', 'admin'],
                'blog-edit-requests.review' => ['super_admin', 'admin'],
            ],

            // Contact Inquiries Management
            'Contact Inquiries Management' => [
                'contact-inquiries.view-all' => ['super_admin', 'admin'],
                'contact-inquiries.view' => ['super_admin', 'admin'],
                'contact-inquiries.update' => ['super_admin', 'admin'],
                'contact-inquiries.delete' => ['super_admin', 'admin'],
            ],

            // Review Management
            'Review Management' => [
                'reviews.view-all' => ['super_admin', 'admin'],
                'reviews.view-own' => ['user', 'agent'],
                'reviews.create' => ['super_admin', 'admin', 'agent', 'user'],
                'reviews.update' => ['super_admin', 'admin'],
                'reviews.approve' => ['super_admin', 'admin'],
                'reviews.delete' => ['super_admin', 'admin'],
            ],

            // Messaging System
            'Message Management' => [
                'messages.view' => ['super_admin', 'admin', 'agent', 'user'],
                'messages.reply' => ['super_admin', 'admin', 'agent', 'user'],
                'messages.mark-read' => ['super_admin', 'admin', 'agent', 'user'],
                'messages.mark-unread' => ['super_admin', 'admin', 'agent', 'user'],
                'messages.mark-all-read' => ['super_admin', 'admin', 'agent', 'user'],
                'messages.delete' => ['super_admin', 'admin'],
            ],
        ];
    }

    public function getRoles(): array
    {
        return [
            [
                'name' => 'super_admin',
                'display_name' => 'Super Administrator',
                'description' => 'System owner with complete access to all platform features and sensitive operations. Can manage system settings, user roles, and has unrestricted access.',
                'is_default' => true,
                'guard_name' => 'web',
            ],
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Platform administrator with operational control over content, users, and business functions. Cannot access sensitive system operations.',
                'is_default' => true,
                'guard_name' => 'web',
            ],
            [
                'name' => 'agent',
                'display_name' => 'Real Estate Agent',
                'description' => 'Licensed real estate agent who can manage their own properties, respond to inquiries, and interact with potential clients.',
                'is_default' => true,
                'guard_name' => 'web',
            ],
            [
                'name' => 'user',
                'display_name' => 'User',
                'description' => 'End user who can browse properties, save favorites, create inquiries, and manage their account preferences.',
                'is_default' => true,
                'guard_name' => 'web',
            ],
        ];
    }
}
