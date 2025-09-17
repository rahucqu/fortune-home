<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Amenity;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Amenity>
 */
class AmenityFactory extends Factory
{
    protected $model = Amenity::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->randomElement([
            'Swimming Pool',
            'Gym',
            'Parking',
            'Garden',
            'Balcony',
            'Air Conditioning',
            'Heating',
            'Fireplace',
            'Walk-in Closet',
            'Dishwasher',
            'Laundry Room',
            'Security System',
            'Elevator',
            'Pet Friendly',
            'WiFi',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . fake()->unique()->randomNumber(5),
            'description' => fake()->sentence(),
            'category' => fake()->randomElement(['interior', 'exterior', 'building', 'neighborhood']),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 100),
        ];
    }
}
