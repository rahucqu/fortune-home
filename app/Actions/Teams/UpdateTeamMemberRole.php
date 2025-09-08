<?php

declare(strict_types=1);

namespace App\Actions\Teams;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class UpdateTeamMemberRole
{
    /**
     * Update the role for the given team member.
     */
    public function update(User $user, Team $team, int $teamMemberId, string $role): void
    {
        Gate::forUser($user)->authorize('updateTeamMember', $team);

        Validator::make(['role' => $role], [
            'role' => ['required', 'string', 'in:member,admin'],
        ])->validateWithBag('updateTeamMember');

        $team->users()->updateExistingPivot($teamMemberId, [
            'role' => $role,
        ]);
    }
}
