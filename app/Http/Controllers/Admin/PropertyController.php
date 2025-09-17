<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePropertyRequest;
use App\Http\Requests\Admin\UpdatePropertyRequest;
use App\Models\Agent;
use App\Models\Amenity;
use App\Models\Location;
use App\Models\Property;
use App\Models\PropertyType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PropertyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = Property::with(['propertyType', 'location', 'agent'])
            ->when($request->search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->type, function ($query, $type) {
                $query->where('listing_type', $type);
            })
            ->when($request->property_type_id, function ($query, $propertyTypeId) {
                $query->where('property_type_id', $propertyTypeId);
            });

        $properties = $query->latest()
            ->paginate(10)
            ->withQueryString();

        $propertyTypes = PropertyType::active()->ordered()->get();
        $locations = Location::active()->ordered()->get();

        return Inertia::render('Admin/Properties/Index', [
            'properties' => $properties,
            'propertyTypes' => $propertyTypes,
            'locations' => $locations,
            'filters' => $request->only(['search', 'status', 'type', 'property_type_id']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $propertyTypes = PropertyType::active()->ordered()->get();
        $locations = Location::active()->ordered()->get();
        $agents = Agent::active()->ordered()->get();
        $amenities = Amenity::active()->ordered()->get();

        return Inertia::render('Admin/Properties/Create', [
            'propertyTypes' => $propertyTypes,
            'locations' => $locations,
            'agents' => $agents,
            'amenities' => $amenities,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePropertyRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Ensure unique slug
        $originalSlug = $validated['slug'];
        $count = 1;
        while (Property::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $count;
            $count++;
        }

        $property = Property::create($validated);

        // Attach amenities if provided
        if (isset($validated['amenities'])) {
            $property->amenities()->attach($validated['amenities']);
        }

        return redirect()->route('admin.properties.index')
            ->with('success', 'Property created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Property $property): Response
    {
        $property->load(['propertyType', 'location', 'agent', 'amenities', 'images']);

        return Inertia::render('Admin/Properties/Show', [
            'property' => $property,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Property $property): Response
    {
        $property->load(['amenities']);

        $propertyTypes = PropertyType::active()->ordered()->get();
        $locations = Location::active()->ordered()->get();
        $agents = Agent::active()->ordered()->get();
        $amenities = Amenity::active()->ordered()->get();

        return Inertia::render('Admin/Properties/Edit', [
            'property' => $property,
            'propertyTypes' => $propertyTypes,
            'locations' => $locations,
            'agents' => $agents,
            'amenities' => $amenities,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePropertyRequest $request, Property $property): RedirectResponse
    {
        $validated = $request->validated();

        // Update slug if title changed
        if ($validated['title'] !== $property->title) {
            $validated['slug'] = Str::slug($validated['title']);

            // Ensure unique slug
            $originalSlug = $validated['slug'];
            $count = 1;
            while (Property::where('slug', $validated['slug'])->where('id', '!=', $property->id)->exists()) {
                $validated['slug'] = $originalSlug . '-' . $count;
                $count++;
            }
        }

        $property->update($validated);

        // Sync amenities
        if (isset($validated['amenities'])) {
            $property->amenities()->sync($validated['amenities']);
        } else {
            $property->amenities()->detach();
        }

        return redirect()->route('admin.properties.index')
            ->with('success', 'Property updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Property $property): RedirectResponse
    {
        $property->delete();

        return redirect()->route('admin.properties.index')
            ->with('success', 'Property deleted successfully.');
    }

    /**
     * Remove multiple properties from storage.
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:properties,id',
        ]);

        Property::whereIn('id', $request->ids)->delete();

        return redirect()->route('admin.properties.index')
            ->with('success', 'Selected properties deleted successfully.');
    }
}
