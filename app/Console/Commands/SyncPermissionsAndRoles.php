<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

use function Laravel\Prompts\multiselect;

class SyncPermissionsAndRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'acl:setup {--force : Force sync without confirmation in production} {--no-user : Skip user creation after sync}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup ACL: Sync permissions and roles, then optionally create admin users';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Safety check for production environment
        if (app()->environment('production') && ! $this->option('force')) {
            if (! $this->confirm('This will sync permissions and roles in PRODUCTION. Are you sure?')) {
                $this->info('Sync cancelled.');

                return Command::SUCCESS;
            }
        }

        $this->info('Starting permissions and roles sync...');

        $seeder = new RolePermissionSeeder();

        // Sync roles
        $this->syncRoles($seeder->getRoles());

        // Sync permissions and role assignments
        $this->syncPermissions($seeder->getGroupPermissions());

        // Clear permission cache
        $this->info('Clearing permission cache...');
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->info('✅ Permissions and roles sync completed successfully!');

        // Ask for user creation if --no-user flag is not set
        if (! $this->option('no-user')) {
            $this->handleUserCreation();
        }

        return Command::SUCCESS;
    }

    private function syncRoles(array $roles): void
    {
        $this->info('Syncing roles...');

        $roleNames = collect($roles)->pluck('name')->toArray();

        foreach ($roles as $roleData) {
            $role = Role::updateOrCreate(
                ['name' => $roleData['name']], // Find by name
                $roleData // Update with all data
            );

            if ($role->wasRecentlyCreated) {
                $this->line("✓ Created role: {$roleData['name']}");
            } else {
                $this->line("✓ Updated role: {$roleData['name']}");
            }
        }

        // Delete roles that are no longer defined (but keep non-default roles)
        $deletedRoles = Role::whereNotIn('name', $roleNames)
            ->where('is_default', true)
            ->get();

        foreach ($deletedRoles as $role) {
            $this->line("✓ Deleted role: {$role->name}");
            $role->delete();
        }
    }

    private function syncPermissions(array $groupPermissions): void
    {
        $this->info('Syncing permissions...');

        foreach ($groupPermissions as $group => $permissions) {
            $this->line("Processing group: {$group}");

            foreach ($permissions as $permissionName => $roleNames) {
                $permission = Permission::updateOrCreate(
                    [
                        'name' => $permissionName,
                        'guard_name' => 'web', // Ensure guard_name is set
                    ],
                    [
                        'group' => $group,
                        'name' => $permissionName,
                        'guard_name' => 'web',
                    ]
                );

                if ($permission->wasRecentlyCreated) {
                    $this->line("  ✓ Created permission: {$permissionName}");
                } else {
                    $this->line("  ✓ Updated permission: {$permissionName}");
                }

                // Sync roles (this will remove old assignments and add new ones)
                $permission->syncRoles($roleNames);
                $this->line("    ✓ Synced roles for permission: {$permissionName}");
            }
        }

        // Clean up orphaned permissions that are no longer defined
        $this->cleanupOrphanedPermissions($groupPermissions);
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
        $deletedPermissions = Permission::whereNotIn('name', $expectedPermissions)->get();

        foreach ($deletedPermissions as $permission) {
            $this->line("✓ Deleted orphaned permission: {$permission->name}");
            $permission->delete();
        }
    }

    private function handleUserCreation(): void
    {
        $this->newLine();
        $this->info('🎉 ACL setup completed successfully!');

        do {
            if ($this->confirm('Do you want to create a new admin user?', true)) {
                if ($this->createUser()) {
                    $this->info('✅ User created successfully!');
                } else {
                    $this->error('❌ Failed to create user. Please check the errors above.');
                }
            } else {
                $this->info('👋 Setup completed! Have a great day!');

                return;
            }
        } while ($this->confirm('Do you want to create another user?', false));

        $this->info('👋 All done! Have a great day!');
    }

    private function createUser(): bool
    {
        try {
            // Get user details
            $name = $this->ask('Enter user name');
            $email = $this->ask('Enter user email');
            $password = $this->secret('Enter user password');

            // Check if user already exists
            $existingUser = User::where('email', $email)->first();

            if ($existingUser) {
                if ($this->confirm("User with email '{$email}' already exists. Do you want to update their password and roles?", true)) {
                    return $this->updateExistingUser($existingUser, $name, $password);
                } else {
                    $this->info('Skipping user update.');

                    return false;
                }
            }

            // Validate input for new user
            $validator = Validator::make([
                'name' => $name,
                'email' => $email,
                'password' => $password,
            ], [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:8'],
            ]);

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $error) {
                    $this->error($error);
                }

                return false;
            }

            // Get selected roles
            $selectedRoleNames = $this->selectUserRoles();
            if ($selectedRoleNames === false) {
                return false;
            }

            // Create user
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'email_verified_at' => now(), // Mark as verified
            ]);

            // Assign roles
            if (! empty($selectedRoleNames)) {
                $user->assignRole($selectedRoleNames);
                $this->line('✓ Assigned roles: '.implode(', ', $selectedRoleNames));
            }

            $this->info("✅ User '{$name}' created successfully!");

            return true;
        } catch (Exception $e) {
            $this->error('Error creating user: '.$e->getMessage());

            return false;
        }
    }

    private function updateExistingUser(User $user, string $name, string $password): bool
    {
        try {
            // Validate input for existing user (no unique email constraint)
            $validator = Validator::make([
                'name' => $name,
                'password' => $password,
            ], [
                'name' => ['required', 'string', 'max:255'],
                'password' => ['required', 'string', 'min:8'],
            ]);

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $error) {
                    $this->error($error);
                }

                return false;
            }

            // Get selected roles
            $selectedRoleNames = $this->selectUserRoles();
            if ($selectedRoleNames === false) {
                return false;
            }

            // Update user details
            $user->update([
                'name' => $name,
                'password' => Hash::make($password),
            ]);

            // Sync roles (this will remove old roles and add new ones)
            $user->syncRoles($selectedRoleNames);

            $this->line('✓ Updated password');
            $this->line('✓ Synced roles: '.implode(', ', $selectedRoleNames));
            $this->info("✅ User '{$name}' updated successfully!");

            return true;
        } catch (Exception $e) {
            $this->error('Error updating user: '.$e->getMessage());

            return false;
        }
    }

    private function selectUserRoles(): array|false
    {
        // Get available roles
        $availableRoles = Role::orderBy('name')->get();
        if ($availableRoles->isEmpty()) {
            $this->error('No roles available. Please run the sync first.');

            return false;
        }

        // Select roles using interactive multiselect
        $roleOptions = $availableRoles->mapWithKeys(function ($role) {
            return [$role->name => sprintf('%s (%s)', $role->display_name, $role->description ?? 'No description')];
        })->toArray();

        $selectedRoleNames = multiselect(
            label: 'Select roles for the user:',
            options: $roleOptions,
            required: true,
            hint: 'Use arrow keys to navigate, space to select, enter to confirm'
        );

        if (empty($selectedRoleNames)) {
            $this->error('At least one role must be selected.');

            return false;
        }

        return $selectedRoleNames;
    }
}
