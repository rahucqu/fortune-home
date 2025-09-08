<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\AnalyticsService;
use Illuminate\Console\Command;

class RefreshAnalyticsCache extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'analytics:refresh-cache {--clear : Only clear the cache without regenerating}';

    /**
     * The console command description.
     */
    protected $description = 'Refresh analytics cache data';

    public function __construct(
        private readonly AnalyticsService $analyticsService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('clear')) {
            $this->info('Clearing analytics cache...');
            $this->analyticsService->clearCache();
            $this->info('Analytics cache cleared successfully!');

            return self::SUCCESS;
        }

        $this->info('Refreshing analytics cache...');

        // Clear existing cache
        $this->analyticsService->clearCache();

        // Regenerate cache by calling the methods
        $this->line('Regenerating dashboard stats...');
        $this->analyticsService->getDashboardStats();

        $this->line('Regenerating monthly post stats...');
        $this->analyticsService->getMonthlyPostStats();

        $this->line('Regenerating content distribution...');
        $this->analyticsService->getContentDistribution();

        $this->line('Regenerating top performing content...');
        $this->analyticsService->getTopPerformingContent();

        $this->info('Analytics cache refreshed successfully!');

        return self::SUCCESS;
    }
}
