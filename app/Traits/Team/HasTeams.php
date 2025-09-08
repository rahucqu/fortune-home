<?php

declare(strict_types=1);

namespace App\Traits\Team;

use App\Models\Team;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

trait HasTeams
{
    /**
     * Determine if the given team is the current team.
     */
    public function isCurrentTeam(Team $team): bool
    {
        return $team->id === $this->currentTeam?->id;
    }

    /**
     * Get the current team of the user's context.
     */
    public function currentTeam(): BelongsTo
    {
        if (is_null($this->current_team_id) && $this->id) {
            $this->switchTeam($this->personalTeam());
        }

        return $this->belongsTo(Team::class, 'current_team_id');
    }

    /**
     * Switch the user's context to the given team.
     */
    public function switchTeam(Team $team): bool
    {
        if (! $this->belongsToTeam($team)) {
            return false;
        }

        $this->forceFill([
            'current_team_id' => $team->id,
        ])->save();

        $this->setRelation('currentTeam', $team);

        return true;
    }

    /**
     * Get all of the teams the user owns or belongs to.
     *
     * @return Collection
     */
    public function allTeams()
    {
        return $this->ownedTeams->merge($this->teams)->sortBy('name');
    }

    /**
     * Get all of the teams the user owns.
     */
    public function ownedTeams(): HasMany
    {
        return $this->hasMany(Team::class, 'user_id');
    }

    /**
     * Get all of the teams the user belongs to.
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_user')
            ->withPivot('role')
            ->withTimestamps()
            ->as('membership');
    }

    /**
     * Get the user's "personal" team.
     */
    public function personalTeam(): ?Team
    {
        return $this->ownedTeams->where('personal_team', true)->first();
    }

    /**
     * Determine if the user owns the given team.
     */
    public function ownsTeam(Team $team): bool
    {
        return $this->id && $team->user_id && $this->id === $team->user_id;
    }

    /**
     * Determine if the user belongs to the given team.
     */
    public function belongsToTeam(Team $team): bool
    {
        return $this->ownsTeam($team) || $this->teams->contains(function ($t) use ($team) {
            return $t->id === $team->id;
        });
    }

    /**
     * Get the role that the user has on the team.
     */
    public function teamRole(Team $team): ?string
    {
        if ($this->ownsTeam($team)) {
            return 'owner';
        }

        $membership = $team->users->where('id', $this->id)->first();

        return $membership?->membership?->role;
    }

    /**
     * Determine if the user has the given role on the given team.
     */
    public function hasTeamRole(Team $team, string $role): bool
    {
        if ($this->ownsTeam($team)) {
            return $role === 'owner';
        }

        return $this->teamRole($team) === $role;
    }

    /**
     * Get the user's permissions for the given team.
     */
    public function teamPermissions(Team $team): array
    {
        if ($this->ownsTeam($team)) {
            return ['*'];
        }

        $role = $this->teamRole($team);

        return match ($role) {
            'admin' => [
                'read',
                'create',
                'update',
                'delete',
                'addTeamMember',
                'updateTeamMember',
                'removeTeamMember',
            ],
            'editor' => [
                'read',
                'create',
                'update',
            ],
            default => [
                'read',
            ],
        };
    }

    /**
     * Determine if the user has the given permission on the given team.
     */
    public function hasTeamPermission(Team $team, string $permission): bool
    {
        $permissions = $this->teamPermissions($team);

        return in_array('*', $permissions) || in_array($permission, $permissions);
    }

    /**
     * Create a personal team for the user.
     */
    public function createPersonalTeam(): Team
    {
        return $this->ownedTeams()->create([
            'name' => explode(' ', $this->name, 2)[0] . "'s Team",
            'personal_team' => true,
        ]);
    }
}
