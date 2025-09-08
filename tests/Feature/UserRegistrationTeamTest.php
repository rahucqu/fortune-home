<?php

declare(strict_types=1);

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('automatically creates a personal team when user registers', function () {
    // Arrange
    $userData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ];

    // Act - Simulate user registration
    $response = $this->post('/register', $userData);

    // Assert
    $response->assertRedirect('/dashboard');

    $user = User::where('email', 'john@example.com')->first();
    expect($user)->not->toBeNull();

    // Check that a personal team was created
    expect($user->ownedTeams)->toHaveCount(1);

    $personalTeam = $user->ownedTeams->first();
    expect($personalTeam->name)->toBe("John's Team");
    expect($personalTeam->personal_team)->toBeTrue();

    // Check that the user's current team is set to the personal team
    expect($user->current_team_id)->toBe($personalTeam->id);
    expect($user->currentTeam->id)->toBe($personalTeam->id);
});

it('creates personal team when user is created programmatically', function () {
    // Act
    $user = User::create([
        'name' => 'Jane Smith',
        'email' => 'jane@example.com',
        'password' => bcrypt('password'),
    ]);

    // Assert
    expect($user->ownedTeams)->toHaveCount(1);

    $personalTeam = $user->ownedTeams->first();
    expect($personalTeam->name)->toBe("Jane's Team");
    expect($personalTeam->personal_team)->toBeTrue();
    expect($user->current_team_id)->toBe($personalTeam->id);
});

it('handles users with single names correctly', function () {
    // Act
    $user = User::create([
        'name' => 'Madonna',
        'email' => 'madonna@example.com',
        'password' => bcrypt('password'),
    ]);

    // Assert
    $personalTeam = $user->ownedTeams->first();
    expect($personalTeam->name)->toBe("Madonna's Team");
});
