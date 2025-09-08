<?php

declare(strict_types=1);

use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified', 'restrict-admin'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    // Teams routes - only create and switch
    Route::get('teams/create', [TeamController::class, 'create'])->name('teams.create');
    Route::post('teams', [TeamController::class, 'store'])->name('teams.store');
    Route::post('teams/{team}/switch', [TeamController::class, 'switch'])->name('teams.switch');

    // Team members and invitations
    Route::get('teams/members', [TeamController::class, 'members'])->name('teams.members');
    Route::get('teams/invitations', [TeamController::class, 'invitations'])->name('teams.invitations');

    // Team invitations - invite members
    Route::get('teams/invite', [TeamController::class, 'invite'])->name('teams.invite');
    Route::post('teams/invitations', [TeamController::class, 'storeInvitation'])->name('teams.invitations.store');

    // Team member management
    Route::delete('teams/members/{user}', [TeamController::class, 'removeMember'])->name('teams.members.destroy');
    Route::put('teams/members/{user}', [TeamController::class, 'updateMemberRole'])->name('teams.members.update');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
