<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            // Admin panel access
            'access admin panel',
            'view dashboard',

            // User management
            'view users',
            'create users',
            'edit users',
            'delete users',
            'assign roles',

            // Team management
            'view teams',
            'create teams',
            'edit teams',
            'delete teams',
            'manage team members',

            // Team Invitations
            'view team invitations',
            'create team invitations',
            'edit team invitations',
            'delete team invitations',

            // Blog Management - Categories
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',

            // Blog Management - Tags
            'view tags',
            'create tags',
            'edit tags',
            'delete tags',

            // Blog Management - Media
            'view media',
            'upload media',
            'edit media',
            'delete media',

            // Blog Management - Posts
            'view posts',
            'create posts',
            'edit posts',
            'delete posts',
            'publish posts',

            // Blog Management - Comments
            'view comments',
            'create comments',
            'edit comments',
            'delete comments',

            // SEO Management
            'manage seo',

            // Property Management - Properties
            'view properties',
            'create properties',
            'edit properties',
            'delete properties',
            'manage property images',

            // Property Images
            'view property images',
            'create property images',
            'edit property images',
            'delete property images',

            // Property Management - Property Types
            'view property types',
            'create property types',
            'edit property types',
            'delete property types',

            // Property Management - Locations
            'view locations',
            'create locations',
            'edit locations',
            'delete locations',

            // Property Management - Agents
            'view agents',
            'create agents',
            'edit agents',
            'delete agents',

            // Property Management - Amenities
            'view amenities',
            'create amenities',
            'edit amenities',
            'delete amenities',

            // Property Management - Inquiries
            'view inquiries',
            'create inquiries',
            'edit inquiries',
            'delete inquiries',

            // Property Management - Favorites
            'view favorites',
            'create favorites',
            'edit favorites',
            'delete favorites',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $agentRole = Role::firstOrCreate(['name' => 'agent']);
        $moderatorRole = Role::firstOrCreate(['name' => 'moderator']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // Assign all permissions to admin
        $adminRole->givePermissionTo(Permission::all());

        // Assign agent permissions
        $agentRole->givePermissionTo([
            'access admin panel',
            'view dashboard',
            'view properties',
            'create properties',
            'edit properties',
            'view property types',
            'view locations',
            'view agents',
            'view amenities',
            'view property images',
            'create property images',
            'edit property images',
            'delete property images',
            'manage property images',
            'view inquiries',
            'edit inquiries',
            'view favorites',
            'view posts',
            'view categories',
            'view tags',
        ]);

        // Assign moderator permissions
        $moderatorRole->givePermissionTo([
            'access admin panel',
            'view dashboard',
            'view posts',
            'create posts',
            'edit posts',
            'publish posts',
            'view categories',
            'create categories',
            'edit categories',
            'view tags',
            'create tags',
            'edit tags',
            'view media',
            'upload media',
            'edit media',
            'view comments',
            'create comments',
            'edit comments',
            'delete comments',
            'view inquiries',
            'edit inquiries',
        ]);

        // Assign basic permissions to user role
        $userRole->givePermissionTo([
            'view teams',
            'create teams',
            'edit teams',
            'view properties',
            'view property types',
            'view locations',
            'view agents',
            'view amenities',
            'view posts',
            'view categories',
            'view tags',
            'create inquiries',
            'create favorites',
            'view favorites',
            'create comments',
        ]);
    }
}
