<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            // Major Cities
            [
                'name' => 'New York City',
                'type' => 'city',
                'state' => 'New York',
                'country' => 'United States',
                'latitude' => 40.7128,
                'longitude' => -74.0060,
                'sort_order' => 1,
            ],
            [
                'name' => 'Los Angeles',
                'type' => 'city',
                'state' => 'California',
                'country' => 'United States',
                'latitude' => 34.0522,
                'longitude' => -118.2437,
                'sort_order' => 2,
            ],
            [
                'name' => 'Chicago',
                'type' => 'city',
                'state' => 'Illinois',
                'country' => 'United States',
                'latitude' => 41.8781,
                'longitude' => -87.6298,
                'sort_order' => 3,
            ],
            [
                'name' => 'Houston',
                'type' => 'city',
                'state' => 'Texas',
                'country' => 'United States',
                'latitude' => 29.7604,
                'longitude' => -95.3698,
                'sort_order' => 4,
            ],
            [
                'name' => 'Miami',
                'type' => 'city',
                'state' => 'Florida',
                'country' => 'United States',
                'latitude' => 25.7617,
                'longitude' => -80.1918,
                'sort_order' => 5,
            ],
            [
                'name' => 'San Francisco',
                'type' => 'city',
                'state' => 'California',
                'country' => 'United States',
                'latitude' => 37.7749,
                'longitude' => -122.4194,
                'sort_order' => 6,
            ],
            [
                'name' => 'Seattle',
                'type' => 'city',
                'state' => 'Washington',
                'country' => 'United States',
                'latitude' => 47.6062,
                'longitude' => -122.3321,
                'sort_order' => 7,
            ],
            [
                'name' => 'Boston',
                'type' => 'city',
                'state' => 'Massachusetts',
                'country' => 'United States',
                'latitude' => 42.3601,
                'longitude' => -71.0589,
                'sort_order' => 8,
            ],

            // Neighborhoods/Districts
            [
                'name' => 'Manhattan',
                'type' => 'neighborhood',
                'state' => 'New York',
                'country' => 'United States',
                'latitude' => 40.7831,
                'longitude' => -73.9712,
                'sort_order' => 9,
            ],
            [
                'name' => 'Brooklyn',
                'type' => 'neighborhood',
                'state' => 'New York',
                'country' => 'United States',
                'latitude' => 40.6782,
                'longitude' => -73.9442,
                'sort_order' => 10,
            ],
            [
                'name' => 'Beverly Hills',
                'type' => 'neighborhood',
                'state' => 'California',
                'country' => 'United States',
                'latitude' => 34.0736,
                'longitude' => -118.4004,
                'sort_order' => 11,
            ],
            [
                'name' => 'Hollywood',
                'type' => 'neighborhood',
                'state' => 'California',
                'country' => 'United States',
                'latitude' => 34.0928,
                'longitude' => -118.3287,
                'sort_order' => 12,
            ],
            [
                'name' => 'South Beach',
                'type' => 'neighborhood',
                'state' => 'Florida',
                'country' => 'United States',
                'latitude' => 25.7907,
                'longitude' => -80.1300,
                'sort_order' => 13,
            ],

            // Suburban Areas
            [
                'name' => 'Westchester County',
                'type' => 'suburb',
                'state' => 'New York',
                'country' => 'United States',
                'latitude' => 41.1220,
                'longitude' => -73.7949,
                'sort_order' => 14,
            ],
            [
                'name' => 'Orange County',
                'type' => 'suburb',
                'state' => 'California',
                'country' => 'United States',
                'latitude' => 33.7175,
                'longitude' => -117.8311,
                'sort_order' => 15,
            ],
        ];

        foreach ($locations as $location) {
            Location::firstOrCreate(
                ['slug' => Str::slug($location['name'])],
                [
                    'name' => $location['name'],
                    'slug' => Str::slug($location['name']),
                    'type' => $location['type'],
                    'state' => $location['state'],
                    'country' => $location['country'],
                    'latitude' => $location['latitude'],
                    'longitude' => $location['longitude'],
                    'is_active' => true,
                    'sort_order' => $location['sort_order'],
                ]
            );
        }
    }
}