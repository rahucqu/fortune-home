<?php

declare(strict_types=1);

namespace App\Actions\Teams;

use App\Models\Team;

class DeleteTeam
{
    /**
     * Delete the given team.
     */
    public function delete(Team $team): void
    {
        $team->purge();
    }
}
