<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostRequest;
use App\Models\Category;
use App\Models\Media;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->get('search', '');
        $status = $request->get('status', '');
        $category = $request->get('category', '');
        $author = $request->get('author', '');

        $posts = Post::query()
            ->with(['user:id,name', 'category:id,name', 'featuredImage:id,name,path', 'tags:id,name,color'])
            ->when($search, fn ($query) => $query->search($search))
            ->when($status, fn ($query) => $query->byStatus($status))
            ->when($category, fn ($query) => $query->byCategory($category))
            ->when($author, fn ($query) => $query->byAuthor($author))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => Post::count(),
            'published' => Post::published()->count(),
            'draft' => Post::draft()->count(),
            'featured' => Post::featured()->count(),
        ];

        return Inertia::render('Admin/Posts/Index', [
            'posts' => $posts,
            'stats' => $stats,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'category' => $category,
                'author' => $author,
            ],
            'categories' => Category::select('id', 'name')->get(),
            'authors' => User::select('id', 'name')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Posts/Create', [
            'categories' => Category::active()->orderBy('name')->get(['id', 'name']),
            'tags' => Tag::active()->orderBy('name')->get(['id', 'name', 'color']),
            'media' => Media::images()->latest()->limit(50)->get(['id', 'name', 'path', 'alt_text']),
        ]);
    }

    public function store(PostRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Handle tag IDs separately
        $tagIds = $validated['tag_ids'] ?? [];
        unset($validated['tag_ids']);

        // Set the author
        $validated['user_id'] = Auth::id();

        // Auto-publish if status is published and no published_at is set
        if ($validated['status'] === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $post = Post::create($validated);

        // Sync tags
        if (! empty($tagIds)) {
            $post->tags()->sync($tagIds);
        }

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post created successfully.');
    }

    public function show(Post $post): Response
    {
        $this->authorize('view', $post);

        $post->load(['user:id,name', 'category:id,name', 'featuredImage:id,name,path', 'tags:id,name,color']);

        return Inertia::render('Admin/Posts/Show', [
            'post' => $post,
        ]);
    }

    public function edit(Post $post): Response
    {
        $this->authorize('update', $post);

        $post->load(['tags:id']);

        return Inertia::render('Admin/Posts/Edit', [
            'post' => $post,
            'categories' => Category::active()->orderBy('name')->get(['id', 'name']),
            'tags' => Tag::active()->orderBy('name')->get(['id', 'name', 'color']),
            'media' => Media::images()->latest()->limit(50)->get(['id', 'name', 'path', 'alt_text']),
            'selectedTagIds' => $post->tags->pluck('id'),
        ]);
    }

    public function update(PostRequest $request, Post $post): RedirectResponse
    {
        $this->authorize('update', $post);

        $validated = $request->validated();

        // Handle tag IDs separately
        $tagIds = $validated['tag_ids'] ?? [];
        unset($validated['tag_ids']);

        // Auto-publish if status is published and no published_at is set
        if ($validated['status'] === 'published' && empty($validated['published_at']) && $post->status !== 'published') {
            $validated['published_at'] = now();
        }

        $post->update($validated);

        // Sync tags
        $post->tags()->sync($tagIds);

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post updated successfully.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->authorize('delete', $post);

        $post->tags()->detach(); // Remove tag relationships
        $post->delete();

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post deleted successfully.');
    }

    // Additional actions
    public function publish(Post $post): RedirectResponse
    {
        $this->authorize('publish', $post);

        $post->publish();

        return redirect()->back()
            ->with('success', 'Post published successfully.');
    }

    public function unpublish(Post $post): RedirectResponse
    {
        $this->authorize('publish', $post);

        $post->unpublish();

        return redirect()->back()
            ->with('success', 'Post unpublished successfully.');
    }

    public function toggleFeatured(Post $post): RedirectResponse
    {
        $this->authorize('update', $post);

        $post->update(['is_featured' => ! $post->is_featured]);

        $message = $post->is_featured ? 'Post marked as featured.' : 'Post removed from featured.';

        return redirect()->back()
            ->with('success', $message);
    }

    public function duplicate(Post $post): RedirectResponse
    {
        $newPost = $post->replicate();
        $newPost->title = $post->title . ' (Copy)';
        $newPost->slug = Post::generateUniqueSlug($newPost->title);
        $newPost->status = 'draft';
        $newPost->published_at = null;
        $newPost->is_featured = false;
        $newPost->views_count = 0;
        $newPost->comments_count = 0;
        $newPost->save();

        // Copy tag relationships
        $newPost->tags()->sync($post->tags->pluck('id'));

        return redirect()->route('admin.posts.edit', $newPost)
            ->with('success', 'Post duplicated successfully.');
    }
}
