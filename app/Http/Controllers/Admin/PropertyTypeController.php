<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PropertyType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PropertyTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $search = $request->get('search');

        $propertyTypes = PropertyType::query()
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->ordered()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/PropertyTypes/Index', [
            'propertyTypes' => $propertyTypes,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/PropertyTypes/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        // Ensure unique slug
        $originalSlug = $validated['slug'];
        $count = 1;
        while (PropertyType::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $originalSlug . '-' . $count;
            $count++;
        }

        PropertyType::create($validated);

        return redirect()->route('admin.property-types.index')
            ->with('success', 'Property type created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PropertyType $propertyType): Response
    {
        $propertyType->load(['properties']);

        return Inertia::render('Admin/PropertyTypes/Show', [
            'propertyType' => $propertyType,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PropertyType $propertyType): Response
    {
        return Inertia::render('Admin/PropertyTypes/Edit', [
            'propertyType' => $propertyType,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PropertyType $propertyType): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // Update slug if name changed
        if ($validated['name'] !== $propertyType->name) {
            $validated['slug'] = Str::slug($validated['name']);

            // Ensure unique slug
            $originalSlug = $validated['slug'];
            $count = 1;
            while (PropertyType::where('slug', $validated['slug'])->where('id', '!=', $propertyType->id)->exists()) {
                $validated['slug'] = $originalSlug . '-' . $count;
                $count++;
            }
        }

        $propertyType->update($validated);

        return redirect()->route('admin.property-types.index')
            ->with('success', 'Property type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PropertyType $propertyType): RedirectResponse
    {
        // Check if any properties are using this type
        if ($propertyType->properties()->exists()) {
            return redirect()->route('admin.property-types.index')
                ->with('error', 'Cannot delete property type. It is being used by properties.');
        }

        $propertyType->delete();

        return redirect()->route('admin.property-types.index')
            ->with('success', 'Property type deleted successfully.');
    }
}
