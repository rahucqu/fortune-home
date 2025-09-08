<?php

declare(strict_types=1);

use App\Models\Team;
use App\Models\User;

it('can create a team', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/teams', [
        'name' => 'Test Team',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('teams', [
        'name' => 'Test Team',
        'user_id' => $user->id,
        'personal_team' => false,
    ]);
});

it('can view team create page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/teams/create');

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('teams/Create')
        );
});

it('can view team members', function () {
    $user = User::factory()->create();
    $user->switchTeam($user->personalTeam());

    $response = $this->actingAs($user)->get('/teams/members');

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('teams/members')
            ->has('members')
            ->has('current_team')
        );
});

it('can view team invitations', function () {
    $user = User::factory()->create();
    $user->switchTeam($user->personalTeam());

    $response = $this->actingAs($user)->get('/teams/invitations');

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('teams/invitations')
            ->has('invitations')
            ->has('current_team')
        );
});

it('can switch teams', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)->post("/teams/{$team->id}/switch");

    $response->assertRedirect();
    $this->assertEquals($team->id, $user->fresh()->current_team_id);
});

it('prevents access to team members when user does not belong to current team', function () {
    $owner = User::factory()->create();
    $user = User::factory()->create();
    $team = Team::factory()->create(['user_id' => $owner->id]);

    // Owner sets their current team to the new team
    $owner->update(['current_team_id' => $team->id]);

    // User sets their current team to the owner's team (but they're not a member)
    $user->update(['current_team_id' => $team->id]);

    $response = $this->actingAs($user)->get('/teams/members');

    $response->assertRedirect(route('dashboard'));
});
