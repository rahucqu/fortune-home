<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PropertyPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create property management permissions
        $permissions = [
            // Properties
            'view properties',
            'create properties',
            'edit properties',
            'delete properties',
            'publish properties',
            'manage property images',

            // Property Types
            'view property types',
            'create property types',
            'edit property types',
            'delete property types',

            // Locations
            'view locations',
            'create locations',
            'edit locations',
            'delete locations',

            // Agents
            'view agents',
            'create agents',
            'edit agents',
            'delete agents',

            // Amenities
            'view amenities',
            'create amenities',
            'edit amenities',
            'delete amenities',

            // Inquiries
            'view inquiries',
            'respond to inquiries',
            'delete inquiries',

            // Reports and Analytics
            'view property reports',
            'view analytics dashboard',

            // System Settings
            'manage property settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create Agent role if it doesn't exist
        $agentRole = Role::firstOrCreate(['name' => 'Agent']);

        // Assign agent permissions
        $agentPermissions = [
            'view properties',
            'create properties',
            'edit properties',
            'manage property images',
            'view inquiries',
            'respond to inquiries',
            'view property reports',
        ];

        $agentRole->syncPermissions($agentPermissions);

        // Get existing admin roles and assign all property permissions
        $adminRoles = ['Super Admin', 'Administrator'];

        foreach ($adminRoles as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $role->syncPermissions(Permission::all());
            }
        }

        $this->command->info('Property permissions created and assigned successfully!');
    }
}
