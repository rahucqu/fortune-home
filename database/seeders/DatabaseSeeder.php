<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ComprehensiveRoleSeeder::class,
            ContentSeeder::class,
        ]);

        // Create additional test users if needed
        // User::factory(10)->create();
    }
}
