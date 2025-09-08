<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Media;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Console\Command;

class ShowContentStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'content:stats {--sample : Show sample content}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display blog content statistics';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->displayStats();

        if ($this->option('sample')) {
            $this->displaySampleContent();
        }
    }

    /**
     * Display content statistics
     */
    private function displayStats(): void
    {
        $this->info('📊 BLOG CONTENT STATISTICS');
        $this->info('==========================');
        $this->newLine();

        // Categories
        $this->line('📁 Categories: ' . Category::count());
        $this->line('   └─ Active: ' . Category::where('is_active', true)->count());
        $this->line('   └─ Inactive: ' . Category::where('is_active', false)->count());

        // Tags
        $this->line('🏷️  Tags: ' . Tag::count());
        $this->line('   └─ Active: ' . Tag::where('is_active', true)->count());
        $this->line('   └─ Inactive: ' . Tag::where('is_active', false)->count());

        // Media
        $this->line('🖼️  Media Files: ' . Media::count());
        $this->line('   └─ Images: ' . Media::where('type', 'image')->count());
        $this->line('   └─ Documents: ' . Media::where('type', 'document')->count());
        $this->line('   └─ Videos: ' . Media::where('type', 'video')->count());
        $this->line('   └─ Audio: ' . Media::where('type', 'audio')->count());

        // Posts
        $this->line('📝 Posts: ' . Post::count());
        $this->line('   └─ Published: ' . Post::where('status', 'published')->count());
        $this->line('   └─ Draft: ' . Post::where('status', 'draft')->count());
        $this->line('   └─ Scheduled: ' . Post::where('status', 'scheduled')->count());
        $this->line('   └─ Archived: ' . Post::where('status', 'archived')->count());
        $this->line('   └─ Featured: ' . Post::where('is_featured', true)->count());
        $this->line('   └─ With Comments: ' . Post::where('allow_comments', true)->count());

        // Comments
        $this->line('💬 Comments: ' . Comment::count());
        $this->line('   └─ Approved: ' . Comment::where('status', 'approved')->count());
        $this->line('   └─ Pending: ' . Comment::where('status', 'pending')->count());
        $this->line('   └─ Rejected: ' . Comment::where('status', 'rejected')->count());
        $this->line('   └─ Replies: ' . Comment::whereNotNull('parent_id')->count());

        // Users
        $this->line('👥 Users: ' . User::count());
        $roles = User::with('roles')->get()->groupBy(function ($user) {
            return $user->roles->first()?->name ?? 'No Role';
        });
        foreach ($roles as $role => $users) {
            $this->line("   └─ {$role}: " . $users->count());
        }

        $this->newLine();
        $this->comment('💡 Use --sample flag to see sample content');
    }

    /**
     * Display sample content
     */
    private function displaySampleContent(): void
    {
        $this->newLine();
        $this->info('📋 SAMPLE CONTENT');
        $this->info('=================');
        $this->newLine();

        // Sample Categories
        $this->line('📁 Sample Categories:');
        $categories = Category::take(5)->get(['name', 'slug', 'is_active']);
        foreach ($categories as $category) {
            $status = $category->is_active ? '✅' : '❌';
            $this->line("   {$status} {$category->name} ({$category->slug})");
        }
        $this->newLine();

        // Sample Tags
        $this->line('🏷️  Sample Tags:');
        $tags = Tag::take(5)->get(['name', 'color']);
        foreach ($tags as $tag) {
            $this->line("   🔖 {$tag->name} <fg={$this->getColorName($tag->color)}>●</> ({$tag->color})");
        }
        $this->newLine();

        // Sample Posts
        $this->line('📝 Sample Posts:');
        $posts = Post::with(['category', 'user', 'tags'])->take(5)->get();
        foreach ($posts as $post) {
            $status = match ($post->status) {
                'published' => '<fg=green>PUBLISHED</>',
                'draft' => '<fg=yellow>DRAFT</>',
                'scheduled' => '<fg=blue>SCHEDULED</>',
                'archived' => '<fg=red>ARCHIVED</>',
                default => $post->status
            };
            $featured = $post->is_featured ? ' ⭐' : '';
            $this->line("   📄 {$post->title} [{$status}]{$featured}");
            $this->line("      👤 by {$post->user->name} | 📁 " . ($post->category?->name ?? 'No category'));
            if ($post->tags->isNotEmpty()) {
                $tagNames = $post->tags->pluck('name')->implode(', ');
                $this->line("      🏷️  Tags: {$tagNames}");
            }
        }
        $this->newLine();

        // Sample Media
        $this->line('🖼️  Sample Media:');
        $media = Media::take(5)->get(['name', 'type', 'mime_type', 'size']);
        foreach ($media as $item) {
            $icon = match ($item->type) {
                'image' => '🖼️',
                'video' => '🎥',
                'audio' => '🎵',
                'document' => '📄',
                default => '📁'
            };
            $size = $this->formatBytes($item->size);
            $this->line("   {$icon} {$item->name} ({$item->mime_type}) - {$size}");
        }
        $this->newLine();

        // Sample Comments
        $this->line('💬 Sample Comments:');
        $comments = Comment::with(['post', 'user'])->whereNull('parent_id')->take(3)->get();
        foreach ($comments as $comment) {
            $status = match ($comment->status) {
                'approved' => '<fg=green>✓</>',
                'pending' => '<fg=yellow>⏳</>',
                'rejected' => '<fg=red>✗</>',
                default => $comment->status
            };
            $author = $comment->user ? $comment->user->name : $comment->author_name;
            $this->line("   💭 \"{$comment->content}\" [{$status}]");
            $this->line("      👤 by {$author} on \"{$comment->post->title}\"");

            // Show replies if any
            $replies = Comment::where('parent_id', $comment->id)->count();
            if ($replies > 0) {
                $this->line("      💬 {$replies} " . ($replies === 1 ? 'reply' : 'replies'));
            }
        }
    }

    /**
     * Get color name for CLI display
     */
    private function getColorName(string $hex): string
    {
        $colors = [
            '#3B82F6' => 'blue',
            '#EF4444' => 'red',
            '#10B981' => 'green',
            '#F59E0B' => 'yellow',
            '#8B5CF6' => 'magenta',
            '#F97316' => 'red',
            '#06B6D4' => 'cyan',
            '#84CC16' => 'green',
        ];

        return $colors[$hex] ?? 'white';
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = floor(log($bytes, 1024));

        return round($bytes / (1024 ** $power), 2) . ' ' . $units[$power];
    }
}
