<?php

declare(strict_types=1);

use App\Http\Controllers\Frontend\AgentController;
use App\Http\Controllers\Frontend\BlogController;
use App\Http\Controllers\Frontend\ContactController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\PropertyController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\PropertyTourController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/agents', [AgentController::class, 'index'])->name('agents');
Route::get('/agent/{agent_slug}', [AgentController::class, 'show'])->name('agent.show');
Route::get('/agent/{agent_slug}/properties', [AgentController::class, 'properties'])->name('agent.properties');

Route::get('/blog', [BlogController::class, 'index'])->name('blog');
Route::get('/blog/{post_slug}', [BlogController::class, 'show'])->name('blog.show');

Route::get('/properties', [PropertyController::class, 'index'])->name('properties');
Route::get('/property/{property_slug}', [PropertyController::class, 'show'])->name('property.show');

Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware(['throttle:3,1'])  // Limit to 3 submissions per minute
    ->name('contact.store');

Route::post('/properties/schedule-tour', [PropertyTourController::class, 'store'])
    ->middleware(['throttle:5,10'])
    ->name('properties.schedule-tour');

// Review routes
Route::prefix('review/{morph_type}/{morph_id}')->group(function () {
    Route::get('/', [ReviewController::class, 'index'])->name('reviews.index');
    Route::get('/create', [ReviewController::class, 'create'])->name('reviews.create');
    Route::post('/', [ReviewController::class, 'store'])->name('reviews.store');
});

// Message routes - public (for sending messages to agents/properties)
Route::prefix('message/{morph_type}/{morph_id}')->group(function () {
    Route::post('/', [MessageController::class, 'store'])->name('messages.store');
});

require __DIR__.'/auth.php';
