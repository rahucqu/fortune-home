<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DeleteUserAction
{
    public function execute(User $user): bool
    {
        // Prevent deletion of the current user
        if (Auth::id() === $user->id) {
            throw new Exception('Cannot delete your own account');
        }

        return DB::transaction(function () use ($user) {
            // Remove all roles from the user
            $user->syncRoles([]);

            // Delete the user
            return $user->delete();
        });
    }
}
