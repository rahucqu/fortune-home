<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Media;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting content seeding...');

        // Disable foreign key checks for faster seeding
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Create categories
        $this->seedCategories();

        // Create tags
        $this->seedTags();

        // Create media files
        $this->seedMedia();

        // Get users with roles for posts
        $users = $this->getContentCreators();

        // Create posts
        $this->seedPosts($users);

        // Create comments
        $this->seedComments($users);

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('✅ Content seeding completed successfully!');
        $this->displayStats();
    }

    /**
     * Seed categories
     */
    private function seedCategories(): void
    {
        $this->command->info('📁 Creating 50 categories...');

        // Predefined category topics for better realism
        $categoryTopics = [
            'Technology', 'Programming', 'Web Development', 'Mobile Development', 'AI & Machine Learning',
            'Data Science', 'Cybersecurity', 'Cloud Computing', 'DevOps', 'Software Engineering',
            'Frontend Development', 'Backend Development', 'Full Stack', 'Database', 'API Development',
            'JavaScript', 'Python', 'PHP', 'Laravel', 'React',
            'Vue.js', 'Node.js', 'Docker', 'Kubernetes', 'AWS',
            'Digital Marketing', 'SEO', 'Content Marketing', 'Social Media', 'E-commerce',
            'Startup', 'Entrepreneurship', 'Business', 'Finance', 'Productivity',
            'Design', 'UI/UX', 'Graphic Design', 'Photography', 'Video Editing',
            'Health & Fitness', 'Travel', 'Food & Cooking', 'Lifestyle', 'Education',
            'Science', 'Sports', 'Gaming', 'Entertainment', 'News & Politics',
        ];

        foreach ($categoryTopics as $index => $topic) {
            Category::factory()->create([
                'name' => $topic,
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);
        }

        $this->command->info('✅ Categories created successfully!');
    }

    /**
     * Seed tags
     */
    private function seedTags(): void
    {
        $this->command->info('🏷️  Creating 50 tags...');

        // Predefined tag topics
        $tagTopics = [
            'laravel', 'php', 'javascript', 'react', 'vue', 'angular', 'node.js', 'express',
            'mysql', 'postgresql', 'mongodb', 'redis', 'elasticsearch', 'docker', 'kubernetes',
            'aws', 'azure', 'gcp', 'devops', 'ci/cd', 'git', 'github', 'gitlab',
            'api', 'rest', 'graphql', 'microservices', 'architecture', 'design-patterns',
            'testing', 'unit-testing', 'integration-testing', 'tdd', 'bdd', 'performance',
            'security', 'authentication', 'authorization', 'oauth', 'jwt', 'encryption',
            'frontend', 'backend', 'fullstack', 'mobile', 'ios', 'android', 'flutter',
            'tutorial', 'guide', 'tips', 'best-practices',
        ];

        // Color palette for tags
        $colors = [
            '#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6', '#F97316', '#06B6D4', '#84CC16',
            '#EC4899', '#6B7280', '#14B8A6', '#F472B6', '#A78BFA', '#FB7185', '#34D399',
            '#FBBF24', '#60A5FA', '#F87171', '#A3E635', '#22D3EE', '#C084FC', '#FDBA74',
        ];

        foreach ($tagTopics as $index => $topic) {
            Tag::factory()->create([
                'name' => $topic,
                'color' => $colors[$index % count($colors)],
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);
        }

        $this->command->info('✅ Tags created successfully!');
    }

    /**
     * Seed media files
     */
    private function seedMedia(): void
    {
        $this->command->info('🖼️  Creating 50 media files...');

        // Create a mix of different media types
        $mediaTypes = ['image', 'document', 'video', 'audio'];
        $imageTypes = ['jpeg', 'png', 'webp', 'gif'];
        $documentTypes = ['pdf', 'doc', 'docx', 'txt'];

        for ($i = 1; $i <= 50; $i++) {
            $type = $mediaTypes[array_rand($mediaTypes)];

            $mediaData = [
                'name' => "sample-{$type}-{$i}",
                'type' => $type,
                'size' => rand(1024, 5242880), // 1KB to 5MB
                'uploaded_by' => User::inRandomOrder()->first()?->id ?? 1,
            ];

            if ($type === 'image') {
                $extension = $imageTypes[array_rand($imageTypes)];
                $mediaData['mime_type'] = "image/{$extension}";
                $mediaData['path'] = "/storage/media/images/sample-image-{$i}.{$extension}";
                $mediaData['width'] = rand(800, 1920);
                $mediaData['height'] = rand(600, 1080);
                $mediaData['alt_text'] = "Sample image {$i} for testing purposes";
            } elseif ($type === 'document') {
                $extension = $documentTypes[array_rand($documentTypes)];
                $mediaData['mime_type'] = "application/{$extension}";
                $mediaData['path'] = "/storage/media/documents/sample-document-{$i}.{$extension}";
            } elseif ($type === 'video') {
                $mediaData['mime_type'] = 'video/mp4';
                $mediaData['path'] = "/storage/media/videos/sample-video-{$i}.mp4";
                $mediaData['metadata'] = ['duration' => rand(30, 600)]; // 30 seconds to 10 minutes
            } else { // audio
                $mediaData['mime_type'] = 'audio/mp3';
                $mediaData['path'] = "/storage/media/audio/sample-audio-{$i}.mp3";
                $mediaData['metadata'] = ['duration' => rand(60, 300)]; // 1 to 5 minutes
            }

            Media::factory()->create($mediaData);
        }

        $this->command->info('✅ Media files created successfully!');
    }

    /**
     * Get users who can create content
     */
    private function getContentCreators(): array
    {
        // Get users with content creation roles
        $users = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['Super Admin', 'Editor', 'Author', 'Contributor']);
        })->get();

        if ($users->isEmpty()) {
            // Fallback to any available users
            $users = User::limit(5)->get();
        }

        return $users->toArray();
    }

    /**
     * Seed posts
     */
    private function seedPosts(array $users): void
    {
        $this->command->info('📝 Creating 50 posts...');

        $categories = Category::all();
        $tags = Tag::all();
        $mediaImages = Media::where('type', 'image')->get();

        // Post statuses with weights (more published posts)
        $statuses = [
            'published' => 30, // 60% published
            'draft' => 12,     // 24% draft
            'scheduled' => 6,  // 12% scheduled
            'archived' => 2,   // 4% archived
        ];

        $statusArray = [];
        foreach ($statuses as $status => $count) {
            $statusArray = array_merge($statusArray, array_fill(0, $count, $status));
        }

        for ($i = 1; $i <= 50; $i++) {
            $user = $users[array_rand($users)];
            $category = $categories->random();
            $status = $statusArray[array_rand($statusArray)];

            $postData = [
                'user_id' => $user['id'],
                'category_id' => $category->id,
                'status' => $status,
                'is_featured' => rand(1, 10) <= 2, // 20% featured
                'allow_comments' => rand(1, 10) <= 8, // 80% allow comments
                'is_sticky' => rand(1, 10) <= 1, // 10% sticky
                'views_count' => rand(10, 5000),
                'sort_order' => $i,
            ];

            // Set dates based on status
            if ($status === 'published') {
                $postData['published_at'] = now()->subDays(rand(1, 365));
            } elseif ($status === 'scheduled') {
                $postData['scheduled_at'] = now()->addDays(rand(1, 30));
            }

            // Add featured image (70% chance)
            if (rand(1, 10) <= 7 && $mediaImages->isNotEmpty()) {
                $postData['featured_image_id'] = $mediaImages->random()->id;
            }

            $post = Post::factory()->create($postData);

            // Attach random tags (1-5 tags per post)
            $randomTags = $tags->random(rand(1, min(5, $tags->count())));
            $post->tags()->attach($randomTags->pluck('id'));

            // Update comments count for published posts
            if ($status === 'published') {
                $post->update(['comments_count' => rand(0, 50)]);
            }
        }

        $this->command->info('✅ Posts created successfully!');
    }

    /**
     * Seed comments
     */
    private function seedComments(array $users): void
    {
        $this->command->info('💬 Creating comments...');

        $publishedPosts = Post::where('status', 'published')->get();

        if ($publishedPosts->isEmpty()) {
            $this->command->warn('⚠️  No published posts found, skipping comments.');

            return;
        }

        $commentCount = 0;

        foreach ($publishedPosts as $post) {
            // Random number of comments per post (0-10)
            $numComments = rand(0, 10);

            for ($i = 0; $i < $numComments; $i++) {
                $user = $users[array_rand($users)];

                $comment = Comment::factory()->create([
                    'post_id' => $post->id,
                    'user_id' => rand(1, 10) <= 7 ? $user['id'] : null, // 70% registered users, 30% guests
                    'status' => rand(1, 10) <= 8 ? 'approved' : 'pending', // 80% approved
                    'likes_count' => rand(0, 20),
                ]);

                $commentCount++;

                // Sometimes add replies (30% chance)
                if (rand(1, 10) <= 3) {
                    $numReplies = rand(1, 3);

                    for ($j = 0; $j < $numReplies; $j++) {
                        $replyUser = $users[array_rand($users)];

                        Comment::factory()->create([
                            'post_id' => $post->id,
                            'parent_id' => $comment->id,
                            'user_id' => rand(1, 10) <= 6 ? $replyUser['id'] : null,
                            'status' => 'approved',
                            'likes_count' => rand(0, 10),
                        ]);

                        $commentCount++;
                    }
                }
            }
        }

        $this->command->info("✅ {$commentCount} comments created successfully!");
    }

    /**
     * Display seeding statistics
     */
    private function displayStats(): void
    {
        $this->command->info('');
        $this->command->info('📊 Seeding Statistics:');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('📁 Categories: ' . Category::count());
        $this->command->info('🏷️  Tags: ' . Tag::count());
        $this->command->info('🖼️  Media Files: ' . Media::count());
        $this->command->info('📝 Posts: ' . Post::count());
        $this->command->info('   └─ Published: ' . Post::where('status', 'published')->count());
        $this->command->info('   └─ Draft: ' . Post::where('status', 'draft')->count());
        $this->command->info('   └─ Scheduled: ' . Post::where('status', 'scheduled')->count());
        $this->command->info('   └─ Featured: ' . Post::where('is_featured', true)->count());
        $this->command->info('💬 Comments: ' . Comment::count());
        $this->command->info('   └─ Approved: ' . Comment::where('status', 'approved')->count());
        $this->command->info('   └─ Pending: ' . Comment::where('status', 'pending')->count());
        $this->command->info('   └─ Replies: ' . Comment::whereNotNull('parent_id')->count());
        $this->command->info('👥 Users: ' . User::count());
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('');
    }
}
