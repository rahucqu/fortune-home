<?php

declare(strict_types=1);

namespace Database\Seeders;

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
            // Role and permission setup
            RolePermissionSeeder::class,


            // Property-related seeders (must be in this order due to relationships)
            PropertyTypeSeeder::class,
            LocationSeeder::class,
            AgentSeeder::class,
            AmenitySeeder::class,
            PropertySeeder::class,

            // Admin and user setup
            AdminUserSeeder::class,
            CreatePersonalTeamsSeeder::class,

            // SEO settings
            SeoSettingsSeeder::class,
        ]);

        // Create additional test users if needed
        // User::factory(10)->create();
    }
}
