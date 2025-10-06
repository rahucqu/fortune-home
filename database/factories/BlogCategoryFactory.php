<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BlogCategory>
 */
class BlogCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $categories = [
            'Real Estate Tips' => 'Helpful advice and tips for buying, selling, and investing in real estate.',
            'Market Analysis' => 'In-depth analysis of local and national real estate market trends.',
            'Home Improvement' => 'Ideas and guides for improving your home\'s value and appeal.',
            'Investment Strategies' => 'Strategies and insights for real estate investment success.',
            'First Time Buyers' => 'Essential information and guidance for first-time home buyers.',
            'Luxury Properties' => 'Showcasing luxury homes and high-end real estate opportunities.',
            'Commercial Real Estate' => 'News and insights about commercial property markets.',
            'Neighborhood Guides' => 'Detailed guides to local neighborhoods and communities.',
            'Legal & Finance' => 'Important legal and financial information for property transactions.',
            'Technology & Innovation' => 'How technology is changing the real estate industry.',
        ];

        // Either use a predefined category or create a random one
        $useRandom = fake()->boolean(30);  // 30% chance to use random category

        if ($useRandom) {
            $category = 'Category '.fake()->words(2, true);
            $description = 'Description for '.$category;
        } else {
            $category = fake()->randomElement(array_keys($categories));
            $description = $categories[$category];
        }

        // Generate a unique slug by adding a random string
        $uniqueSlug = Str::slug($category).'-'.strtolower(Str::random(5));

        return [
            'name' => $category,
            'slug' => $uniqueSlug,
            'description' => $description,
            'meta_title' => $category.' - Fortune Home Blog',
            'meta_description' => $description,
            'is_active' => fake()->boolean(95), // 95% active
            'sort_order' => fake()->numberBetween(1, 100),
        ];
    }

    /**
     * Create an active category.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Create an inactive category.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a category with specific sort order.
     */
    public function sortOrder(int $order): static
    {
        return $this->state(fn (array $attributes) => [
            'sort_order' => $order,
        ]);
    }
}
