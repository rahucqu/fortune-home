<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class BlogRolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Blog-specific permissions
        $blogPermissions = [
            // Posts management
            'view posts',
            'create posts',
            'edit posts',
            'delete posts',
            'publish posts',
            'view own posts',
            'edit own posts',
            'delete own posts',

            // Categories management
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',

            // Tags management
            'view tags',
            'create tags',
            'edit tags',
            'delete tags',

            // Media management
            'view media',
            'upload media',
            'edit media',
            'delete media',
            'view own media',
            'edit own media',
            'delete own media',

            // Comments management
            'view comments',
            'moderate comments',
            'approve comments',
            'delete comments',
            'reply to comments',

            // SEO management
            'view seo settings',
            'edit seo settings',

            // Analytics
            'view analytics',
            'view dashboard',

            // General admin
            'access admin panel',
            'manage settings',
        ];

        // Create permissions
        foreach ($blogPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create blog-specific roles
        $this->createAdminRole();
        $this->createEditorRole();
        $this->createAuthorRole();
        $this->createContributorRole();

        $this->command->info('Blog roles and permissions created successfully!');
    }

    /**
     * Create Super Admin role with all permissions
     */
    private function createAdminRole(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'super-admin']);

        // Give all permissions to super admin
        $adminRole->givePermissionTo(Permission::all());

        $this->command->line('Super Admin role created with all permissions');
    }

    /**
     * Create Editor role - can manage content but not system settings
     */
    private function createEditorRole(): void
    {
        $editorRole = Role::firstOrCreate(['name' => 'editor']);

        $editorPermissions = [
            // Full content management
            'view posts',
            'create posts',
            'edit posts',
            'delete posts',
            'publish posts',

            // Categories and tags
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',
            'view tags',
            'create tags',
            'edit tags',
            'delete tags',

            // Media management
            'view media',
            'upload media',
            'edit media',
            'delete media',

            // Comments moderation
            'view comments',
            'moderate comments',
            'approve comments',
            'delete comments',
            'reply to comments',

            // Limited admin access
            'access admin panel',
            'view dashboard',
            'view analytics',
        ];

        $editorRole->givePermissionTo($editorPermissions);

        $this->command->line('Editor role created with content management permissions');
    }

    /**
     * Create Author role - can manage own posts and limited content
     */
    private function createAuthorRole(): void
    {
        $authorRole = Role::firstOrCreate(['name' => 'author']);

        $authorPermissions = [
            // Own posts management
            'view posts',
            'create posts',
            'view own posts',
            'edit own posts',
            'delete own posts',

            // Limited content access
            'view categories',
            'view tags',
            'create tags', // Authors can create tags for their posts

            // Own media management
            'view media',
            'upload media',
            'view own media',
            'edit own media',
            'delete own media',

            // Comments
            'view comments',
            'reply to comments',

            // Basic admin access
            'access admin panel',
            'view dashboard',
        ];

        $authorRole->givePermissionTo($authorPermissions);

        $this->command->line('Author role created with limited content permissions');
    }

    /**
     * Create Contributor role - can create posts but cannot publish
     */
    private function createContributorRole(): void
    {
        $contributorRole = Role::firstOrCreate(['name' => 'contributor']);

        $contributorPermissions = [
            // Basic post creation (drafts only)
            'view own posts',
            'create posts',
            'edit own posts',

            // Read-only access to content structure
            'view categories',
            'view tags',

            // Own media only
            'view own media',
            'upload media',
            'edit own media',

            // Basic admin access
            'access admin panel',
            'view dashboard',
        ];

        $contributorRole->givePermissionTo($contributorPermissions);

        $this->command->line('Contributor role created with basic permissions');
    }
}
