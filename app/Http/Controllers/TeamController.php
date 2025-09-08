<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Teams\CreateTeam;
use App\Actions\Teams\InviteTeamMember;
use App\Actions\Teams\RemoveTeamMember;
use App\Actions\Teams\UpdateTeamMemberRole;
use App\Models\Team;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class TeamController extends Controller
{
    /**
     * Show the form for creating a new team.
     */
    public function create()
    {
        return Inertia::render('teams/Create');
    }

    /**
     * Store a newly created team.
     */
    public function store(Request $request)
    {
        $action = new CreateTeam();

        $team = $action->create($request->user(), [
            'name' => $request->name,
        ]);

        return redirect()->route('dashboard')->with('success', 'Team created successfully!');
    }

    /**
     * Switch the user's current team.
     */
    public function switch(Request $request, Team $team)
    {
        $user = $request->user();

        abort_unless($user->belongsToTeam($team), 403);

        $user->switchTeam($team);

        return redirect()->back();
    }

    /**
     * Display team members with pagination.
     */
    public function members()
    {
        $user = Auth::user();

        if (! $user->current_team_id) {
            return redirect()->route('dashboard')->with('error', 'Please select a team first.');
        }

        $currentTeam = $user->currentTeam;

        if (! $currentTeam) {
            return redirect()->route('dashboard')->with('error', 'Team not found.');
        }

        // Check if user belongs to the current team
        if (! $user->belongsToTeam($currentTeam)) {
            return redirect()->route('dashboard')->with('error', 'You do not have access to this team.');
        }

        // Get team members with owner and regular members combined
        $teamMembers = $currentTeam->users()->withPivot('role', 'created_at as joined_at')->get();

        // Add the owner to the collection with owner role
        $owner = $currentTeam->owner;
        $owner->pivot = (object) [
            'role' => 'owner',
            'joined_at' => $currentTeam->created_at,
        ];

        // Combine and paginate manually
        $allMembers = $teamMembers->prepend($owner);

        // Convert to paginated collection for consistent API
        $perPage = 15;
        $currentPage = request('page', 1);
        $total = $allMembers->count();
        $items = $allMembers->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginator = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'pageName' => 'page']
        );

        return Inertia::render('teams/members', [
            'members' => [
                'data' => $paginator->items(),
                'links' => $paginator->linkCollection()->toArray(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
            ],
            'current_team' => $currentTeam,
        ]);
    }

    /**
     * Display team invitations with pagination.
     */
    public function invitations()
    {
        $user = Auth::user();

        if (! $user->current_team_id) {
            return redirect()->route('dashboard')->with('error', 'Please select a team first.');
        }

        $currentTeam = $user->currentTeam;

        if (! $currentTeam) {
            return redirect()->route('dashboard')->with('error', 'Team not found.');
        }

        // Check if user belongs to the current team
        if (! $user->belongsToTeam($currentTeam)) {
            return redirect()->route('dashboard')->with('error', 'You do not have access to this team.');
        }

        $invitations = $currentTeam->teamInvitations()->paginate(15);

        return Inertia::render('teams/invitations', [
            'invitations' => [
                'data' => $invitations->items(),
                'links' => $invitations->linkCollection()->toArray(),
                'meta' => [
                    'current_page' => $invitations->currentPage(),
                    'last_page' => $invitations->lastPage(),
                    'per_page' => $invitations->perPage(),
                    'total' => $invitations->total(),
                    'from' => $invitations->firstItem(),
                    'to' => $invitations->lastItem(),
                ],
            ],
            'current_team' => $currentTeam,
        ]);
    }

    /**
     * Show the form for inviting a team member.
     */
    public function invite()
    {
        $user = Auth::user();

        if (! $user->current_team_id) {
            return redirect()->route('dashboard')->with('error', 'Please select a team first.');
        }

        $currentTeam = $user->currentTeam;

        if (! $currentTeam) {
            return redirect()->route('dashboard')->with('error', 'Team not found.');
        }

        // Check if user belongs to the current team (and has permission to invite)
        if (! $user->belongsToTeam($currentTeam)) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to invite members to this team.');
        }

        return Inertia::render('teams/invite', [
            'current_team' => $currentTeam,
        ]);
    }

    /**
     * Store a team invitation.
     */
    public function storeInvitation(Request $request)
    {
        $user = Auth::user();

        if (! $user->current_team_id) {
            return redirect()->route('dashboard')->with('error', 'Please select a team first.');
        }

        $currentTeam = $user->currentTeam;

        if (! $currentTeam) {
            return redirect()->route('dashboard')->with('error', 'Team not found.');
        }

        // Check if user belongs to the current team
        if (! $user->belongsToTeam($currentTeam)) {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to invite members to this team.');
        }

        $action = new InviteTeamMember();

        try {
            $action->invite($user, $currentTeam, $request->email, $request->role);

            return redirect()->route('teams.invitations')->with('success', 'Invitation sent successfully!');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }

    /**
     * Remove a team member.
     */
    public function removeMember(Request $request, User $user)
    {
        $currentUser = Auth::user();

        if (! $currentUser->current_team_id) {
            return redirect()->route('dashboard')->with('error', 'Please select a team first.');
        }

        $currentTeam = $currentUser->currentTeam;

        if (! $currentTeam) {
            return redirect()->route('dashboard')->with('error', 'Team not found.');
        }

        $action = new RemoveTeamMember();

        try {
            $action->remove($currentUser, $currentTeam, $user);

            return redirect()->route('teams.members')->with('success', 'Team member removed successfully!');
        } catch (Exception $e) {
            return back()->withErrors(['general' => $e->getMessage()]);
        }
    }

    /**
     * Update a team member's role.
     */
    public function updateMemberRole(Request $request, User $user)
    {
        $currentUser = Auth::user();

        if (! $currentUser->current_team_id) {
            return redirect()->route('dashboard')->with('error', 'Please select a team first.');
        }

        $currentTeam = $currentUser->currentTeam;

        if (! $currentTeam) {
            return redirect()->route('dashboard')->with('error', 'Team not found.');
        }

        $action = new UpdateTeamMemberRole();

        try {
            $action->update($currentUser, $currentTeam, $user->id, $request->role);

            return redirect()->route('teams.members')->with('success', 'Team member role updated successfully!');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }
}
