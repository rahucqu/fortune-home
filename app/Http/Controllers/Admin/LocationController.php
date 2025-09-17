<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $locations = Location::query()
            ->when($request->get('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->get('type'), function ($query, $type) {
                $query->where('type', $type);
            })
            ->withCount('properties')
            ->orderBy($request->get('sort', 'name'), $request->get('direction', 'asc'))
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Locations/Index', [
            'locations' => $locations,
            'filters' => $request->only(['search', 'type', 'sort', 'direction']),
            'types' => Location::distinct('type')->pluck('type')->filter()->values(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Locations/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:locations,name',
            'slug' => 'nullable|string|max:255|unique:locations,slug',
            'type' => 'required|string|in:city,suburb,district,region,state',
            'description' => 'nullable|string|max:1000',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_active' => 'boolean',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = str($validated['name'])->slug();
        }

        Location::create($validated);

        return redirect()->route('admin.locations.index')
            ->with('success', 'Location created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Location $location): Response
    {
        $location->load('properties');

        return Inertia::render('Admin/Locations/Show', [
            'location' => $location,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Location $location): Response
    {
        return Inertia::render('Admin/Locations/Edit', [
            'location' => $location,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Location $location): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:locations,name,' . $location->id,
            'slug' => 'nullable|string|max:255|unique:locations,slug,' . $location->id,
            'type' => 'required|string|in:city,suburb,district,region,state',
            'description' => 'nullable|string|max:1000',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_active' => 'boolean',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = str($validated['name'])->slug();
        }

        $location->update($validated);

        return redirect()->route('admin.locations.index')
            ->with('success', 'Location updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Location $location): RedirectResponse
    {
        if ($location->properties()->count() > 0) {
            return redirect()->route('admin.locations.index')
                ->with('error', 'Cannot delete location with associated properties.');
        }

        $location->delete();

        return redirect()->route('admin.locations.index')
            ->with('success', 'Location deleted successfully.');
    }
}
