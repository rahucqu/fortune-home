<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Location>
 */
class LocationFactory extends Factory
{
    protected $model = Location::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->randomElement([
            'Downtown',
            'Uptown',
            'Suburb Hills',
            'City Center',
            'Waterfront',
            'Historic District',
            'Business District',
            'Residential Area',
            'Garden District',
            'Arts Quarter',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . fake()->unique()->randomNumber(5),
            'type' => fake()->randomElement(['city', 'suburb', 'neighborhood']),
            'state' => fake()->randomElement(['Dhaka', 'Chittagong', 'Sylhet', 'Khulna', 'Rajshahi', 'Rangpur', 'Barisal', 'Mymensingh']),
            'country' => 'Bangladesh',
            'latitude' => fake()->latitude(20, 26),
            'longitude' => fake()->longitude(88, 93),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 100),
        ];
    }
}
