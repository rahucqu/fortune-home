<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Amenity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AmenitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $amenities = [
            // Interior Amenities
            [
                'name' => 'Air Conditioning',
                'description' => 'Central air conditioning system',
                'icon' => 'snowflake',
                'category' => 'interior',
                'sort_order' => 1,
            ],
            [
                'name' => 'Hardwood Floors',
                'description' => 'Beautiful hardwood flooring throughout',
                'icon' => 'tree',
                'category' => 'interior',
                'sort_order' => 2,
            ],
            [
                'name' => 'Walk-in Closet',
                'description' => 'Spacious walk-in closet storage',
                'icon' => 'hanger',
                'category' => 'interior',
                'sort_order' => 3,
            ],
            [
                'name' => 'Fireplace',
                'description' => 'Cozy fireplace for warmth and ambiance',
                'icon' => 'flame',
                'category' => 'interior',
                'sort_order' => 4,
            ],
            [
                'name' => 'High Ceilings',
                'description' => 'Soaring high ceilings for open feel',
                'icon' => 'arrow-up',
                'category' => 'interior',
                'sort_order' => 5,
            ],
            [
                'name' => 'Updated Kitchen',
                'description' => 'Modern kitchen with updated appliances',
                'icon' => 'chef-hat',
                'category' => 'interior',
                'sort_order' => 6,
            ],
            [
                'name' => 'Granite Countertops',
                'description' => 'Premium granite countertops',
                'icon' => 'square',
                'category' => 'interior',
                'sort_order' => 7,
            ],
            [
                'name' => 'Stainless Steel Appliances',
                'description' => 'Modern stainless steel appliances',
                'icon' => 'tools-kitchen',
                'category' => 'interior',
                'sort_order' => 8,
            ],

            // Exterior Amenities
            [
                'name' => 'Swimming Pool',
                'description' => 'Private or community swimming pool',
                'icon' => 'pool',
                'category' => 'exterior',
                'sort_order' => 9,
            ],
            [
                'name' => 'Garden',
                'description' => 'Beautiful landscaped garden',
                'icon' => 'flower',
                'category' => 'exterior',
                'sort_order' => 10,
            ],
            [
                'name' => 'Balcony',
                'description' => 'Private balcony with city or nature views',
                'icon' => 'building-bridge',
                'category' => 'exterior',
                'sort_order' => 11,
            ],
            [
                'name' => 'Patio',
                'description' => 'Outdoor patio space for entertaining',
                'icon' => 'umbrella',
                'category' => 'exterior',
                'sort_order' => 12,
            ],
            [
                'name' => 'Garage',
                'description' => 'Attached or detached garage parking',
                'icon' => 'car-garage',
                'category' => 'exterior',
                'sort_order' => 13,
            ],
            [
                'name' => 'Deck',
                'description' => 'Wooden deck for outdoor relaxation',
                'icon' => 'stairs',
                'category' => 'exterior',
                'sort_order' => 14,
            ],

            // Building Amenities
            [
                'name' => 'Gym/Fitness Center',
                'description' => 'On-site fitness center and equipment',
                'icon' => 'barbell',
                'category' => 'building',
                'sort_order' => 15,
            ],
            [
                'name' => 'Concierge',
                'description' => '24/7 concierge services',
                'icon' => 'user-tie',
                'category' => 'building',
                'sort_order' => 16,
            ],
            [
                'name' => 'Doorman',
                'description' => 'Professional doorman service',
                'icon' => 'door',
                'category' => 'building',
                'sort_order' => 17,
            ],
            [
                'name' => 'Elevator',
                'description' => 'Modern elevator access',
                'icon' => 'elevator',
                'category' => 'building',
                'sort_order' => 18,
            ],
            [
                'name' => 'Laundry Room',
                'description' => 'On-site laundry facilities',
                'icon' => 'washing-machine',
                'category' => 'building',
                'sort_order' => 19,
            ],
            [
                'name' => 'Storage Unit',
                'description' => 'Additional storage space available',
                'icon' => 'box',
                'category' => 'building',
                'sort_order' => 20,
            ],
            [
                'name' => 'Rooftop Access',
                'description' => 'Access to rooftop terrace or garden',
                'icon' => 'building-skyscraper',
                'category' => 'building',
                'sort_order' => 21,
            ],

            // Technology & Security
            [
                'name' => 'High-Speed Internet',
                'description' => 'Fast fiber internet connection',
                'icon' => 'wifi',
                'category' => 'technology',
                'sort_order' => 22,
            ],
            [
                'name' => 'Security System',
                'description' => 'Advanced security and alarm system',
                'icon' => 'shield-check',
                'category' => 'technology',
                'sort_order' => 23,
            ],
            [
                'name' => 'Smart Home Features',
                'description' => 'Smart home automation and controls',
                'icon' => 'smart-home',
                'category' => 'technology',
                'sort_order' => 24,
            ],
            [
                'name' => 'Video Intercom',
                'description' => 'Video intercom system for security',
                'icon' => 'video',
                'category' => 'technology',
                'sort_order' => 25,
            ],

            // Location & Transportation
            [
                'name' => 'Near Public Transport',
                'description' => 'Close to metro, bus, or train stations',
                'icon' => 'train',
                'category' => 'location',
                'sort_order' => 26,
            ],
            [
                'name' => 'Shopping Nearby',
                'description' => 'Walking distance to shopping centers',
                'icon' => 'shopping-bag',
                'category' => 'location',
                'sort_order' => 27,
            ],
            [
                'name' => 'Near Schools',
                'description' => 'Close to quality schools and universities',
                'icon' => 'school',
                'category' => 'location',
                'sort_order' => 28,
            ],
            [
                'name' => 'Park View',
                'description' => 'Beautiful views of nearby parks',
                'icon' => 'tree-pine',
                'category' => 'location',
                'sort_order' => 29,
            ],
            [
                'name' => 'Waterfront',
                'description' => 'Located on or near waterfront',
                'icon' => 'waves',
                'category' => 'location',
                'sort_order' => 30,
            ],
        ];

        foreach ($amenities as $amenity) {
            Amenity::firstOrCreate(
                ['slug' => Str::slug($amenity['name'])],
                [
                    'name' => $amenity['name'],
                    'slug' => Str::slug($amenity['name']),
                    'description' => $amenity['description'],
                    'icon' => $amenity['icon'],
                    'category' => $amenity['category'],
                    'is_active' => true,
                    'sort_order' => $amenity['sort_order'],
                ]
            );
        }
    }
}