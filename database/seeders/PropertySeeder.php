<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\Location;
use App\Models\Agent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some reference data
        $propertyTypes = PropertyType::all();
        $locations = Location::all();
        $agents = Agent::all();
        $amenities = Amenity::all();

        if ($propertyTypes->isEmpty() || $locations->isEmpty() || $agents->isEmpty()) {
            $this->command->warn('Please run PropertyTypeSeeder, LocationSeeder, and AgentSeeder first');
            return;
        }

        $properties = [
            [
                'title' => 'Luxury Manhattan Penthouse',
                'description' => 'Stunning penthouse with panoramic city views, featuring floor-to-ceiling windows, marble finishes, and a private terrace. This exceptional property offers the ultimate in luxury living in the heart of Manhattan.',
                'listing_type' => 'sale',
                'status' => 'available',
                'price' => 2500000.00,
                'currency' => 'USD',
                'bedrooms' => 3,
                'bathrooms' => 3,
                'total_rooms' => 8,
                'area_sqft' => 2200.00,
                'floors' => 1,
                'floor_number' => 42,
                'built_year' => 2020,
                'address' => '432 Park Avenue, New York, NY 10022',
                'latitude' => 40.7614,
                'longitude' => -73.9776,
                'postal_code' => '10022',
                'is_furnished' => true,
                'has_parking' => true,
                'parking_spaces' => 2,
                'pet_friendly' => false,
                'is_featured' => true,
                'property_type' => 'penthouse',
                'location' => 'manhattan',
                'views_count' => 1245,
                'favorites_count' => 89,
                'inquiries_count' => 23,
            ],
            [
                'title' => 'Modern Brooklyn Townhouse',
                'description' => 'Beautifully renovated townhouse in trendy Brooklyn neighborhood. Features original brick walls, modern kitchen, rooftop deck, and private garden. Perfect for families seeking urban convenience with a neighborhood feel.',
                'listing_type' => 'sale',
                'status' => 'available',
                'price' => 1200000.00,
                'currency' => 'USD',
                'bedrooms' => 4,
                'bathrooms' => 3,
                'total_rooms' => 10,
                'area_sqft' => 2800.00,
                'land_area_sqft' => 1200.00,
                'floors' => 3,
                'built_year' => 1920,
                'address' => '123 Prospect Street, Brooklyn, NY 11215',
                'latitude' => 40.6692,
                'longitude' => -73.9896,
                'postal_code' => '11215',
                'is_furnished' => false,
                'has_parking' => true,
                'parking_spaces' => 1,
                'pet_friendly' => true,
                'is_featured' => true,
                'property_type' => 'townhouse',
                'location' => 'brooklyn',
                'views_count' => 897,
                'favorites_count' => 67,
                'inquiries_count' => 18,
            ],
            [
                'title' => 'Beverly Hills Luxury Villa',
                'description' => 'Spectacular estate in prestigious Beverly Hills with pool, tennis court, and manicured gardens. This architectural masterpiece offers privacy and elegance with top-of-the-line amenities throughout.',
                'listing_type' => 'sale',
                'status' => 'available',
                'price' => 8500000.00,
                'currency' => 'USD',
                'bedrooms' => 6,
                'bathrooms' => 7,
                'total_rooms' => 15,
                'area_sqft' => 6500.00,
                'land_area_sqft' => 15000.00,
                'floors' => 2,
                'built_year' => 2018,
                'address' => '1010 Benedict Canyon Drive, Beverly Hills, CA 90210',
                'latitude' => 34.0928,
                'longitude' => -118.4081,
                'postal_code' => '90210',
                'is_furnished' => true,
                'has_parking' => true,
                'parking_spaces' => 6,
                'pet_friendly' => true,
                'is_featured' => true,
                'property_type' => 'villa',
                'location' => 'beverly-hills',
                'views_count' => 2341,
                'favorites_count' => 156,
                'inquiries_count' => 45,
            ],
            [
                'title' => 'Downtown Chicago Loft',
                'description' => 'Industrial chic loft in converted warehouse with exposed brick, high ceilings, and city skyline views. Located in vibrant downtown area with easy access to restaurants, shopping, and public transportation.',
                'listing_type' => 'rent',
                'status' => 'available',
                'price' => 0.00,
                'monthly_rent' => 3200.00,
                'currency' => 'USD',
                'bedrooms' => 2,
                'bathrooms' => 2,
                'total_rooms' => 4,
                'area_sqft' => 1800.00,
                'floors' => 1,
                'floor_number' => 5,
                'built_year' => 1925,
                'address' => '200 West Randolph Street, Chicago, IL 60606',
                'latitude' => 41.8845,
                'longitude' => -87.6368,
                'postal_code' => '60606',
                'is_furnished' => false,
                'has_parking' => true,
                'parking_spaces' => 1,
                'pet_friendly' => true,
                'is_featured' => false,
                'property_type' => 'loft',
                'location' => 'chicago',
                'views_count' => 456,
                'favorites_count' => 34,
                'inquiries_count' => 12,
            ],
            [
                'title' => 'Miami Beach Oceanfront Condo',
                'description' => 'Stunning oceanfront condominium with direct beach access and breathtaking ocean views. Features floor-to-ceiling windows, marble bathrooms, and access to world-class amenities including spa and fitness center.',
                'listing_type' => 'sale',
                'status' => 'available',
                'price' => 1800000.00,
                'currency' => 'USD',
                'bedrooms' => 3,
                'bathrooms' => 3,
                'total_rooms' => 7,
                'area_sqft' => 2100.00,
                'floors' => 1,
                'floor_number' => 18,
                'built_year' => 2019,
                'address' => '9701 Collins Avenue, Miami Beach, FL 33154',
                'latitude' => 25.8867,
                'longitude' => -80.1210,
                'postal_code' => '33154',
                'is_furnished' => true,
                'has_parking' => true,
                'parking_spaces' => 2,
                'pet_friendly' => false,
                'is_featured' => true,
                'property_type' => 'condominium',
                'location' => 'miami',
                'views_count' => 1789,
                'favorites_count' => 123,
                'inquiries_count' => 34,
            ],
            [
                'title' => 'San Francisco Victorian House',
                'description' => 'Charming Victorian house with original architectural details, updated modern amenities, and stunning city views. Located in desirable neighborhood with easy access to tech companies and downtown.',
                'listing_type' => 'sale',
                'status' => 'pending',
                'price' => 2200000.00,
                'currency' => 'USD',
                'bedrooms' => 4,
                'bathrooms' => 3,
                'total_rooms' => 9,
                'area_sqft' => 2600.00,
                'land_area_sqft' => 800.00,
                'floors' => 3,
                'built_year' => 1920,
                'address' => '1847 Lombard Street, San Francisco, CA 94123',
                'latitude' => 37.8022,
                'longitude' => -122.4286,
                'postal_code' => '94123',
                'is_furnished' => false,
                'has_parking' => true,
                'parking_spaces' => 2,
                'pet_friendly' => true,
                'is_featured' => true,
                'property_type' => 'house',
                'location' => 'san-francisco',
                'views_count' => 1234,
                'favorites_count' => 98,
                'inquiries_count' => 28,
            ],
            [
                'title' => 'Seattle Modern Apartment',
                'description' => 'Contemporary apartment in new high-rise building with panoramic mountain and city views. Features modern finishes, in-unit laundry, and access to rooftop amenities including outdoor kitchen and lounge areas.',
                'listing_type' => 'rent',
                'status' => 'available',
                'price' => 0.00,
                'monthly_rent' => 2800.00,
                'currency' => 'USD',
                'bedrooms' => 2,
                'bathrooms' => 2,
                'total_rooms' => 5,
                'area_sqft' => 1400.00,
                'floors' => 1,
                'floor_number' => 25,
                'built_year' => 2021,
                'address' => '400 Dexter Avenue, Seattle, WA 98109',
                'latitude' => 47.6235,
                'longitude' => -122.3493,
                'postal_code' => '98109',
                'is_furnished' => false,
                'has_parking' => true,
                'parking_spaces' => 1,
                'pet_friendly' => true,
                'is_featured' => false,
                'property_type' => 'apartment',
                'location' => 'seattle',
                'views_count' => 678,
                'favorites_count' => 45,
                'inquiries_count' => 15,
            ],
            [
                'title' => 'Boston Historic Duplex',
                'description' => 'Beautiful historic duplex in prestigious Back Bay neighborhood. Original hardwood floors, high ceilings, working fireplaces, and period details throughout. Walking distance to public gardens and shopping.',
                'listing_type' => 'sale',
                'status' => 'available',
                'price' => 1650000.00,
                'currency' => 'USD',
                'bedrooms' => 5,
                'bathrooms' => 4,
                'total_rooms' => 12,
                'area_sqft' => 3200.00,
                'floors' => 2,
                'built_year' => 1910,
                'address' => '456 Commonwealth Avenue, Boston, MA 02215',
                'latitude' => 42.3505,
                'longitude' => -71.0892,
                'postal_code' => '02215',
                'is_furnished' => false,
                'has_parking' => false,
                'parking_spaces' => 0,
                'pet_friendly' => true,
                'is_featured' => true,
                'property_type' => 'duplex',
                'location' => 'boston',
                'views_count' => 934,
                'favorites_count' => 72,
                'inquiries_count' => 21,
            ],
        ];

        foreach ($properties as $propertyData) {
            // Find related entities
            $propertyType = $propertyTypes->where('slug', $propertyData['property_type'])->first();
            $location = $locations->where('slug', $propertyData['location'])->first();
            $agent = $agents->random();

            if (!$propertyType || !$location) {
                continue;
            }

            $property = Property::firstOrCreate(
                ['slug' => Str::slug($propertyData['title'])],
                [
                    'title' => $propertyData['title'],
                    'slug' => Str::slug($propertyData['title']),
                    'description' => $propertyData['description'],
                    'listing_type' => $propertyData['listing_type'],
                    'status' => $propertyData['status'],
                    'price' => $propertyData['price'] ?? null,
                    'monthly_rent' => $propertyData['monthly_rent'] ?? null,
                    'currency' => $propertyData['currency'],
                    'bedrooms' => $propertyData['bedrooms'],
                    'bathrooms' => $propertyData['bathrooms'],
                    'total_rooms' => $propertyData['total_rooms'],
                    'area_sqft' => $propertyData['area_sqft'],
                    'land_area_sqft' => $propertyData['land_area_sqft'] ?? null,
                    'floors' => $propertyData['floors'],
                    'floor_number' => $propertyData['floor_number'] ?? null,
                    'built_year' => $propertyData['built_year'],
                    'address' => $propertyData['address'],
                    'latitude' => $propertyData['latitude'],
                    'longitude' => $propertyData['longitude'],
                    'postal_code' => $propertyData['postal_code'],
                    'is_furnished' => $propertyData['is_furnished'],
                    'has_parking' => $propertyData['has_parking'],
                    'parking_spaces' => $propertyData['parking_spaces'],
                    'pet_friendly' => $propertyData['pet_friendly'],
                    'is_featured' => $propertyData['is_featured'],
                    'property_type_id' => $propertyType->id,
                    'location_id' => $location->id,
                    'agent_id' => $agent->id,
                    'views_count' => $propertyData['views_count'],
                    'favorites_count' => $propertyData['favorites_count'],
                    'inquiries_count' => $propertyData['inquiries_count'],
                ]
            );

            // Attach random amenities to each property
            $randomAmenities = $amenities->random(rand(5, 12));
            $property->amenities()->sync($randomAmenities->pluck('id')->toArray());
        }
    }
}