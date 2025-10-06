<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\PropertyType;
use App\Models\User;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        // Get users with roles other than 'user' for the agents section
        $agents = User::whereHas('roles', function ($query) {
            $query->where('name', '=', 'agent');
        })
            ->where('is_active', true)
            ->select([
                'id',
                'name',
                'designation',
                'profile_photo_path',
                'social_links',
                'email',
            ])
            ->take(6) // Limit to 6 agents for the home page
            ->get()
            ->map(function ($agent) {
                return [
                    'id' => $agent->id,
                    'name' => $agent->name,
                    'designation' => $agent->designation ?? 'Agent',
                    'profile_photo_url' => $agent->profile_photo_url,
                    'social_links' => $agent->social_links ?? [],
                    'email' => $agent->email,
                ];
            });

        // Get data for search functionality
        $propertyTypes = PropertyType::where('is_active', true)->orderBy('sort_order')->get();
        $locations = Location::where('is_active', true)->orderBy('name')->get();

        return Inertia::render('frontend/home', [
            'agents' => $agents,
            'propertyTypes' => $propertyTypes,
            'locations' => $locations,
        ]);
    }
}
