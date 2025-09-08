<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use App\Models\Media;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(4, true);
        $content = fake()->paragraphs(10, true);
        
        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'excerpt' => fake()->paragraph(3),
            'content' => $content,
            'meta_title' => fake()->optional(0.7)->sentence(6, true),
            'meta_description' => fake()->optional(0.8)->paragraph(2),
            'meta_keywords' => fake()->optional(0.6)->words(5, true),
            'status' => fake()->randomElement(['draft', 'published', 'scheduled', 'archived']),
            'published_at' => fake()->optional(0.8)->dateTimeBetween('-1 year', 'now'),
            'scheduled_at' => fake()->optional(0.1)->dateTimeBetween('now', '+1 month'),
            'is_featured' => fake()->boolean(20), // 20% chance of being featured
            'allow_comments' => fake()->boolean(90), // 90% allow comments
            'is_sticky' => fake()->boolean(5), // 5% chance of being sticky
            'user_id' => User::factory(),
            'category_id' => fake()->optional(0.9)->randomElement(Category::pluck('id')->toArray() ?: [Category::factory()->create()->id]),
            'featured_image_id' => fake()->optional(0.7)->randomElement(Media::where('type', 'image')->pluck('id')->toArray() ?: []),
            'views_count' => fake()->numberBetween(0, 10000),
            'comments_count' => fake()->numberBetween(0, 100),
            'sort_order' => fake()->numberBetween(0, 1000),
        ];
    }

    /**
     * Indicate that the post is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    /**
     * Indicate that the post is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the post is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Indicate that the post is scheduled.
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'scheduled',
            'scheduled_at' => fake()->dateTimeBetween('now', '+1 month'),
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the post has high views.
     */
    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'views_count' => fake()->numberBetween(5000, 50000),
            'comments_count' => fake()->numberBetween(50, 500),
        ]);
    }

    /**
     * Indicate that the post has a specific category.
     */
    public function withCategory(int $categoryId): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => $categoryId,
        ]);
    }

    /**
     * Indicate that the post has a specific author.
     */
    public function byAuthor(int $userId): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $userId,
        ]);
    }

    /**
     * Configure the factory to create a post with tags after creation.
     */
    public function withTags(int $count = 3): static
    {
        return $this->afterCreating(function (Post $post) use ($count) {
            $tagIds = \App\Models\Tag::inRandomOrder()->limit($count)->pluck('id');
            $post->tags()->sync($tagIds);
        });
    }
}
