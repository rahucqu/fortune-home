<?php

declare(strict_types=1);

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Property;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Foundation - Roles, Permissions, and Core Settings
        $this->call([
            RolePermissionSeeder::class,
            SettingSeeder::class,
        ]);

        $this->call([
            UserSeeder::class,
        ]);

        $this->call([
            PropertyTypeSeeder::class,
            FeatureSeeder::class,
            LocationSeeder::class,
            AmenitySeeder::class,
        ]);

        // 4. Core Property Data
        $this->call([
            PropertySeeder::class,
            PropertyImageSeeder::class,
            PropertyFloorPlanSeeder::class,
        ]);

        // 5. Property Interactions
        $this->call([
            PropertyInquirySeeder::class,
            PropertyFavoriteSeeder::class,
            SavedSearchSeeder::class,
        ]);

        // 6. Blog System
        $this->call([
            BlogCategorySeeder::class,
            BlogPostSeeder::class,
            BlogTagSeeder::class,
        ]);

        // 7. Communication and Analytics
        $this->call([
            ContactInquirySeeder::class,
            PropertyViewSeeder::class,
        ]);

        // 8. SEO and Marketing
        $this->call([
            SeoMetaSeeder::class,
        ]);

    }
}
