<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\BlogComment;
use App\Models\BlogEditRequest;
use App\Models\BlogPost;
use App\Policies\BlogCommentPolicy;
use App\Policies\BlogEditRequestPolicy;
use App\Policies\BlogPostPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     */
    protected array $policies = [
        BlogPost::class => BlogPostPolicy::class,
        BlogComment::class => BlogCommentPolicy::class,
        BlogEditRequest::class => BlogEditRequestPolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }

    /**
     * Register the application's policies.
     */
    public function registerPolicies(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
}
