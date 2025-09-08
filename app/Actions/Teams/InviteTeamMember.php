<?php

declare(strict_types=1);

namespace App\Actions\Teams;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class InviteTeamMember
{
    /**
     * Invite a new team member to the given team.
     */
    public function invite(User $user, Team $team, string $email, ?string $role = null): void
    {
        Gate::forUser($user)->authorize('addTeamMember', $team);

        $this->validate($team, $email, $role);

        // Create the invitation
        $team->teamInvitations()->create([
            'email' => $email,
            'role' => $role ?? 'member',
        ]);
    }

    /**
     * Validate the invite member operation.
     */
    protected function validate(Team $team, string $email, ?string $role): void
    {
        Validator::make([
            'email' => $email,
            'role' => $role,
        ], [
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'in:member,admin'],
        ], [
            'email.required' => 'The email address is required.',
            'email.email' => 'The email address must be valid.',
        ])->after(function ($validator) use ($team, $email) {
            // Check if user is already a team member
            $existingUser = User::where('email', $email)->first();
            if ($existingUser && $existingUser->belongsToTeam($team)) {
                $validator->errors()->add('email', 'This user is already a member of the team.');
            }

            // Check if invitation already exists
            $existingInvitation = $team->teamInvitations()
                ->where('email', $email)
                ->where('created_at', '>', now()->subDays(7))
                ->first();

            if ($existingInvitation) {
                $validator->errors()->add('email', 'An invitation has already been sent to this email address.');
            }
        })->validateWithBag('addTeamMember');
    }
}
