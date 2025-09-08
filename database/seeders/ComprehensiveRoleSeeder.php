<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ComprehensiveRoleSeeder extends Seeder
{
    /**
     * All system permissions mapped to roles that should have them
     */
    private array $permissions = [
        // === SYSTEM ADMINISTRATION ===
        'access admin panel' => ['Super Admin', 'Administrator', 'Editor', 'Author', 'Contributor', 'Moderator'],
        'view dashboard' => ['Super Admin', 'Administrator', 'Editor', 'Author', 'Contributor', 'Moderator'],
        'manage settings' => ['Super Admin'],
        'view system logs' => ['Super Admin'],
        'manage system' => ['Super Admin'],

        // === USER MANAGEMENT ===
        'view users' => ['Super Admin', 'Administrator'],
        'create users' => ['Super Admin', 'Administrator'],
        'edit users' => ['Super Admin', 'Administrator'],
        'delete users' => ['Super Admin', 'Administrator'],
        'assign roles' => ['Super Admin', 'Administrator'],
        'manage user permissions' => ['Super Admin', 'Administrator'],
        'view user profiles' => ['Super Admin', 'Administrator'],
        'impersonate users' => ['Super Admin', 'Administrator'],

        // === TEAM MANAGEMENT ===
        'view teams' => ['Super Admin', 'Administrator', 'User'],
        'create teams' => ['Super Admin', 'Administrator', 'User'],
        'edit teams' => ['Super Admin', 'Administrator', 'User'],
        'delete teams' => ['Super Admin', 'Administrator'],
        'manage team members' => ['Super Admin', 'Administrator'],
        'invite team members' => ['Super Admin', 'Administrator'],
        'remove team members' => ['Super Admin', 'Administrator'],
        'view team analytics' => ['Super Admin', 'Administrator'],

        // === BLOG - POSTS MANAGEMENT ===
        'view posts' => ['Super Admin', 'Administrator', 'Editor', 'Author', 'Moderator'],
        'create posts' => ['Super Admin', 'Administrator', 'Editor', 'Author', 'Contributor'],
        'edit posts' => ['Super Admin', 'Administrator', 'Editor'],
        'delete posts' => ['Super Admin', 'Administrator', 'Editor'],
        'publish posts' => ['Super Admin', 'Administrator', 'Editor'],
        'unpublish posts' => ['Super Admin', 'Administrator', 'Editor'],
        'schedule posts' => ['Super Admin', 'Administrator', 'Editor'],
        'view own posts' => ['Super Admin', 'Administrator', 'Editor', 'Author', 'Contributor'],
        'edit own posts' => ['Super Admin', 'Administrator', 'Editor', 'Author', 'Contributor'],
        'delete own posts' => ['Super Admin', 'Administrator', 'Editor', 'Author'],
        'duplicate posts' => ['Super Admin', 'Administrator', 'Editor'],
        'bulk edit posts' => ['Super Admin', 'Administrator', 'Editor'],
        'feature posts' => ['Super Admin', 'Administrator', 'Editor'],

        // === BLOG - CATEGORIES MANAGEMENT ===
        'view categories' => ['Super Admin', 'Administrator', 'Editor', 'Author', 'Contributor', 'Moderator'],
        'create categories' => ['Super Admin', 'Administrator', 'Editor'],
        'edit categories' => ['Super Admin', 'Administrator', 'Editor'],
        'delete categories' => ['Super Admin', 'Administrator', 'Editor'],
        'manage category hierarchy' => ['Super Admin', 'Administrator', 'Editor'],
        'assign posts to categories' => ['Super Admin', 'Administrator', 'Editor'],

        // === BLOG - TAGS MANAGEMENT ===
        'view tags' => ['Super Admin', 'Administrator', 'Editor', 'Author', 'Contributor', 'Moderator'],
        'create tags' => ['Super Admin', 'Administrator', 'Editor', 'Author'],
        'edit tags' => ['Super Admin', 'Administrator', 'Editor'],
        'delete tags' => ['Super Admin', 'Administrator', 'Editor'],
        'merge tags' => ['Super Admin', 'Administrator', 'Editor'],
        'bulk manage tags' => ['Super Admin', 'Administrator', 'Editor'],

        // === MEDIA LIBRARY ===
        'view media' => ['Super Admin', 'Administrator', 'Editor', 'Author', 'Contributor', 'Moderator'],
        'upload media' => ['Super Admin', 'Administrator', 'Editor', 'Author', 'Contributor'],
        'edit media' => ['Super Admin', 'Administrator', 'Editor'],
        'delete media' => ['Super Admin', 'Administrator', 'Editor'],
        'view own media' => ['Super Admin', 'Administrator', 'Editor', 'Author', 'Contributor'],
        'edit own media' => ['Super Admin', 'Administrator', 'Editor', 'Author', 'Contributor'],
        'delete own media' => ['Super Admin', 'Administrator', 'Editor', 'Author'],
        'organize media' => ['Super Admin', 'Administrator', 'Editor'],
        'bulk edit media' => ['Super Admin', 'Administrator', 'Editor'],
        'manage media folders' => ['Super Admin', 'Administrator', 'Editor'],

        // === COMMENTS SYSTEM ===
        'view comments' => ['Super Admin', 'Administrator', 'Editor', 'Author', 'Moderator'],
        'moderate comments' => ['Super Admin', 'Administrator', 'Editor', 'Moderator'],
        'approve comments' => ['Super Admin', 'Administrator', 'Editor', 'Moderator'],
        'reject comments' => ['Super Admin', 'Administrator', 'Editor', 'Moderator'],
        'delete comments' => ['Super Admin', 'Administrator', 'Editor', 'Moderator'],
        'reply to comments' => ['Super Admin', 'Administrator', 'Editor', 'Author', 'Moderator'],
        'mark as spam' => ['Super Admin', 'Administrator', 'Editor', 'Moderator'],
        'bulk moderate comments' => ['Super Admin', 'Administrator', 'Editor', 'Moderator'],
        'view comment analytics' => ['Super Admin', 'Administrator', 'Editor', 'Moderator'],

        // === SEO MANAGEMENT ===
        'view seo settings' => ['Super Admin', 'Administrator', 'Editor'],
        'edit seo settings' => ['Super Admin', 'Administrator', 'Editor'],
        'manage meta tags' => ['Super Admin', 'Administrator', 'Editor'],
        'manage sitemaps' => ['Super Admin', 'Administrator', 'Editor'],
        'view seo analytics' => ['Super Admin', 'Administrator', 'Editor'],
        'optimize content' => ['Super Admin', 'Administrator', 'Editor'],

        // === ANALYTICS & REPORTING ===
        'view analytics' => ['Super Admin', 'Administrator', 'Editor'],
        'view reports' => ['Super Admin', 'Administrator', 'Editor'],
        'export data' => ['Super Admin', 'Administrator', 'Editor'],
        'view traffic analytics' => ['Super Admin', 'Administrator', 'Editor'],
        'view content performance' => ['Super Admin', 'Administrator', 'Editor'],
        'view user behavior' => ['Super Admin', 'Administrator', 'Editor'],

        // === NOTIFICATIONS ===
        'view notifications' => ['Super Admin', 'Administrator', 'Editor'],
        'send notifications' => ['Super Admin', 'Administrator', 'Editor'],
        'manage notification settings' => ['Super Admin', 'Administrator', 'Editor'],
        'bulk notifications' => ['Super Admin', 'Administrator', 'Editor'],
    ];

    /**
     * Role definitions with their descriptions
     */
    private array $roles = [
        'Super Admin' => [
            'description' => 'Complete system access with all permissions',
            'guard_name' => 'web',
        ],

        'Administrator' => [
            'description' => 'Full admin access except system-level settings',
            'guard_name' => 'web',
        ],

        'Editor' => [
            'description' => 'Content management and moderation',
            'guard_name' => 'web',
        ],

        'Author' => [
            'description' => 'Create and manage own content',
            'guard_name' => 'web',
        ],

        'Contributor' => [
            'description' => 'Create draft content for review',
            'guard_name' => 'web',
        ],

        'Moderator' => [
            'description' => 'Content moderation and comment management',
            'guard_name' => 'web',
        ],

        'User' => [
            'description' => 'Basic authenticated user',
            'guard_name' => 'web',
        ],
    ];

    /**
     * Demo users for each major role
     */
    private array $demoUsers = [
        [
            'name' => 'Super Administrator',
            'email' => 'superadmin@example.com',
            'password' => 'password',
            'role' => 'Super Admin',
            'email_verified' => true,
        ],
        [
            'name' => 'System Administrator',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => 'Administrator',
            'email_verified' => true,
        ],
        [
            'name' => 'Content Editor',
            'email' => 'editor@example.com',
            'password' => 'password',
            'role' => 'Editor',
            'email_verified' => true,
        ],
        [
            'name' => 'Blog Author',
            'email' => 'author@example.com',
            'password' => 'password',
            'role' => 'Author',
            'email_verified' => true,
        ],
        [
            'name' => 'Content Contributor',
            'email' => 'contributor@example.com',
            'password' => 'password',
            'role' => 'Contributor',
            'email_verified' => true,
        ],
        [
            'name' => 'Comment Moderator',
            'email' => 'moderator@example.com',
            'password' => 'password',
            'role' => 'Moderator',
            'email_verified' => true,
        ],
        [
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => 'password',
            'role' => 'User',
            'email_verified' => true,
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting Comprehensive Role & Permission Seeding...');

        // Clear permission cache before starting
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Step 1: Create all permissions
        $this->createPermissions();

        // Step 2: Create all roles with their permissions
        $this->createRoles();

        // Step 3: Create demo users for each role
        $this->createDemoUsers();

        $this->command->info('✅ Comprehensive Role & Permission seeding completed successfully!');
        $this->displaySummary();
    }

    /**
     * Create all permissions from the permissions array
     */
    private function createPermissions(): void
    {
        $this->command->line('📝 Creating permissions...');

        $createdCount = 0;
        $totalPermissions = count($this->permissions);

        foreach ($this->permissions as $permissionName => $roles) {
            try {
                $permission = Permission::firstOrCreate([
                    'name' => $permissionName,
                    'guard_name' => 'web',
                ]);

                if ($permission->wasRecentlyCreated) {
                    $createdCount++;
                    $this->command->line("     Created: {$permissionName}");
                } else {
                    $this->command->line("     Exists: {$permissionName}");
                }
            } catch (\Exception $e) {
                $this->command->warn("     Failed: {$permissionName} - " . $e->getMessage());
            }
        }

        // Clear permission cache after creating
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $actualCount = Permission::count();
        $this->command->info("   ✓ Permissions in database: {$actualCount} (Created: {$createdCount} new, Total defined: {$totalPermissions})");
    }

    /**
     * Create all roles and assign their permissions
     */
    private function createRoles(): void
    {
        $this->command->line('👥 Creating roles...');

        foreach ($this->roles as $roleName => $roleConfig) {
            $this->command->line("   Creating {$roleName} role...");

            $role = Role::firstOrCreate([
                'name' => $roleName,
            ], [
                'name' => $roleName,
                'guard_name' => $roleConfig['guard_name'],
            ]);

            // Get permissions for this role
            $rolePermissions = $this->getPermissionsForRole($roleName);

            if (! empty($rolePermissions)) {
                // Verify permissions exist before syncing
                $existingPermissions = Permission::whereIn('name', $rolePermissions)->pluck('name')->toArray();
                $missingPermissions = array_diff($rolePermissions, $existingPermissions);
                
                if (!empty($missingPermissions)) {
                    $this->command->warn("     ⚠ Missing permissions for {$roleName}: " . implode(', ', $missingPermissions));
                }
                
                if (!empty($existingPermissions)) {
                    $role->syncPermissions($existingPermissions);
                    $this->command->line('     ✓ Assigned ' . count($existingPermissions) . " permissions to {$roleName}");
                }
            } else {
                $this->command->line("     ℹ No permissions assigned to {$roleName}");
            }
        }

        // Clear permission cache after role creation
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('   ✓ Created ' . count($this->roles) . ' roles');
    }

    /**
     * Get permissions for a specific role based on the permissions mapping
     */
    private function getPermissionsForRole(string $roleName): array
    {
        $rolePermissions = [];

        // Go through all permissions and find ones that include this role
        foreach ($this->permissions as $permissionName => $roles) {
            if (in_array($roleName, $roles)) {
                $rolePermissions[] = $permissionName;
            }
        }

        return $rolePermissions;
    }

    /**
     * Create demo users for each role
     */
    private function createDemoUsers(): void
    {
        $this->command->line('👤 Creating demo users...');

        foreach ($this->demoUsers as $userData) {
            $this->command->line("   Creating {$userData['name']} ({$userData['email']})...");

            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make($userData['password']),
                    'email_verified_at' => $userData['email_verified'] ? now() : null,
                ]
            );

            // Assign role to user
            $role = Role::where('name', $userData['role'])->first();
            if ($role) {
                $user->syncRoles([$userData['role']]);
                $this->command->line("     ✓ Assigned {$userData['role']} role to {$userData['name']}");
            } else {
                $this->command->warn("     ⚠ Role {$userData['role']} not found for {$userData['name']}");
            }
        }

        $this->command->info('   ✓ Created ' . count($this->demoUsers) . ' demo users');
    }

    /**
     * Display a summary of what was created
     */
    private function displaySummary(): void
    {
        $this->command->info('');
        $this->command->info('📊 SEEDING SUMMARY');
        $this->command->info('==================');

        // Count total permissions
        $totalPermissions = count($this->permissions);
        $this->command->info("Permissions: {$totalPermissions} total permissions");
        $this->command->info('Roles: ' . count($this->roles));
        $this->command->info('Demo Users: ' . count($this->demoUsers));

        $this->command->info('');
        $this->command->info('🔑 DEMO USER CREDENTIALS');
        $this->command->info('========================');

        foreach ($this->demoUsers as $user) {
            $this->command->info("📧 {$user['email']} | 🔒 {$user['password']} | 👤 {$user['role']}");
        }

        $this->command->info('');
        $this->command->info('💡 TIP: Use these credentials to test different permission levels');
    }
}
