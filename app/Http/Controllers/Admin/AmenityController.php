<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AmenityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $amenities = Amenity::query()
            ->when($request->get('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->get('category'), function ($query, $category) {
                $query->where('category', $category);
            })
            ->withCount('properties')
            ->orderBy($request->get('sort', 'name'), $request->get('direction', 'asc'))
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Amenities/Index', [
            'amenities' => $amenities,
            'filters' => $request->only(['search', 'category', 'sort', 'direction']),
            'categories' => Amenity::distinct('category')->pluck('category')->filter()->values(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Amenities/Create', [
            'categories' => Amenity::distinct('category')->pluck('category')->filter()->values(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:amenities,name',
            'slug' => 'nullable|string|max:255|unique:amenities,slug',
            'description' => 'nullable|string|max:1000',
            'category' => 'required|string|max:100',
            'icon' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = str($validated['name'])->slug();
        }

        Amenity::create($validated);

        return redirect()->route('admin.amenities.index')
            ->with('success', 'Amenity created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Amenity $amenity): Response
    {
        $amenity->load('properties');

        return Inertia::render('Admin/Amenities/Show', [
            'amenity' => $amenity,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Amenity $amenity): Response
    {
        return Inertia::render('Admin/Amenities/Edit', [
            'amenity' => $amenity,
            'categories' => Amenity::distinct('category')->pluck('category')->filter()->values(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Amenity $amenity): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:amenities,name,' . $amenity->id,
            'slug' => 'nullable|string|max:255|unique:amenities,slug,' . $amenity->id,
            'description' => 'nullable|string|max:1000',
            'category' => 'required|string|max:100',
            'icon' => 'nullable|string|max:100',
            'is_active' => 'boolean',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = str($validated['name'])->slug();
        }

        $amenity->update($validated);

        return redirect()->route('admin.amenities.index')
            ->with('success', 'Amenity updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Amenity $amenity): RedirectResponse
    {
        if ($amenity->properties()->count() > 0) {
            return redirect()->route('admin.amenities.index')
                ->with('error', 'Cannot delete amenity with associated properties.');
        }

        $amenity->delete();

        return redirect()->route('admin.amenities.index')
            ->with('success', 'Amenity deleted successfully.');
    }
}
