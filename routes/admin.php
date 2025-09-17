<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AgentController;
use App\Http\Controllers\Admin\AmenityController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CommentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\PropertyController;
use App\Http\Controllers\Admin\PropertyTypeController;
use App\Http\Controllers\Admin\SeoSettingController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\TeamController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])
        ->middleware('permission:view dashboard')
        ->name('dashboard');

    // User Management (existing admin permissions)
    Route::resource('users', UserController::class)->except(['create', 'store']);
    Route::post('users/{user}/assign-role', [UserController::class, 'assignRole'])->name('users.assign-role');
    Route::delete('users/{user}/remove-role', [UserController::class, 'removeRole'])->name('users.remove-role');

    // Team Management (existing admin permissions)
    Route::resource('teams', TeamController::class)->except(['create', 'store']);
    Route::post('teams/{team}/add-member', [TeamController::class, 'addMember'])->name('teams.add-member');
    Route::delete('teams/{team}/remove-member', [TeamController::class, 'removeMember'])->name('teams.remove-member');
    Route::patch('teams/{team}/update-member-role', [TeamController::class, 'updateMemberRole'])->name('teams.update-member-role');

    // Blog Management - Categories
    Route::middleware('permission:view categories')->group(function () {
        Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
    });
    Route::middleware('permission:create categories')->group(function () {
        Route::get('categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
    });
    Route::middleware('permission:edit categories')->group(function () {
        Route::get('categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::patch('categories/{category}', [CategoryController::class, 'update']);
    });
    Route::middleware('permission:delete categories')->group(function () {
        Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    });

    // Blog Management - Tags
    Route::middleware('permission:view tags')->group(function () {
        Route::get('tags', [TagController::class, 'index'])->name('tags.index');
    });
    Route::middleware('permission:create tags')->group(function () {
        Route::get('tags/create', [TagController::class, 'create'])->name('tags.create');
        Route::post('tags', [TagController::class, 'store'])->name('tags.store');
    });
    Route::middleware('permission:view tags')->group(function () {
        Route::get('tags/{tag}', [TagController::class, 'show'])->name('tags.show');
    });
    Route::middleware('permission:edit tags')->group(function () {
        Route::get('tags/{tag}/edit', [TagController::class, 'edit'])->name('tags.edit');
        Route::put('tags/{tag}', [TagController::class, 'update'])->name('tags.update');
        Route::patch('tags/{tag}', [TagController::class, 'update']);
    });
    Route::middleware('permission:delete tags')->group(function () {
        Route::delete('tags/{tag}', [TagController::class, 'destroy'])->name('tags.destroy');
    });

    // Blog Management - Media
    Route::middleware('permission:view media')->group(function () {
        Route::get('media', [MediaController::class, 'index'])->name('media.index');
        Route::get('media/{media}', [MediaController::class, 'show'])->name('media.show');
    });
    Route::middleware('permission:upload media')->group(function () {
        Route::get('media/create', [MediaController::class, 'create'])->name('media.create');
        Route::post('media', [MediaController::class, 'store'])->name('media.store');
    });
    Route::middleware('permission:edit media')->group(function () {
        Route::get('media/{media}/edit', [MediaController::class, 'edit'])->name('media.edit');
        Route::put('media/{media}', [MediaController::class, 'update'])->name('media.update');
        Route::patch('media/{media}', [MediaController::class, 'update']);
    });
    Route::middleware('permission:delete media')->group(function () {
        Route::delete('media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');
    });

    // Blog Management - Comments
    Route::middleware('permission:view comments')->group(function () {
        Route::get('comments', [CommentController::class, 'index'])->name('comments.index');
        Route::get('comments/{comment}', [CommentController::class, 'show'])->name('comments.show');
    });
    Route::middleware('permission:edit comments')->group(function () {
        Route::get('comments/{comment}/edit', [CommentController::class, 'edit'])->name('comments.edit');
        Route::put('comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
        Route::patch('comments/{comment}', [CommentController::class, 'update']);
        Route::patch('comments/{comment}/approve', [CommentController::class, 'approve'])->name('comments.approve');
        Route::patch('comments/{comment}/reject', [CommentController::class, 'reject'])->name('comments.reject');
    });
    Route::middleware('permission:delete comments')->group(function () {
        Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    });

    Route::middleware('permission:create posts')->group(function () {
        Route::get('posts/create', [PostController::class, 'create'])->name('posts.create');
        Route::post('posts', [PostController::class, 'store'])->name('posts.store');
        Route::post('posts/{post}/duplicate', [PostController::class, 'duplicate'])->name('posts.duplicate');
    });
    Route::middleware('permission:edit posts')->group(function () {
        Route::get('posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
        Route::put('posts/{post}', [PostController::class, 'update'])->name('posts.update');
        Route::patch('posts/{post}', [PostController::class, 'update']);
        Route::patch('posts/{post}/toggle-featured', [PostController::class, 'toggleFeatured'])->name('posts.toggle-featured');
    });
    // Blog Management - Posts
    Route::middleware('permission:view posts')->group(function () {
        Route::get('posts', [PostController::class, 'index'])->name('posts.index');
        Route::get('posts/{post}', [PostController::class, 'show'])->name('posts.show');
    });
    Route::middleware('permission:delete posts')->group(function () {
        Route::delete('posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
    });
    Route::middleware('permission:publish posts')->group(function () {
        Route::patch('posts/{post}/publish', [PostController::class, 'publish'])->name('posts.publish');
        Route::patch('posts/{post}/unpublish', [PostController::class, 'unpublish'])->name('posts.unpublish');
    });

    // SEO Management
    Route::middleware('permission:manage seo')->group(function () {
        Route::get('seo-settings', [SeoSettingController::class, 'index'])->name('seo-settings.index');
        Route::get('seo-settings/create', [SeoSettingController::class, 'create'])->name('seo-settings.create');
        Route::post('seo-settings', [SeoSettingController::class, 'store'])->name('seo-settings.store');
        Route::get('seo-settings/{seoSetting}', [SeoSettingController::class, 'show'])->name('seo-settings.show');
        Route::get('seo-settings/{seoSetting}/edit', [SeoSettingController::class, 'edit'])->name('seo-settings.edit');
        Route::put('seo-settings/{seoSetting}', [SeoSettingController::class, 'update'])->name('seo-settings.update');
        Route::patch('seo-settings/{seoSetting}', [SeoSettingController::class, 'update']);
        Route::delete('seo-settings/{seoSetting}', [SeoSettingController::class, 'destroy'])->name('seo-settings.destroy');
    });

    // Property Management Routes
    Route::delete('properties/bulk-destroy', [PropertyController::class, 'bulkDestroy'])->name('properties.bulk-destroy');
    Route::resource('properties', PropertyController::class);
    Route::resource('property-types', PropertyTypeController::class);
    Route::resource('locations', LocationController::class);
    Route::resource('agents', AgentController::class);
    Route::resource('amenities', AmenityController::class);

    // Property-specific routes
    Route::middleware('permission:manage property images')->group(function () {
        Route::post('properties/{property}/images', [PropertyController::class, 'uploadImages'])->name('properties.images.upload');
        Route::delete('properties/{property}/images/{image}', [PropertyController::class, 'deleteImage'])->name('properties.images.delete');
    });
});
