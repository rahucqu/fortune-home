<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        return [
            'content' => $this->faker->paragraphs(rand(1, 3), true),
            'author_name' => $this->faker->name(),
            'author_email' => $this->faker->safeEmail(),
            'author_website' => $this->faker->optional()->url(),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected', 'spam']),
            'post_id' => Post::factory(),
            'user_id' => $this->faker->boolean(70) ? User::factory() : null,
            'parent_id' => null,
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'metadata' => $this->faker->optional()->randomElements([
                'referrer' => $this->faker->url(),
                'source' => $this->faker->randomElement(['web', 'mobile', 'api']),
                'tags' => $this->faker->words(3),
            ]),
            'likes_count' => $this->faker->numberBetween(0, 50),
            'replies_count' => 0,
            'is_featured' => $this->faker->boolean(5),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'approved_by' => User::factory(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'approved_at' => null,
            'approved_by' => null,
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'approved_at' => null,
            'approved_by' => null,
        ]);
    }

    public function spam(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'spam',
            'approved_at' => null,
            'approved_by' => null,
        ]);
    }

    public function fromRegisteredUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => User::factory(),
            'author_name' => null,
            'author_email' => null,
        ]);
    }

    public function fromGuest(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
            'author_name' => $this->faker->name(),
            'author_email' => $this->faker->safeEmail(),
        ]);
    }

    public function reply(Comment $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
            'post_id' => $parent->post_id,
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
            'status' => 'approved',
            'approved_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'approved_by' => User::factory(),
        ]);
    }

    public function withLikes(int $min = 1, int $max = 100): static
    {
        return $this->state(fn (array $attributes) => [
            'likes_count' => $this->faker->numberBetween($min, $max),
        ]);
    }

    public function withReplies(int $count = 3): static
    {
        return $this->afterCreating(function (Comment $comment) use ($count) {
            Comment::factory()
                ->count($count)
                ->approved()
                ->reply($comment)
                ->create();
                
            $comment->update(['replies_count' => $count]);
        });
    }

    public function longContent(): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => $this->faker->paragraphs(rand(5, 10), true),
        ]);
    }

    public function shortContent(): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => $this->faker->sentence(rand(5, 15)),
        ]);
    }
}
