<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TeamController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->get('search', '');

        $teams = Team::query()
            ->with(['owner', 'users'])
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhereHas('owner', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            })
            ->withCount('users')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Teams/Index', [
            'teams' => $teams,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function show(Team $team): Response
    {
        $team->load([
            'owner',
            'users' => function ($query) {
                $query->with('roles');
            },
            'invitations',
        ]);

        return Inertia::render('Admin/Teams/Show', [
            'team' => $team,
        ]);
    }

    public function edit(Team $team): Response
    {
        $team->load('owner');
        $users = User::where('id', '!=', $team->owner->id)->get();

        return Inertia::render('Admin/Teams/Edit', [
            'team' => $team,
            'users' => $users,
        ]);
    }

    public function update(Request $request, Team $team): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'owner_id' => 'required|exists:users,id',
        ]);

        $team->update([
            'name' => $validated['name'],
            'user_id' => $validated['owner_id'],
        ]);

        return redirect()->route('admin.teams.index')
            ->with('success', 'Team updated successfully.');
    }

    public function destroy(Team $team): RedirectResponse
    {
        $team->delete();

        return redirect()->route('admin.teams.index')
            ->with('success', 'Team deleted successfully.');
    }

    public function addMember(Request $request, Team $team): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|string|in:member,admin',
        ]);

        $user = User::findOrFail($validated['user_id']);

        if ($team->users()->where('user_id', $user->id)->exists()) {
            return redirect()->back()
                ->with('error', 'User is already a member of this team.');
        }

        $team->users()->attach($user->id, ['role' => $validated['role']]);

        return redirect()->back()
            ->with('success', 'Member added successfully.');
    }

    public function removeMember(Request $request, Team $team): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($validated['user_id']);

        if ($user->id === $team->user_id) {
            return redirect()->back()
                ->with('error', 'Cannot remove team owner.');
        }

        $team->users()->detach($user->id);

        return redirect()->back()
            ->with('success', 'Member removed successfully.');
    }

    public function updateMemberRole(Request $request, Team $team): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|string|in:member,admin',
        ]);

        $user = User::findOrFail($validated['user_id']);

        if ($user->id === $team->user_id) {
            return redirect()->back()
                ->with('error', 'Cannot change team owner role.');
        }

        $team->users()->updateExistingPivot($user->id, ['role' => $validated['role']]);

        return redirect()->back()
            ->with('success', 'Member role updated successfully.');
    }
}
