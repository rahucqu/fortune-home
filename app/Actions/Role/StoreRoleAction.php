<?php

declare(strict_types=1);

namespace App\Actions\Role;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class StoreRoleAction
{
    public function execute(array $data): Role
    {
        return DB::transaction(function () use ($data) {
            $role = Role::create([
                'name' => $data['name'],
                'display_name' => $data['display_name'],
                'description' => $data['description'] ?? null,
                'guard_name' => $data['guard_name'] ?? 'web',
                'is_default' => false,
            ]);

            if (isset($data['permissions']) && is_array($data['permissions']) && ! empty($data['permissions'])) {
                $permissions = Permission::whereIn('id', $data['permissions'])->get();
                $role->givePermissionTo($permissions);
            }

            return $role->load('permissions');
        });
    }
}
