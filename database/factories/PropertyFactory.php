<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Agent;
use App\Models\Location;
use App\Models\Property;
use App\Models\PropertyType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Property>
 */
class PropertyFactory extends Factory
{
    protected $model = Property::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->words(3, true);

        return [
            'title' => $title,
            'slug' => Str::slug($title) . '-' . fake()->unique()->randomNumber(5),
            'description' => fake()->paragraphs(3, true),
            'price' => fake()->numberBetween(100000, 2000000),
            'bedrooms' => fake()->numberBetween(1, 6),
            'bathrooms' => fake()->numberBetween(1, 4),
            'area_sqft' => fake()->numberBetween(500, 5000),
            'address' => fake()->address(),
            'status' => fake()->randomElement(['available', 'sold', 'rented', 'pending']),
            'listing_type' => fake()->randomElement(['sale', 'rent']),
            'is_featured' => fake()->boolean(20), // 20% chance of being featured
            'property_type_id' => PropertyType::factory(),
            'agent_id' => Agent::factory(),
            'location_id' => Location::factory(),
        ];
    }

    /**
     * Indicate that the property is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Indicate that the property is available.
     */
    public function available(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'available',
        ]);
    }

    /**
     * Indicate that the property is sold.
     */
    public function sold(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'sold',
        ]);
    }

    /**
     * Indicate that the property is for sale.
     */
    public function forSale(): static
    {
        return $this->state(fn (array $attributes) => [
            'listing_type' => 'sale',
        ]);
    }

    /**
     * Indicate that the property is for rent.
     */
    public function forRent(): static
    {
        return $this->state(fn (array $attributes) => [
            'listing_type' => 'rent',
        ]);
    }
}
