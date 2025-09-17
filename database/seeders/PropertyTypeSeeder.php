<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\PropertyType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PropertyTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $propertyTypes = [
            [
                'name' => 'Apartment',
                'description' => 'Multi-story residential buildings with individual units',
                'icon' => 'building',
                'sort_order' => 1,
            ],
            [
                'name' => 'House',
                'description' => 'Single-family detached homes',
                'icon' => 'home',
                'sort_order' => 2,
            ],
            [
                'name' => 'Townhouse',
                'description' => 'Multi-story homes sharing walls with neighbors',
                'icon' => 'buildings',
                'sort_order' => 3,
            ],
            [
                'name' => 'Condominium',
                'description' => 'Privately owned units in shared buildings',
                'icon' => 'building-2',
                'sort_order' => 4,
            ],
            [
                'name' => 'Villa',
                'description' => 'Luxury detached homes with premium amenities',
                'icon' => 'castle',
                'sort_order' => 5,
            ],
            [
                'name' => 'Studio',
                'description' => 'Single-room living spaces with combined areas',
                'icon' => 'square',
                'sort_order' => 6,
            ],
            [
                'name' => 'Duplex',
                'description' => 'Two-unit buildings sharing a common wall',
                'icon' => 'building-arch',
                'sort_order' => 7,
            ],
            [
                'name' => 'Penthouse',
                'description' => 'Luxury units on the top floors of buildings',
                'icon' => 'building-skyscraper',
                'sort_order' => 8,
            ],
            [
                'name' => 'Loft',
                'description' => 'Open-plan living spaces, often in converted buildings',
                'icon' => 'warehouse',
                'sort_order' => 9,
            ],
            [
                'name' => 'Commercial',
                'description' => 'Properties for business and commercial use',
                'icon' => 'briefcase',
                'sort_order' => 10,
            ],
        ];

        foreach ($propertyTypes as $propertyType) {
            PropertyType::firstOrCreate(
                ['slug' => Str::slug($propertyType['name'])],
                [
                    'name' => $propertyType['name'],
                    'slug' => Str::slug($propertyType['name']),
                    'description' => $propertyType['description'],
                    'icon' => $propertyType['icon'],
                    'is_active' => true,
                    'sort_order' => $propertyType['sort_order'],
                ]
            );
        }
    }
}