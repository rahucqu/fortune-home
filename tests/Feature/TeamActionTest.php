<?php

declare(strict_types=1);

use App\Actions\Teams\CreateTeam;
use App\Models\User;

it('can create a team using the action', function () {
    $user = User::factory()->create();
    $action = new CreateTeam();

    $team = $action->create($user, [
        'name' => 'Test Action Team',
    ]);

    expect($team)->not->toBeNull();
    expect($team->name)->toBe('Test Action Team');
    expect($team->user_id)->toBe($user->id);
    expect($team->personal_team)->toBeFalse();
    expect($user->fresh()->current_team_id)->toBe($team->id);
});
