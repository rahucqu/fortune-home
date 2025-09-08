<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(CommentRequest $request): RedirectResponse
    {
        $validated = $request->validatedData();
        
        // Verify the post exists and is published
        $post = Post::published()->findOrFail($validated['post_id']);
        
        // If replying to a comment, verify it exists and is approved
        if (!empty($validated['parent_id'])) {
            $parentComment = Comment::approved()->findOrFail($validated['parent_id']);
            
            // Ensure the parent comment belongs to the same post
            if ($parentComment->post_id !== $post->id) {
                return back()->withErrors(['parent_id' => 'Invalid parent comment.']);
            }
        }
        
        $comment = Comment::create($validated);
        
        // Load relationships for response
        $comment->load(['user:id,name', 'post:id,title']);
        
        return back()->with('success', 'Comment submitted successfully and is pending approval.');
    }

    public function show(Post $post, Comment $comment): JsonResponse
    {
        // Ensure comment belongs to the post and is approved
        if ($comment->post_id !== $post->id || !$comment->is_approved) {
            abort(404);
        }

        $comment->load([
            'user:id,name',
            'approvedReplies.user:id,name',
            'approvedReplies' => function ($query) {
                $query->latest();
            }
        ]);

        return response()->json(['comment' => $comment]);
    }

    public function like(Comment $comment): JsonResponse
    {
        // Only allow liking approved comments
        if (!$comment->is_approved) {
            return response()->json(['error' => 'Comment not found.'], 404);
        }

        $comment->incrementLikes();

        return response()->json([
            'likes_count' => $comment->fresh()->likes_count,
            'message' => 'Comment liked successfully.',
        ]);
    }

    public function unlike(Comment $comment): JsonResponse
    {
        // Only allow unliking approved comments
        if (!$comment->is_approved) {
            return response()->json(['error' => 'Comment not found.'], 404);
        }

        $comment->decrementLikes();

        return response()->json([
            'likes_count' => $comment->fresh()->likes_count,
            'message' => 'Comment unliked successfully.',
        ]);
    }

    public function replies(Comment $comment): JsonResponse
    {
        // Only show replies for approved comments
        if (!$comment->is_approved) {
            return response()->json(['error' => 'Comment not found.'], 404);
        }

        $replies = $comment->approvedReplies()
            ->with('user:id,name')
            ->latest()
            ->paginate(10);

        return response()->json(['replies' => $replies]);
    }

    public function loadMore(Post $post, Request $request): JsonResponse
    {
        $request->validate([
            'page' => 'sometimes|integer|min:1',
            'parent_id' => 'sometimes|exists:comments,id',
        ]);

        $query = Comment::approved()
            ->byPost($post->id)
            ->with('user:id,name')
            ->latest();

        // If parent_id is provided, get replies to that comment
        if ($request->filled('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        } else {
            // Otherwise get top-level comments
            $query->topLevel();
        }

        $comments = $query->paginate(10);

        return response()->json(['comments' => $comments]);
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        // Only allow users to delete their own comments
        if (!Auth::check() || $comment->user_id !== Auth::id()) {
            return back()->with('error', 'You can only delete your own comments.');
        }

        // Only allow deletion of pending or rejected comments
        if ($comment->is_approved) {
            return back()->with('error', 'Cannot delete approved comments.');
        }

        $comment->delete();

        return back()->with('success', 'Comment deleted successfully.');
    }
}
