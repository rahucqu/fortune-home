<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SeoSetting;
use Illuminate\Database\Seeder;

class SeoSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = SeoSetting::getDefaults();

        foreach ($defaults as $key => $config) {
            SeoSetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $config['value'],
                    'type' => $config['type'],
                    'group' => $config['group'],
                    'description' => $config['description'],
                    'is_active' => true,
                    'sort_order' => 0,
                ]
            );
        }

        $this->command->info('SEO settings seeded successfully!');
    }
}Database\Seeders;

use Illuminate\Database\Seeder;

class SeoSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
    }
}
