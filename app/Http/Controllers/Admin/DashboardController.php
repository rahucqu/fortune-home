<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use App\Services\AnalyticsService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly AnalyticsService $analyticsService
    ) {}

    public function index(): Response
    {
        // Get blog analytics
        $blogStats = $this->analyticsService->getDashboardStats();
        $monthlyStats = $this->analyticsService->getMonthlyPostStats();
        $pendingComments = $this->analyticsService->getRecentPendingComments(5);
        $contentDistribution = $this->analyticsService->getContentDistribution();
        $recentActivity = $this->analyticsService->getRecentActivity(10);
        $topContent = $this->analyticsService->getTopPerformingContent();

        // Legacy team/user stats (keeping for backward compatibility)
        $legacyStats = [
            'total_users' => User::count(),
            'total_teams' => Team::count(),
            'users_this_month' => User::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'teams_this_month' => Team::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        $recent_users = User::with('roles')
            ->latest()
            ->limit(5)
            ->get();

        $recent_teams = Team::with('owner')
            ->latest()
            ->limit(5)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            // Blog analytics
            'blog_stats' => $blogStats,
            'monthly_post_stats' => $monthlyStats,
            'pending_comments' => $pendingComments,
            'content_distribution' => $contentDistribution,
            'recent_activity' => $recentActivity,
            'top_performing_content' => $topContent,

            // Legacy data
            'stats' => $legacyStats,
            'recent_users' => $recent_users,
            'recent_teams' => $recent_teams,
        ]);
    }
}
