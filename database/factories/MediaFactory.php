<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Media>
 */
class MediaFactory extends Factory
{
    protected $model = Media::class;

    public function definition(): array
    {
        $types = ['image', 'document', 'video', 'audio', 'other'];
        $type = $this->faker->randomElement($types);

        return [
            'name' => $this->faker->words(3, true),
            'file_name' => $this->faker->slug . '.' . $this->getExtensionForType($type),
            'original_name' => $this->faker->words(2, true) . '.' . $this->getExtensionForType($type),
            'path' => 'media/' . $this->faker->slug . '.' . $this->getExtensionForType($type),
            'mime_type' => $this->getMimeTypeForType($type),
            'type' => $type,
            'size' => $this->faker->numberBetween(1024, 10485760), // 1KB to 10MB
            'width' => $type === 'image' ? $this->faker->numberBetween(200, 1920) : null,
            'height' => $type === 'image' ? $this->faker->numberBetween(200, 1080) : null,
            'alt_text' => $type === 'image' ? $this->faker->sentence : null,
            'description' => $this->faker->optional()->paragraph,
            'metadata' => [],
            'is_active' => $this->faker->boolean(90), // 90% chance of being active
            'uploaded_by' => User::factory(),
        ];
    }

    public function image(): static
    {
        return $this->state(function () {
            return [
                'type' => 'image',
                'mime_type' => $this->faker->randomElement(['image/jpeg', 'image/png', 'image/gif', 'image/webp']),
                'file_name' => $this->faker->slug . '.jpg',
                'original_name' => $this->faker->words(2, true) . '.jpg',
                'path' => 'media/' . $this->faker->slug . '.jpg',
                'width' => $this->faker->numberBetween(200, 1920),
                'height' => $this->faker->numberBetween(200, 1080),
                'alt_text' => $this->faker->sentence,
            ];
        });
    }

    public function document(): static
    {
        return $this->state(function () {
            return [
                'type' => 'document',
                'mime_type' => $this->faker->randomElement(['application/pdf', 'application/msword']),
                'file_name' => $this->faker->slug . '.pdf',
                'original_name' => $this->faker->words(2, true) . '.pdf',
                'path' => 'media/' . $this->faker->slug . '.pdf',
                'width' => null,
                'height' => null,
                'alt_text' => null,
            ];
        });
    }

    public function video(): static
    {
        return $this->state(function () {
            return [
                'type' => 'video',
                'mime_type' => $this->faker->randomElement(['video/mp4', 'video/avi', 'video/mov']),
                'file_name' => $this->faker->slug . '.mp4',
                'original_name' => $this->faker->words(2, true) . '.mp4',
                'path' => 'media/' . $this->faker->slug . '.mp4',
                'width' => null,
                'height' => null,
                'alt_text' => null,
                'size' => $this->faker->numberBetween(5242880, 104857600), // 5MB to 100MB
            ];
        });
    }

    public function active(): static
    {
        return $this->state(['is_active' => true]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    private function getExtensionForType(string $type): string
    {
        return match ($type) {
            'image' => $this->faker->randomElement(['jpg', 'png', 'gif', 'webp']),
            'document' => $this->faker->randomElement(['pdf', 'doc', 'docx']),
            'video' => $this->faker->randomElement(['mp4', 'avi', 'mov']),
            'audio' => $this->faker->randomElement(['mp3', 'wav', 'ogg']),
            default => 'txt',
        };
    }

    private function getMimeTypeForType(string $type): string
    {
        return match ($type) {
            'image' => $this->faker->randomElement(['image/jpeg', 'image/png', 'image/gif', 'image/webp']),
            'document' => $this->faker->randomElement(['application/pdf', 'application/msword']),
            'video' => $this->faker->randomElement(['video/mp4', 'video/avi', 'video/mov']),
            'audio' => $this->faker->randomElement(['audio/mp3', 'audio/wav', 'audio/ogg']),
            default => 'text/plain',
        };
    }
}
