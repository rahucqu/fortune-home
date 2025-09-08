<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class CreatePersonalTeamsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create personal teams for users who don't have them
        User::whereDoesntHave('ownedTeams', function ($query) {
            $query->where('personal_team', true);
        })->each(function (User $user) {
            $team = $user->ownedTeams()->create([
                'name' => explode(' ', $user->name, 2)[0] . "'s Team",
                'personal_team' => true,
            ]);

            $user->current_team_id = $team->id;
            $user->save();
        });
    }
}
