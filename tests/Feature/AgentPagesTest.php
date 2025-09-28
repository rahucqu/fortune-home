<?php

declare(strict_types=1);

use App\Models\User;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\get;

it('displays agents on home page', function () {
    // Create some test users with different roles
    $agentRole = Role::firstOrCreate(['name' => 'agent'], ['display_name' => 'Agent']);
    $userRole = Role::firstOrCreate(['name' => 'user'], ['display_name' => 'User']);

    // Create agents (only users with 'agent' role should appear)
    $agent1 = User::factory()->create([
        'name' => 'John Agent',
        'email' => 'agent1@example.com',
        'is_active' => true,
    ]);
    $agent1->assignRole($agentRole);

    $agent2 = User::factory()->create([
        'name' => 'Jane Agent',
        'email' => 'agent2@example.com',
        'is_active' => true,
    ]);
    $agent2->assignRole($agentRole);

    // Create a regular user (should not appear as agent)
    $regularUser = User::factory()->create([
        'name' => 'Regular User',
        'email' => 'user@example.com',
        'is_active' => true,
    ]);
    $regularUser->assignRole($userRole);

    // Visit home page
    $response = get('/');

    $response->assertStatus(200);

    // Check that agents are passed to the view
    $response->assertInertia(fn ($page) => $page->has('agents')
        ->where('agents', fn ($agents) => count($agents) === 2 && // Should have 2 agents
            collect($agents)->pluck('email')->contains('agent1@example.com') &&
            collect($agents)->pluck('email')->contains('agent2@example.com') &&
            ! collect($agents)->pluck('email')->contains('user@example.com') // Regular user should not be included
        )
    );
});

it('displays all agents on agents index page', function () {
    // Create test roles
    $agentRole = Role::firstOrCreate(['name' => 'agent'], ['display_name' => 'Agent']);

    // Create multiple agents
    for ($i = 1; $i <= 3; $i++) {
        $user = User::factory()->create([
            'name' => "Agent{$i} Test",
            'email' => "agent{$i}@example.com",
            'is_active' => true,
        ]);
        $user->assignRole($agentRole);
    }

    // Visit agents index page
    $response = $this->get('/agents');

    $response->assertStatus(200);

    // Check that all agents are displayed (agents come paginated)
    $response->assertInertia(fn ($page) => $page->has('agents')
        ->has('agents.data')
        ->where('agents.data', fn ($agents) => count($agents) === 3)
    );
});

it('displays individual agent details', function () {
    // Create test role
    $agentRole = Role::firstOrCreate(['name' => 'agent'], ['display_name' => 'Agent']);

    // Create an agent with detailed information
    $agent = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john.doe@example.com',
        'phone' => '+1234567890',
        'bio' => 'Experienced agent with 10+ years in real estate.',
        'license_number' => 'RE123456',
        'address' => '123 Main St',
        'city' => 'New York',
        'state' => 'NY',
        'zip_code' => '10001',
        'is_active' => true,
    ]);
    $agent->assignRole($agentRole);

    // Visit individual agent page
    $response = $this->get("/agent/{$agent->id}");

    $response->assertStatus(200);

    // Check that agent details are displayed
    $response->assertInertia(fn ($page) => $page->has('agent')
        ->where('agent.name', 'John Doe')
        ->where('agent.email', 'john.doe@example.com')
        ->where('agent.bio', 'Experienced agent with 10+ years in real estate.')
    );
});

it('does not display inactive agents', function () {
    // Create test role
    $agentRole = Role::firstOrCreate(['name' => 'agent'], ['display_name' => 'Agent']);

    // Create active agent
    $activeAgent = User::factory()->create([
        'name' => 'Active Agent',
        'email' => 'active@example.com',
        'is_active' => true,
    ]);
    $activeAgent->assignRole($agentRole);

    // Create inactive agent
    $inactiveAgent = User::factory()->create([
        'name' => 'Inactive Agent',
        'email' => 'inactive@example.com',
        'is_active' => false,
    ]);
    $inactiveAgent->assignRole($agentRole);

    // Visit home page
    $response = $this->get('/');

    $response->assertStatus(200);

    // Check that only active agents are displayed
    $response->assertInertia(fn ($page) => $page->has('agents')
        ->where('agents', fn ($agents) => count($agents) === 1 &&
            collect($agents)->first()['email'] === 'active@example.com'
        )
    );
});

it('returns 404 for nonexistent agent', function () {
    $response = $this->get('/agent/99999');

    $response->assertStatus(404);
});

it('does not show regular users as agents', function () {
    // Create test roles
    $agentRole = Role::firstOrCreate(['name' => 'agent'], ['display_name' => 'Agent']);
    $userRole = Role::firstOrCreate(['name' => 'user'], ['display_name' => 'User']);

    // Create an agent
    $agent = User::factory()->create([
        'name' => 'Real Agent',
        'email' => 'real.agent@example.com',
        'is_active' => true,
    ]);
    $agent->assignRole($agentRole);

    // Create multiple regular users
    for ($i = 1; $i <= 3; $i++) {
        $user = User::factory()->create([
            'name' => "User{$i} Regular",
            'email' => "user{$i}@example.com",
            'is_active' => true,
        ]);
        $user->assignRole($userRole);
    }

    // Visit home page
    $response = $this->get('/');

    $response->assertStatus(200);

    // Check that only the agent is displayed, not regular users
    $response->assertInertia(fn ($page) => $page->has('agents')
        ->where('agents', fn ($agents) => count($agents) === 1 &&
            collect($agents)->first()['email'] === 'real.agent@example.com'
        )
    );
});
