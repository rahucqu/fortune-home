<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ShowRolesPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roles:show {--users : Show users for each role} {--permissions : Show permissions for each role}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display a summary of roles, permissions, and users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->showHeader();
        $this->showSystemSummary();
        $this->showRolesSummary();

        if ($this->option('permissions')) {
            $this->showRolePermissions();
        }

        if ($this->option('users')) {
            $this->showUsersWithRoles();
        }

        $this->showFooter();
    }

    private function showHeader()
    {
        $this->info('');
        $this->info('🔐 ROLES & PERMISSIONS SYSTEM');
        $this->info('====================================');
    }

    private function showSystemSummary()
    {
        $totalRoles = Role::count();
        $totalPermissions = Permission::count();
        $totalUsers = User::count();
        $usersWithRoles = User::whereHas('roles')->count();

        $this->info('📊 System Overview:');
        $this->line("   Roles: {$totalRoles}");
        $this->line("   Permissions: {$totalPermissions}");
        $this->line("   Total Users: {$totalUsers}");
        $this->line("   Users with Roles: {$usersWithRoles}");
        $this->info('');
    }

    private function showRolesSummary()
    {
        $this->info('👥 Roles Summary:');

        $headers = ['Role', 'Permissions', 'Users', 'Description'];
        $rows = [];

        Role::all()->each(function ($role) use (&$rows) {
            $permissionCount = $role->getAllPermissions()->count();
            $userCount = $role->users()->count();
            $description = $this->getRoleDescription($role->name);

            $rows[] = [
                $role->name,
                $permissionCount,
                $userCount,
                $description,
            ];
        });

        $this->table($headers, $rows);
        $this->info('');
    }

    private function showRolePermissions()
    {
        $this->info('🔑 Role Permissions Breakdown:');

        Role::all()->each(function ($role) {
            $permissions = $role->getAllPermissions();
            $this->warn("   {$role->name} ({$permissions->count()} permissions):");

            $groupedPermissions = $permissions->groupBy(function ($permission) {
                // Group by first word of permission (e.g., 'view posts' -> 'view')
                return explode(' ', $permission->name)[0];
            });

            $groupedPermissions->each(function ($perms, $group) {
                $permNames = $perms->pluck('name')->map(function ($name) use ($group) {
                    return str_replace($group . ' ', '', $name);
                })->join(', ');
                $this->line("     {$group}: {$permNames}");
            });

            $this->info('');
        });
    }

    private function showUsersWithRoles()
    {
        $this->info('👤 Users & Their Roles:');

        $headers = ['Name', 'Email', 'Roles', 'Permissions'];
        $rows = [];

        User::with('roles')->get()->each(function ($user) use (&$rows) {
            $roles = $user->roles->pluck('name')->join(', ') ?: 'No roles';
            $permissionCount = $user->getAllPermissions()->count();

            $rows[] = [
                $user->name,
                $user->email,
                $roles,
                $permissionCount,
            ];
        });

        $this->table($headers, $rows);
        $this->info('');
    }

    private function showFooter()
    {
        $this->info('💡 Usage Tips:');
        $this->line('   • Use --permissions to see detailed permission breakdown');
        $this->line('   • Use --users to see user assignments');
        $this->line('   • Login credentials: password for all demo users');
        $this->info('');
    }

    private function getRoleDescription($roleName): string
    {
        $descriptions = [
            'Super Admin' => 'Complete system access',
            'Administrator' => 'Full admin except system',
            'Editor' => 'Content management',
            'Author' => 'Own content creation',
            'Contributor' => 'Draft content only',
            'Moderator' => 'Comment moderation',
            'User' => 'Basic user access',
        ];

        return $descriptions[$roleName] ?? 'Custom role';
    }
}
