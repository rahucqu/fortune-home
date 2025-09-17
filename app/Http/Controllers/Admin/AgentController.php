<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AgentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $agents = Agent::query()
            ->when($request->get('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('license_number', 'like', "%{$search}%");
            })
            ->when($request->get('status'), function ($query, $status) {
                $query->where('is_active', $status === 'active');
            })
            ->withCount('properties')
            ->orderBy($request->get('sort', 'name'), $request->get('direction', 'asc'))
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Agents/Index', [
            'agents' => $agents,
            'filters' => $request->only(['search', 'status', 'sort', 'direction']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Agents/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:agents,email',
            'phone' => 'required|string|max:20',
            'license_number' => 'nullable|string|max:100|unique:agents,license_number',
            'bio' => 'nullable|string|max:2000',
            'specializations' => 'nullable|array',
            'specializations.*' => 'string|max:100',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_active' => 'boolean',
        ]);

        // Handle photo upload if provided
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('agents', 'public');
        }

        Agent::create($validated);

        return redirect()->route('admin.agents.index')
            ->with('success', 'Agent created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Agent $agent): Response
    {
        $agent->load('properties');

        return Inertia::render('Admin/Agents/Show', [
            'agent' => $agent,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Agent $agent): Response
    {
        return Inertia::render('Admin/Agents/Edit', [
            'agent' => $agent,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Agent $agent): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:agents,email,' . $agent->id,
            'phone' => 'required|string|max:20',
            'license_number' => 'nullable|string|max:100|unique:agents,license_number,' . $agent->id,
            'bio' => 'nullable|string|max:2000',
            'specializations' => 'nullable|array',
            'specializations.*' => 'string|max:100',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'is_active' => 'boolean',
        ]);

        // Handle photo upload if provided
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($agent->photo) {
                Storage::disk('public')->delete($agent->photo);
            }
            $validated['photo'] = $request->file('photo')->store('agents', 'public');
        }

        $agent->update($validated);

        return redirect()->route('admin.agents.index')
            ->with('success', 'Agent updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Agent $agent): RedirectResponse
    {
        if ($agent->properties()->count() > 0) {
            return redirect()->route('admin.agents.index')
                ->with('error', 'Cannot delete agent with associated properties.');
        }

        // Delete photo if exists
        if ($agent->photo) {
            Storage::disk('public')->delete($agent->photo);
        }

        $agent->delete();

        return redirect()->route('admin.agents.index')
            ->with('success', 'Agent deleted successfully.');
    }
}
