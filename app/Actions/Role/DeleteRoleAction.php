<?php

declare(strict_types=1);

namespace App\Actions\Role;

use App\Models\Role;
use Exception;
use Illuminate\Support\Facades\DB;

class DeleteRoleAction
{
    public function execute(Role $role): bool
    {
        // Prevent deletion of default roles
        if ($role->is_default) {
            throw new Exception('Cannot delete default role');
        }

        // Prevent deletion of roles that have users assigned
        $userCount = $role->users()->count();
        if ($userCount > 0) {
            throw new Exception("Cannot delete role '{$role->display_name}' because it is assigned to {$userCount} user(s). Please remove the role from all users before deleting.");
        }

        return DB::transaction(function () use ($role) {
            // Remove all permissions from the role
            $role->syncPermissions([]);

            // Delete the role
            return $role->delete();
        });
    }
}
