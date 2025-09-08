<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommentRequest;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CommentController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Comment::query()
            ->with(['post:id,title,slug', 'user:id,name,email', 'approvedBy:id,name'])
            ->latest();

        // Filter by status
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        // Filter by post
        if ($request->filled('post_id')) {
            $query->byPost($request->post_id);
        }

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by user type
        if ($request->filled('user_type')) {
            if ($request->user_type === 'registered') {
                $query->fromRegisteredUsers();
            } elseif ($request->user_type === 'guest') {
                $query->fromGuests();
            }
        }

        $comments = $query->paginate(15);

        $stats = [
            'total' => Comment::count(),
            'pending' => Comment::pending()->count(),
            'approved' => Comment::approved()->count(),
            'rejected' => Comment::rejected()->count(),
            'spam' => Comment::spam()->count(),
        ];

        return Inertia::render('Admin/Comments/Index', [
            'comments' => $comments,
            'stats' => $stats,
            'filters' => $request->only(['status', 'post_id', 'search', 'user_type']),
            'posts' => Post::select('id', 'title')->get(),
        ]);
    }

    public function show(Comment $comment): Response
    {
        $comment->load([
            'post:id,title,slug',
            'user:id,name,email',
            'approvedBy:id,name',
            'parent.user:id,name',
            'replies.user:id,name',
            'replies.approvedBy:id,name',
        ]);

        return Inertia::render('Admin/Comments/Show', [
            'comment' => $comment,
        ]);
    }

    public function approve(Comment $comment)
    {
        $comment->approve();

        return back()->with('success', 'Comment approved successfully.');
    }

    public function reject(Comment $comment)
    {
        $comment->reject();

        return back()->with('success', 'Comment rejected successfully.');
    }

    public function spam(Comment $comment)
    {
        $comment->markAsSpam();

        return back()->with('success', 'Comment marked as spam.');
    }

    public function toggleFeatured(Comment $comment)
    {
        $comment->toggleFeatured();

        $message = $comment->is_featured 
            ? 'Comment featured successfully.' 
            : 'Comment unfeatured successfully.';

        return back()->with('success', $message);
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:approve,reject,spam,delete',
            'comment_ids' => 'required|array',
            'comment_ids.*' => 'exists:comments,id',
        ]);

        $comments = Comment::whereIn('id', $request->comment_ids);

        match ($request->action) {
            'approve' => $comments->get()->each->approve(),
            'reject' => $comments->get()->each->reject(),
            'spam' => $comments->get()->each->markAsSpam(),
            'delete' => $comments->delete(),
        };

        $count = count($request->comment_ids);
        $action = ucfirst($request->action);
        
        return back()->with('success', "{$count} comments {$action}d successfully.");
    }

    public function destroy(Comment $comment)
    {
        $comment->delete();

        return back()->with('success', 'Comment deleted successfully.');
    }

    public function moderate(Request $request): Response
    {
        $pendingComments = Comment::pending()
            ->with(['post:id,title,slug', 'user:id,name,email'])
            ->latest()
            ->paginate(10);

        $recentActivity = Comment::query()
            ->whereIn('status', ['approved', 'rejected', 'spam'])
            ->with(['post:id,title,slug', 'user:id,name', 'approvedBy:id,name'])
            ->latest('updated_at')
            ->limit(10)
            ->get();

        return Inertia::render('Admin/Comments/Moderate', [
            'pendingComments' => $pendingComments,
            'recentActivity' => $recentActivity,
        ]);
    }

    public function analytics(): Response
    {
        $stats = [
            'total_comments' => Comment::count(),
            'approved_comments' => Comment::approved()->count(),
            'pending_comments' => Comment::pending()->count(),
            'rejected_comments' => Comment::rejected()->count(),
            'spam_comments' => Comment::spam()->count(),
            'guest_comments' => Comment::fromGuests()->count(),
            'registered_comments' => Comment::fromRegisteredUsers()->count(),
            'featured_comments' => Comment::featured()->count(),
        ];

        // Comments by day for the last 30 days
        $commentsByDay = Comment::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Most active posts by comment count
        $mostActivePosts = Post::withCount('comments')
            ->having('comments_count', '>', 0)
            ->orderByDesc('comments_count')
            ->limit(10)
            ->get(['id', 'title', 'slug', 'comments_count']);

        // Top commenters (registered users)
        $topCommenters = Comment::fromRegisteredUsers()
            ->approved()
            ->selectRaw('user_id, COUNT(*) as comment_count')
            ->with('user:id,name,email')
            ->groupBy('user_id')
            ->orderByDesc('comment_count')
            ->limit(10)
            ->get();

        return Inertia::render('Admin/Comments/Analytics', [
            'stats' => $stats,
            'commentsByDay' => $commentsByDay,
            'mostActivePosts' => $mostActivePosts,
            'topCommenters' => $topCommenters,
        ]);
    }
}
